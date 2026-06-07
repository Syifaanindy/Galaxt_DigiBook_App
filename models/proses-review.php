<?php
session_start();
require_once __DIR__ . '/../config/database.php'; // Sesuaikan path jika perlu

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $book_id = filter_input(INPUT_POST, 'id_buku', FILTER_SANITIZE_NUMBER_INT);
    $rating  = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_INT);
    $ulasan  = trim($_POST['ulasan'] ?? '');

    // Validasi dasar
    if (empty($book_id) || empty($rating) || empty($ulasan)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi!']);
        exit;
    }

    // Menggunakan prepared statement untuk keamanan (mencegah SQL Injection)
    $stmt = $conn->prepare("INSERT INTO book_reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $book_id, $rating, $ulasan);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Ulasan Anda berhasil dikirim!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ulasan ke database.']);
    }

    $stmt->close();
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}
?>