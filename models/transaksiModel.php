<?php

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
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

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
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
