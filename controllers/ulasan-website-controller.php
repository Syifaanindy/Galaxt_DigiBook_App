<?php
session_start();

require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../models/ulasan-website-model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dapatkan ID user dari session, jika tidak ada pakai dari input hidden POST
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['id']) ? (int)$_SESSION['id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0));
    
    $ulasan = isset($_POST['ulasan']) ? trim($_POST['ulasan']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    // Pastikan user memang sudah ter-autentikasi (ID > 0)
    if ($user_id <= 0 || empty($ulasan) || $rating < 1 || $rating > 5) {
        header("Location: ../views/user/kontak.php?status=invalid");
        exit;
    }

    $ulasanModel = new UlasanWebsiteModel($conn);
    // Masukkan rating, comment, dan user_id ke model database terbaru
    $saved = $ulasanModel->simpanUlasan($rating, $ulasan, $user_id);

    if ($saved) {
        header("Location: ../views/user/kontak.php?status=success");
    } else {
        header("Location: ../views/user/kontak.php?status=failed");
    }
    exit;
} else {
    header("Location: ../views/user/kontak.php");
    exit;
}