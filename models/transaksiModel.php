<?php

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
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

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
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    $data = mysqli_fetch_assoc($result);
    return (int)$data['total'];
}

function ambilRiwayatTransaksiUser($conn, $user_id) {
    $user_id = (int)$user_id;
    
    // Diperbaiki: Menggunakan tabel 'transactions' dan JOIN 'transaction_items'
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
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>