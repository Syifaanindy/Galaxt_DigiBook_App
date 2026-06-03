<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghubungkan ke database & model
require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../models/ulasan-website-model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sekarang data ini sudah aman karena form di HTML sudah memilikinya
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $ulasan = isset($_POST['ulasan']) ? trim($_POST['ulasan']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    // Jalur redirect disesuaikan agar kembali ke view dengan benar
    if (empty($nama) || empty($email) || empty($ulasan) || $rating < 1 || $rating > 5) {
        header("Location: " . $_SERVER['HTTP_REFERER'] ?: "../views/user/kontak.php?status=invalid");
        exit;
    }

    $ulasanModel = new UlasanWebsiteModel($conn);
    $saved = $ulasanModel->simpanUlasan($nama, $email, $rating, $ulasan);

    if ($saved) {
        // Menggunakan HTTP_REFERER agar otomatis kembali ke URL asal halaman form berada
        header("Location: " . explode('?', $_SERVER['HTTP_REFERER'])[0] . "?status=success");
    } else {
        header("Location: " . explode('?', $_SERVER['HTTP_REFERER'])[0] . "?status=failed");
    }
    exit;
}
?>