<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Muat file konfigurasi dan model dengan path yang sesuai nama file Anda
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../models/invoice.php'; // <-- Sudah disesuaikan ke invoice.php

// 2. Proteksi Akses: User wajib login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('views/auth/login.php'));
    exit;
}

// 3. Ambil parameter kode transaksi dari URL
$transaction_code = $_GET['code'] ?? '';

if (empty($transaction_code)) {
    die("Error: Kode transaksi tidak valid.");
}

// 4. Ambil data dari database melalui fungsi di Model
$transaksi = ambilDetailTransaksiPerKode($conn, $transaction_code);

// Validasi jika data tidak ditemukan
if (!$transaksi) {
    die("Error: Data invoice tidak ditemukan di sistem.");
}

// Proteksi Keamanan: User hanya boleh mengunduh invoice miliknya sendiri
if ($transaksi['user_id'] != $_SESSION['user_id']) {
    die("Error: Anda tidak memiliki akses untuk melihat invoice ini.");
}

// 5. Panggil file tampilan (View) yang berada di folder views/user/
include __DIR__ . '/../views/user/invoice-jpg.php';
?>