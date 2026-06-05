<?php

function ambilBukuTerbatas($conn, $limit = 4) {
    $query = "SELECT * FROM books ORDER BY id DESC LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
function ambilSemua($conn) {
    $query = "SELECT * FROM books ORDER BY id DESC LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function ambilReviewTerbaik($conn) {
    $query = "SELECT web_reviews.*, users.username 
              FROM web_reviews 
              JOIN users ON web_reviews.user_id = users.id 
              WHERE web_reviews.rating > 3 
              ORDER BY web_reviews.id DESC LIMIT 3";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Error Query: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function ambilBukuBestSeller($conn) {
    $query = "SELECT books.*, COUNT(transaction.book_id) as total_terjual 
              FROM books 
              LEFT JOIN transaction ON books.id = transaction.book_id 
              GROUP BY books.id 
              ORDER BY total_terjual DESC, books.id ASC LIMIT 3";
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Error Query Best Seller: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>