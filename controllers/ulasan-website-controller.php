<?php
session_start();

// Menggunakan path berbasis __DIR__ menuju file model (huruf kecil semua)
require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../models/ulasan-website-model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // MEMPERBAIKI TYPO: Sekarang menangkap $_POST['nama'] dengan benar
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $ulasan = isset($_POST['ulasan']) ? trim($_POST['ulasan']) : '';
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    // Validasi memastikan tidak ada data form yang kosong
    if (empty($nama) || empty($email) || empty($ulasan) || $rating < 1 || $rating > 5) {
        header("Location: ../views/user/kontak.php?status=invalid");
        exit;
    }

    $ulasanModel = new UlasanWebsiteModel($conn);
    // Kirimkan ke model: nama, email, rating, ulasan
    $saved = $ulasanModel->simpanUlasan($nama, $email, $rating, $ulasan);

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