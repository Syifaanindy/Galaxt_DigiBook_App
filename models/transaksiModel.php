<?php

function ambilSemuaTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null, $limit = 5, $offset = 0) {
    $query = "SELECT 
                t.transaction_code, 
                u.username, 
                b.title, 
                t.total_price, 
                t.transaction_date, 
                t.status
              FROM transaction t
              JOIN users u ON t.user_id = u.id
              JOIN books b ON t.book_id = b.id
              WHERE u.role = 'user'";

    // Tambahkan filter kalau user input tanggal (Aman dari SQL Injection)
    if ($tgl_mulai && $tgl_akhir) {
        $tgl_mulai_safe = mysqli_real_escape_string($conn, $tgl_mulai);
        $tgl_akhir_safe = mysqli_real_escape_string($conn, $tgl_akhir);
        $query .= " AND DATE(t.transaction_date) BETWEEN '$tgl_mulai_safe' AND '$tgl_akhir_safe'";
    }

    // Mengamankan parameter limit dan offset agar selalu bertipe integer
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
    // Menambahkan JOIN ke books dihitung totalnya juga agar kondisi relasinya sama persis dengan fungsi di atas
    $query = "SELECT COUNT(*) as total 
              FROM transaction t 
              JOIN users u ON t.user_id = u.id 
              JOIN books b ON t.book_id = b.id
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
?>