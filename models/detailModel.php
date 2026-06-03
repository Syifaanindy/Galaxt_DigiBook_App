<?php
function ambilDetailBuku($conn, $id) {
    // Menggunakan prepared statement supaya aman dari SQL Injection
    $query = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $buku = $result->fetch_assoc();
    
    $stmt->close();
    return $buku; // Mengembalikan array data 1 buku
}

function ambilRatingBuku($conn, $id_buku) {
    $query = "SELECT 
                COUNT(id) AS total_ulasan,
                ROUND(AVG(rating), 1) AS avg_rating
              FROM book_reviews 
              WHERE book_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_buku);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt->close();
    
    // Jika tidak ada review, set default rating 0 dan ulasan 0
    return [
        'avg_rating' => $data['avg_rating'] ?? 0,
        'total_ulasan' => $data['total_ulasan'] ?? 0
    ];
}

function ambilReviewBuku($conn, $id_buku) {
    $query = "SELECT br.*, users.username 
              FROM book_reviews br
              JOIN users ON br.user_id = users.id
              WHERE br.book_id = ?
              ORDER BY br.id DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_buku);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    return $reviews;
}
?>