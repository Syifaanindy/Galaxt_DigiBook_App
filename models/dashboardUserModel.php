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
    
    $query = "SELECT books.*, COUNT(transaction_items.id) as total_terjual 
              FROM books 
              JOIN transaction_items ON books.id = transaction_items.book_id
              JOIN transactions ON transaction_items.transaction_id = transactions.id
              WHERE transactions.status = 'success'
              GROUP BY books.id 
              ORDER BY total_terjual DESC 
              LIMIT 3"; 
              
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query Error di dashboardUserModel: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}