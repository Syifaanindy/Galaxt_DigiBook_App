<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PERBAIKAN: Menyesuaikan nama file sesuai struktur asli di VS Code (transaksiModel.php)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../helpers/midtrans-helper.php';
require_once __DIR__ . '/../models/transaksi-model.php';
require_once __DIR__ . '/../models/buku-model.php';

/**
 * Buat transaksi lokal dengan status pending, lalu minta Snap token ke Midtrans.
 */
function buatTransaksiMidtrans($conn, $userId, $bookId, $phone = '') {
    if (!midtrans_is_configured()) {
        throw new Exception("Key Midtrans belum diisi di config/midtrans.php.");
    }

    $buku = ambilBukuById($conn, $bookId);
    if (!$buku) {
        throw new Exception("Buku tidak ditemukan.");
    }

    $biayaLayanan = 2500;
    $hargaBuku = (int)$buku['price'];
    $totalHarga = $hargaBuku + $biayaLayanan;
    $transaction_code = 'INV-' . date('YmdHis') . '-' . $userId . '-' . random_int(100, 999);
    $date_now = date('Y-m-d H:i:s');

    mysqli_begin_transaction($conn);

    try {
        $transactionId = tambahTransaksiUtama($conn, $transaction_code, $userId, $date_now, $totalHarga, 'pending');
        if (!$transactionId) {
            throw new Exception("Gagal menyimpan data transaksi utama.");
        }

        $simpanItem = tambahDetailItem($conn, $transactionId, $bookId, $totalHarga);
        if (!$simpanItem) {
            throw new Exception("Gagal menyimpan item detail transaksi.");
        }

        $simpanMidtransOrder = tambahMidtransOrder(
            $conn,
            $transaction_code,
            $userId,
            $bookId,
            $totalHarga,
            'pending',
            $transactionId,
            json_encode(['created_from' => 'checkout'])
        );
        if (!$simpanMidtransOrder) {
            throw new Exception("Gagal menyimpan data order Midtrans.");
        }

        $simpanMidtransItem = tambahMidtransOrderItem($conn, $transaction_code, $bookId, $hargaBuku);
        if (!$simpanMidtransItem) {
            throw new Exception("Gagal menyimpan item order Midtrans.");
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    $payload = [
        'transaction_details' => [
            'order_id' => $transaction_code,
            'gross_amount' => $totalHarga,
        ],
        'item_details' => [
            [
                'id' => 'BOOK-' . $bookId,
                'price' => $hargaBuku,
                'quantity' => 1,
                'name' => substr($buku['title'], 0, 50),
            ],
            [
                'id' => 'SERVICE-FEE',
                'price' => $biayaLayanan,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ],
        ],
        'customer_details' => [
            'first_name' => $_SESSION['username'] ?? 'Customer',
        ],
        'callbacks' => [
            'finish' => base_url('views/user/riwayat_transaksi.php'),
        ],
    ];

    $email = trim($_SESSION['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $payload['customer_details']['email'] = $email;
    }

    if ($phone !== '') {
        $payload['customer_details']['phone'] = $phone;
    }

    try {
        $snap = midtrans_create_snap_transaction($payload);
    } catch (Exception $e) {
        updateStatusTransaksi($conn, (int)$transactionId, 'failed');
        updateStatusMidtransOrder($conn, $transaction_code, 'failed', [
            'error_message' => $e->getMessage(),
            'created_payload' => $payload,
        ]);
        throw $e;
    }

    updateSnapMidtransOrder(
        $conn,
        $transaction_code,
        $snap['token'] ?? '',
        $snap['redirect_url'] ?? '',
        json_encode($snap)
    );

    return [
        'status' => 'success',
        'transaction_code' => $transaction_code,
        'snap_token' => $snap['token'] ?? '',
        'redirect_url' => $snap['redirect_url'] ?? '',
    ];
}

function normalisasiBookIds($bookIds) {
    if (is_string($bookIds)) {
        $bookIds = explode(',', $bookIds);
    }

    if (!is_array($bookIds)) {
        return [];
    }

    $hasil = [];
    foreach ($bookIds as $bookId) {
        $bookId = (int)$bookId;
        if ($bookId > 0) {
            $hasil[$bookId] = $bookId;
        }
    }

    return array_values($hasil);
}

function ambilBukuKeranjangTerpilih($conn, $userId, $bookIds) {
    $bookIds = normalisasiBookIds($bookIds);
    if (empty($bookIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
    $types = 'i' . str_repeat('i', count($bookIds));
    $params = array_merge([(int)$userId], $bookIds);

    $query = "SELECT b.*
              FROM books b
              JOIN cart c ON c.book_id = b.id
              WHERE c.user_id = ? AND b.id IN ($placeholders)
              ORDER BY FIELD(b.id, $placeholders)";

    $types .= str_repeat('i', count($bookIds));
    $params = array_merge($params, $bookIds);

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $books;
}

/**
 * Buat satu transaksi Midtrans untuk beberapa buku dari keranjang.
 */
function buatTransaksiMidtransMultiple($conn, $userId, $bookIds, $phone = '') {
    if (!midtrans_is_configured()) {
        throw new Exception("Key Midtrans belum diisi di config/midtrans.php.");
    }

    $bookIds = normalisasiBookIds($bookIds);
    if (empty($bookIds)) {
        throw new Exception("Pilih minimal satu buku untuk checkout.");
    }

    $books = ambilBukuKeranjangTerpilih($conn, $userId, $bookIds);
    if (count($books) !== count($bookIds)) {
        throw new Exception("Beberapa buku pilihan tidak valid atau sudah tidak ada di keranjang.");
    }

    $biayaLayanan = 2500;
    $subtotalBuku = 0;
    foreach ($books as $book) {
        $subtotalBuku += (int)$book['price'];
    }

    $totalHarga = $subtotalBuku + $biayaLayanan;
    $transaction_code = 'INV-' . date('YmdHis') . '-' . $userId . '-' . random_int(100, 999);
    $date_now = date('Y-m-d H:i:s');
    $primaryBookId = (int)$books[0]['id'];
    $transactionId = null;

    mysqli_begin_transaction($conn);

    try {
        $transactionId = tambahTransaksiUtama($conn, $transaction_code, $userId, $date_now, $totalHarga, 'pending');
        if (!$transactionId) {
            throw new Exception("Gagal menyimpan data transaksi utama.");
        }

        foreach ($books as $book) {
            $bookId = (int)$book['id'];
            $hargaBuku = (int)$book['price'];

            if (!tambahDetailItem($conn, $transactionId, $bookId, $hargaBuku)) {
                throw new Exception("Gagal menyimpan item detail transaksi.");
            }
        }

        $simpanMidtransOrder = tambahMidtransOrder(
            $conn,
            $transaction_code,
            $userId,
            $primaryBookId,
            $totalHarga,
            'pending',
            $transactionId,
            json_encode([
                'created_from' => 'checkout_multiple',
                'book_ids' => $bookIds,
            ])
        );
        if (!$simpanMidtransOrder) {
            throw new Exception("Gagal menyimpan data order Midtrans.");
        }

        foreach ($books as $book) {
            if (!tambahMidtransOrderItem($conn, $transaction_code, (int)$book['id'], (int)$book['price'])) {
                throw new Exception("Gagal menyimpan item order Midtrans.");
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    $itemDetails = [];
    foreach ($books as $book) {
        $itemDetails[] = [
            'id' => 'BOOK-' . (int)$book['id'],
            'price' => (int)$book['price'],
            'quantity' => 1,
            'name' => substr($book['title'], 0, 50),
        ];
    }
    $itemDetails[] = [
        'id' => 'SERVICE-FEE',
        'price' => $biayaLayanan,
        'quantity' => 1,
        'name' => 'Biaya Layanan',
    ];

    $payload = [
        'transaction_details' => [
            'order_id' => $transaction_code,
            'gross_amount' => $totalHarga,
        ],
        'item_details' => $itemDetails,
        'customer_details' => [
            'first_name' => $_SESSION['username'] ?? 'Customer',
        ],
        'callbacks' => [
            'finish' => base_url('views/user/riwayat_transaksi.php'),
        ],
    ];

    $email = trim($_SESSION['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $payload['customer_details']['email'] = $email;
    }

    if ($phone !== '') {
        $payload['customer_details']['phone'] = $phone;
    }

    try {
        $snap = midtrans_create_snap_transaction($payload);
    } catch (Exception $e) {
        updateStatusTransaksi($conn, (int)$transactionId, 'failed');
        updateStatusMidtransOrder($conn, $transaction_code, 'failed', [
            'error_message' => $e->getMessage(),
            'created_payload' => $payload,
        ]);
        throw $e;
    }

    updateSnapMidtransOrder(
        $conn,
        $transaction_code,
        $snap['token'] ?? '',
        $snap['redirect_url'] ?? '',
        json_encode($snap)
    );

    return [
        'status' => 'success',
        'transaction_code' => $transaction_code,
        'snap_token' => $snap['token'] ?? '',
        'redirect_url' => $snap['redirect_url'] ?? '',
    ];
}

function jsonResponse($statusCode, $data) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === '') {
        $jsonBody = json_decode(file_get_contents('php://input'), true);
        if (is_array($jsonBody)) {
            $_POST = array_merge($_POST, $jsonBody);
            $action = $_POST['action'] ?? '';
        }
    }

    if ($action === 'create_midtrans_transaction') {
        if (!isset($_SESSION['user_id'])) {
            jsonResponse(401, ['status' => 'error', 'message' => 'Silahkan login terlebih dahulu.']);
        }

        $bookId = intval($_POST['book_id'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');

        if ($bookId <= 0) {
            jsonResponse(422, ['status' => 'error', 'message' => 'Data buku tidak valid.']);
        }

        try {
            jsonResponse(200, buatTransaksiMidtrans($conn, (int)$_SESSION['user_id'], $bookId, $phone));
        } catch (Exception $e) {
            jsonResponse(500, ['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    if ($action === 'create_midtrans_transaction_multiple') {
        if (!isset($_SESSION['user_id'])) {
            jsonResponse(401, ['status' => 'error', 'message' => 'Silahkan login terlebih dahulu.']);
        }

        $bookIds = $_POST['book_ids'] ?? [];
        $phone = trim($_POST['phone'] ?? '');

        try {
            jsonResponse(200, buatTransaksiMidtransMultiple($conn, (int)$_SESSION['user_id'], $bookIds, $phone));
        } catch (Exception $e) {
            jsonResponse(500, ['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    if ($action === 'sync_midtrans_status') {
        if (!isset($_SESSION['user_id'])) {
            jsonResponse(401, ['status' => 'error', 'message' => 'Silahkan login terlebih dahulu.']);
        }

        $transactionCode = trim($_POST['transaction_code'] ?? '');
        if ($transactionCode === '' || !transaksiMilikUser($conn, $transactionCode, (int)$_SESSION['user_id'])) {
            jsonResponse(404, ['status' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
        }

        try {
            $midtransStatus = midtrans_get_transaction_status($transactionCode);
            $statusBaru = midtrans_map_status(
                $midtransStatus['transaction_status'] ?? 'pending',
                $midtransStatus['fraud_status'] ?? null
            );
            updateTransaksiDariStatusMidtrans($conn, $transactionCode, $statusBaru, $midtransStatus);

            jsonResponse(200, ['status' => 'success', 'payment_status' => $statusBaru]);
        } catch (Exception $e) {
            jsonResponse(500, ['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    jsonResponse(400, ['status' => 'error', 'message' => 'Action tidak dikenal.']);
}
