<?php

/**
 * Menyimpan data induk transaksi (Tabel: transactions)
 */
function tambahTransaksiUtama($conn, $transaction_code, $user_id, $transaction_date, $total_price, $status = 'success') {
    $transaction_code = mysqli_real_escape_string($conn, $transaction_code);
    $user_id = (int)$user_id;
    $transaction_date = mysqli_real_escape_string($conn, $transaction_date);
    $total_price = (int)$total_price;
    $status = mysqli_real_escape_string($conn, $status);

    $query = "INSERT INTO transactions (transaction_code, user_id, transaction_date, total_price, status) 
              VALUES ('$transaction_code', $user_id, '$transaction_date', $total_price, '$status')";
              
    if (mysqli_query($conn, $query)) {
        return mysqli_insert_id($conn); // Mengembalikan ID transaksi yang baru dibuat
    }
    return false;
}

function ambilTransaksiByKode($conn, $transaction_code) {
    $query = "SELECT * FROM transactions WHERE transaction_code = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $transaction_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close();

    return $transaction;
}

function ambilItemTransaksi($conn, $transaction_id) {
    $transaction_id = (int)$transaction_id;
    $query = "SELECT * FROM transaction_items WHERE transaction_id = $transaction_id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function updateStatusTransaksi($conn, $transaction_id, $status) {
    $query = "UPDATE transactions SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $transaction_id);
    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

function transaksiMilikUser($conn, $transaction_code, $user_id) {
    $query = "SELECT id FROM transactions WHERE transaction_code = ? AND user_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $transaction_code, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ada = (bool)$result->fetch_assoc();
    $stmt->close();

    return $ada;
}

function selesaikanTransaksiBerhasil($conn, $transaction_code) {
    $transaction = ambilTransaksiByKode($conn, $transaction_code);
    if (!$transaction) {
        throw new Exception("Transaksi tidak ditemukan.");
    }

    if ($transaction['status'] === 'success') {
        return true;
    }

    mysqli_begin_transaction($conn);

    try {
        if (!updateStatusTransaksi($conn, (int)$transaction['id'], 'success')) {
            throw new Exception("Gagal mengubah status transaksi menjadi success.");
        }

        $items = ambilItemTransaksi($conn, (int)$transaction['id']);
        foreach ($items as $item) {
            $aksesBerhasil = tambahBukuUser(
                $conn,
                (int)$transaction['user_id'],
                (int)$item['book_id'],
                (int)$transaction['id'],
                date('Y-m-d H:i:s')
            );

            if (!$aksesBerhasil) {
                throw new Exception("Gagal memberikan akses buku digital.");
            }

            if (!hapusBukuDariKeranjangSetelahBeli($conn, (int)$transaction['user_id'], (int)$item['book_id'])) {
                throw new Exception("Gagal menghapus buku dari keranjang.");
            }
        }

        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

function updateTransaksiDariStatusMidtrans($conn, $transaction_code, $status_baru, $midtrans_response = []) {
    $transaction = ambilTransaksiByKode($conn, $transaction_code);
    if (!$transaction) {
        throw new Exception("Transaksi tidak ditemukan.");
    }

    if ($status_baru === 'success') {
        $berhasil = selesaikanTransaksiBerhasil($conn, $transaction_code);
        updateStatusMidtransOrder($conn, $transaction_code, 'success', $midtrans_response);
        return $berhasil;
    }

    if ($transaction['status'] === 'success') {
        updateStatusMidtransOrder($conn, $transaction_code, 'success', $midtrans_response);
        return true;
    }

    if ($status_baru === 'pending' && $transaction['status'] === 'failed') {
        updateStatusMidtransOrder($conn, $transaction_code, 'failed', $midtrans_response);
        return true;
    }

    $updateTransaksi = updateStatusTransaksi($conn, (int)$transaction['id'], $status_baru);
    $updateMidtransOrder = updateStatusMidtransOrder($conn, $transaction_code, $status_baru, $midtrans_response);

    return $updateTransaksi && $updateMidtransOrder;
}

/**
 * Menyimpan detail item buku yang dibeli (Tabel: transaction_items)
 */
function tambahDetailItem($conn, $transaction_id, $book_id, $price) {
    $transaction_id = (int)$transaction_id;
    $book_id = (int)$book_id;
    $price = (int)$price;

    $query = "INSERT INTO transaction_items (transaction_id, book_id, price) 
              VALUES ($transaction_id, $book_id, $price)";
              
    return mysqli_query($conn, $query);
}

function tambahMidtransOrder($conn, $order_id, $user_id, $book_id, $gross_amount, $status, $local_transaction_id = null, $raw_response = null) {
    $query = "INSERT INTO midtrans_orders
                (order_id, user_id, book_id, gross_amount, status, local_transaction_id, raw_response)
              VALUES (?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
                user_id = VALUES(user_id),
                book_id = VALUES(book_id),
                gross_amount = VALUES(gross_amount),
                status = VALUES(status),
                local_transaction_id = VALUES(local_transaction_id),
                raw_response = VALUES(raw_response)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "siiisis",
        $order_id,
        $user_id,
        $book_id,
        $gross_amount,
        $status,
        $local_transaction_id,
        $raw_response
    );

    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

function tambahMidtransOrderItem($conn, $order_id, $book_id, $price) {
    $query = "INSERT INTO midtrans_order_items (order_id, book_id, price)
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE price = VALUES(price)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $order_id, $book_id, $price);
    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

function updateSnapMidtransOrder($conn, $order_id, $snap_token, $redirect_url, $raw_response) {
    $query = "UPDATE midtrans_orders
              SET snap_token = ?, redirect_url = ?, raw_response = ?
              WHERE order_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $snap_token, $redirect_url, $raw_response, $order_id);
    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

function updateStatusMidtransOrder($conn, $order_id, $status, $midtrans_response = []) {
    $payment_type = $midtrans_response['payment_type'] ?? null;
    $midtrans_transaction_id = $midtrans_response['transaction_id'] ?? null;
    $raw_response = !empty($midtrans_response) ? json_encode($midtrans_response) : null;
    $paid_at = null;

    if ($status === 'success') {
        $paid_at = $midtrans_response['settlement_time']
            ?? $midtrans_response['transaction_time']
            ?? date('Y-m-d H:i:s');
    }

    $query = "UPDATE midtrans_orders
              SET status = ?,
                  payment_type = COALESCE(?, payment_type),
                  midtrans_transaction_id = COALESCE(?, midtrans_transaction_id),
                  raw_response = COALESCE(?, raw_response),
                  paid_at = COALESCE(?, paid_at)
              WHERE order_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssss",
        $status,
        $payment_type,
        $midtrans_transaction_id,
        $raw_response,
        $paid_at,
        $order_id
    );

    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

/**
 * Memberikan hak akses buku kepada user (Tabel: user_book)
 */
function tambahBukuUser($conn, $user_id, $book_id, $transaction_id, $purchased_at) {
    $user_id = (int)$user_id;
    $book_id = (int)$book_id;
    $transaction_id = (int)$transaction_id;
    $purchased_at = mysqli_real_escape_string($conn, $purchased_at);

    // Cek duplikasi agar user tidak memiliki buku yang sama dua kali
    $cek_query = "SELECT id FROM user_book WHERE user_id = $user_id AND book_id = $book_id";
    $cek_result = mysqli_query($conn, $cek_query);
    
    if (mysqli_num_rows($cek_result) == 0) {
        $query = "INSERT INTO user_book (user_id, book_id, transaction_id, purchased_at) 
                  VALUES ($user_id, $book_id, $transaction_id, '$purchased_at')";
        return mysqli_query($conn, $query);
    }
    return true; // Jika sudah punya, anggap sukses (tidak di-insert ulang)
}

function hapusBukuDariKeranjangSetelahBeli($conn, $user_id, $book_id) {
    $query = "DELETE FROM cart WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $book_id);
    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

/**
 * Ambil semua transaksi (Untuk Halaman Admin)
 */
function ambilSemuaTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null, $limit = 5, $offset = 0) {
    $query = "SELECT 
                t.transaction_code, 
                u.username, 
                GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ') as title, 
                t.total_price, 
                t.transaction_date, 
                t.status
              FROM transactions t
              JOIN users u ON t.user_id = u.id
              JOIN transaction_items ti ON t.id = ti.transaction_id
              JOIN books b ON ti.book_id = b.id
              WHERE u.role = 'user'";

    if ($tgl_mulai && $tgl_akhir) {
        $tgl_mulai_safe = mysqli_real_escape_string($conn, $tgl_mulai);
        $tgl_akhir_safe = mysqli_real_escape_string($conn, $tgl_akhir);
        $query .= " AND DATE(t.transaction_date) BETWEEN '$tgl_mulai_safe' AND '$tgl_akhir_safe'";
    }

    $limit = (int)$limit;
    $offset = (int)$offset;
    $query .= " GROUP BY t.id, t.transaction_code, u.username, t.total_price, t.transaction_date, t.status";
    $query .= " ORDER BY t.transaction_date DESC LIMIT $limit OFFSET $offset";
              
    $result = mysqli_query($conn, $query);
    if (!$result) { die("Query Error: " . mysqli_error($conn)); }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Hitung total transaksi untuk pagination (Admin)
 */
function hitungTotalTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null) {
    $query = "SELECT COUNT(DISTINCT t.id) as total 
              FROM transactions t 
              JOIN users u ON t.user_id = u.id 
              JOIN transaction_items ti ON t.id = ti.transaction_id
              JOIN books b ON ti.book_id = b.id
              WHERE u.role = 'user'";
    
    if ($tgl_mulai && $tgl_akhir) {
        $tgl_mulai_safe = mysqli_real_escape_string($conn, $tgl_mulai);
        $tgl_akhir_safe = mysqli_real_escape_string($conn, $tgl_akhir);
        $query .= " AND DATE(t.transaction_date) BETWEEN '$tgl_mulai_safe' AND '$tgl_akhir_safe'";
    }

    $result = mysqli_query($conn, $query);
    if (!$result) { die("Query Error: " . mysqli_error($conn)); }
    $data = mysqli_fetch_assoc($result);
    return (int)$data['total'];
}

/**
 * Ambil riwayat transaksi milik user tertentu (Halaman Riwayat User)
 */
function ambilRiwayatTransaksiUser($conn, $user_id) {
    $user_id = (int)$user_id;
    $query = "SELECT 
                t.transaction_code, 
                u.username, 
                MIN(b.id) as book_id,
                COUNT(ti.id) as item_count,
                GROUP_CONCAT(b.id ORDER BY b.title SEPARATOR ',') as book_ids,
                GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ') as title, 
                t.total_price, 
                t.transaction_date, 
                t.status
              FROM transactions t
              JOIN users u ON t.user_id = u.id
              JOIN transaction_items ti ON t.id = ti.transaction_id
              JOIN books b ON ti.book_id = b.id
              WHERE t.user_id = $user_id
              GROUP BY t.id, t.transaction_code, u.username, t.total_price, t.transaction_date, t.status
              ORDER BY t.transaction_date DESC";
              
    $result = mysqli_query($conn, $query);
    if (!$result) { die("Query Error: " . mysqli_error($conn)); }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
