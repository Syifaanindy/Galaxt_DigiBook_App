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

/**
 * Ambil semua transaksi (Untuk Halaman Admin)
 */
function ambilSemuaTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null, $limit = 5, $offset = 0) {
    $query = "SELECT 
                t.transaction_code, 
                u.username, 
                b.title, 
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
    $query .= " ORDER BY t.transaction_date DESC LIMIT $limit OFFSET $offset";
              
    $result = mysqli_query($conn, $query);
    if (!$result) { die("Query Error: " . mysqli_error($conn)); }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Hitung total transaksi untuk pagination (Admin)
 */
function hitungTotalTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null) {
    $query = "SELECT COUNT(*) as total 
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
                b.id as book_id,
                b.title, 
                t.total_price, 
                t.transaction_date, 
                t.status
              FROM transactions t
              JOIN users u ON t.user_id = u.id
              JOIN transaction_items ti ON t.id = ti.transaction_id
              JOIN books b ON ti.book_id = b.id
              WHERE t.user_id = $user_id
              ORDER BY t.transaction_date DESC";
              
    $result = mysqli_query($conn, $query);
    if (!$result) { die("Query Error: " . mysqli_error($conn)); }
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>