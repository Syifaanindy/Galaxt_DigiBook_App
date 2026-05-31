<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../models/kategori-model.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'create') {
        prosesTambahKategori();
    }

    elseif ($action === 'update') {
        prosesUpdateKategori();
    }

} elseif ($action === 'delete') {

    prosesHapusKategori();

}

function prosesTambahKategori() {
    global $conn;

    $category_name = trim($_POST['category_name']);

    // 1. CEK DUPLIKAT: Menggunakan nama tabel 'category' sesuai database kamu
    $query_cek = "SELECT * FROM category WHERE category_name = '$category_name'";
    $result_cek = mysqli_query($conn, $query_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        // Jika nama kategori sudah ada, gagalkan proses dan kirim alert merah
        $_SESSION['error'] = "Gagal! Kategori '$category_name' sudah ada.";
        header("Location: " . base_url('views/admin/kategori-buku.php'));
        exit;
    }

    // 2. Jika lolos cek, jalankan fungsi insert bawaan dari model
    if (tambahKategori($conn, $category_name)) {
        $_SESSION['success'] = "Kategori berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan kategori.";
    }

    header("Location: " . base_url('views/admin/kategori-buku.php'));
    exit;
}

function prosesUpdateKategori() {
    global $conn;

    $id = intval($_POST['id']);
    $category_name = trim($_POST['category_name']);

    // 1. CEK DUPLIKAT SAAT EDIT: Mencegah nama kembar milik data lain di tabel 'category'
    $query_cek = "SELECT * FROM category WHERE category_name = '$category_name' AND id != $id";
    $result_cek = mysqli_query($conn, $query_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        // Jika nama baru sudah dipakai oleh kategori lain, gagalkan proses edit
        $_SESSION['error'] = "Gagal! Nama kategori '$category_name' sudah digunakan oleh data lain.";
        header("Location: " . base_url('views/admin/kategori-buku.php'));
        exit;
    }

    // 2. Jika aman, lakukan update data via model
    if (updateKategori($conn, $id, $category_name)) {
        $_SESSION['success'] = "Kategori berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui kategori.";
    }

    header("Location: " . base_url('views/admin/kategori-buku.php'));
    exit;
}

function prosesHapusKategori() {
    global $conn;

    $id = intval($_GET['id']);

    if (hapusKategori($conn, $id)) {
        $_SESSION['success'] = "Kategori berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus kategori.";
    }

    header("Location: " . base_url('views/admin/kategori-buku.php'));
    exit;
}

?>