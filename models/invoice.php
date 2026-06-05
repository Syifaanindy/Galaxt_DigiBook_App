<?php

/**
 * Hitung total transaksi milik user tertentu untuk pagination riwayat
 */
function hitungTotalTransaksiUser($conn, $user_id) {
    $user_id = (int)$user_id;
    
    $query = "SELECT COUNT(*) as total 
              FROM transactions 
              WHERE user_id = $user_id";
              
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query Error di invoice.php (hitung): " . mysqli_error($conn));
    }
    
    $data = mysqli_fetch_assoc($result);
    return (int)$data['total'];
}

/**
 * Ambil data transaksi per halaman (Pagination) dengan JOIN 3 tabel baru
 */
function ambilTransaksiPerHalaman($conn, $user_id, $limit, $offset) {
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $query = "SELECT 
                t.transaction_code, 
                t.transaction_date, 
                t.total_price, 
                t.status,
                b.id as book_id,
                b.title
              FROM transactions t
              JOIN transaction_items ti ON t.id = ti.transaction_id
              JOIN books b ON ti.book_id = b.id
              WHERE t.user_id = $user_id
              ORDER BY t.transaction_date DESC 
              LIMIT $limit OFFSET $offset";
              
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query Error di invoice.php (per halaman): " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}