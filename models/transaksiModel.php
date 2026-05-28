<?php
function ambilSemuaTransaksi($conn, $tgl_mulai = null, $tgl_akhir = null) {
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

    // Tambahkan filter kalau user input tanggal
    if ($tgl_mulai && $tgl_akhir) {
        $query .= " AND DATE(t.transaction_date) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";
    }

    $query .= " ORDER BY t.transaction_date DESC";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>