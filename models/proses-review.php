<?php
session_start();
include '../config/database.php'; 

header('Content-Type: application/json'); // Penting!

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $book_id = mysqli_real_escape_string($conn, $_POST['id_buku']);
    $rating  = mysqli_real_escape_string($conn, $_POST['rating']);
    $ulasan  = mysqli_real_escape_string($conn, $_POST['ulasan']);

    if (empty($book_id) || empty($rating) || empty($ulasan)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi!']);
        exit;
    }

    $query = "INSERT INTO book_reviews (user_id, book_id, rating, comment) VALUES ('$user_id', '$book_id', '$rating', '$ulasan')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Terima kasih atas ulasan Anda!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada database.']);
    }
}
?>