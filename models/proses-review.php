<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda harus login terlebih dahulu.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak valid.'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = (int)($_POST['id_buku'] ?? 0);
$rating  = (int)($_POST['rating'] ?? 0);
$ulasan  = trim($_POST['ulasan'] ?? '');

/*
|--------------------------------------------------------------------------
| Validasi Input
|--------------------------------------------------------------------------
*/
if ($book_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID buku tidak valid.'
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Rating harus antara 1 sampai 5.'
    ]);
    exit;
}

if (empty($ulasan)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ulasan tidak boleh kosong.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek apakah user sudah pernah review buku ini
|--------------------------------------------------------------------------
*/
$cek = $conn->prepare("
    SELECT id
    FROM book_reviews
    WHERE user_id = ? AND book_id = ?
");

$cek->bind_param("ii", $user_id, $book_id);
$cek->execute();
$result = $cek->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda sudah memberikan ulasan untuk buku ini.'
    ]);
    exit;
}

$cek->close();

/*
|--------------------------------------------------------------------------
| Simpan Review
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    INSERT INTO book_reviews
    (user_id, book_id, rating, comment)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "iiis",
    $user_id,
    $book_id,
    $rating,
    $ulasan
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Ulasan berhasil dikirim.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database Error: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
exit;
?>