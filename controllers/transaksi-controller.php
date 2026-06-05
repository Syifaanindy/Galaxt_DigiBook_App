<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PERBAIKAN: Menyesuaikan nama file sesuai struktur asli di VS Code (transaksiModel.php)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/transaksi-model.php'; 

/**
 * Fungsi Inti Proses Pembayaran Langsung (Jalur Pertama)
 */
function prosesPembayaranLangsung($conn, $userId, $bookId, $price) {
    // Buat kode invoice unik dan catat waktu sekarang
    $transaction_code = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
    $date_now         = date('Y-m-d H:i:s');

    // Aktifkan database transaction (Jika ada satu query gagal, semua otomatis dibatalkan)
    mysqli_begin_transaction($conn);

    try {
        // A. Simpan ke tabel induk: transactions
        $transactionId = tambahTransaksiUtama($conn, $transaction_code, $userId, $date_now, $price, 'success');
        if (!$transactionId) {
            throw new Exception("Gagal menyimpan data transaksi utama.");
        }

        // B. Simpan ke tabel detail: transaction_items
        $simpanItem = tambahDetailItem($conn, $transactionId, $bookId, $price);
        if (!$simpanItem) {
            throw new Exception("Gagal menyimpan item detail transaksi.");
        }

        // C. Simpan ke tabel hak akses: user_book (Buku resmi jadi milik user)
        $simpanAksesBuku = tambahBukuUser($conn, $userId, $bookId, $transactionId, $date_now);
        if (!$simpanAksesBuku) {
            throw new Exception("Gagal memberikan akses buku digital ke user.");
        }

        // Kunci semua perubahan jika sukses tanpa error
        mysqli_commit($conn);
        return ['status' => 'success', 'message' => 'Pembayaran Berhasil! Buku telah ditambahkan ke koleksimu.'];

    } catch (Exception $e) {
        // Batalkan seluruh query jika di tengah jalan ada yang gagal (Database tetap bersih)
        mysqli_rollback($conn);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// ========================================================
// HANDLER REQUEST: Menerima Lemparan Form dari transaksi.php
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_bayar'])) {
    
    // Proteksi keamanan: Pastikan user sudah login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Silahkan login terlebih dahulu.";
        header("Location: ../views/user/login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $bookId = intval($_POST['book_id'] ?? 0);
    $price  = intval($_POST['price'] ?? 0);

    // Validasi data input awal
    if ($bookId <= 0 || $price <= 0) {
        $_SESSION['error'] = "Data transaksi tidak valid.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Jalankan fungsi transaksi di atas
    $hasil = prosesPembayaranLangsung($conn, $userId, $bookId, $price);

    if ($hasil['status'] === 'success') {
        $_SESSION['success'] = $hasil['message'];
        // Alihkan langsung ke halaman riwayat belanja milik user
        header("Location: ../views/user/riwayat_transaksi.php"); 
    } else {
        $_SESSION['error'] = $hasil['message'];
        // Kembalikan ke halaman transaksi jika gagal
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit;
}