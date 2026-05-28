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