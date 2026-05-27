<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil file database, helper, dan model yang sudah disesuaikan namanya
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../models/buku-model.php';

$action = $_GET['action'] ?? '';

// Memilah aksi berdasarkan request form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        prosesTambahBuku();
    } elseif ($action === 'update') {
        prosesUpdateBuku();
    }
} elseif ($action === 'delete') {
    prosesHapusBuku();
} else {
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}

function prosesTambahBuku() {
    global $conn;
    
    // Mengambil data teks dan angka dari form
    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $publisher   = trim($_POST['publisher']);
    $category_id = intval($_POST['category_id']);
    $synopsis    = trim($_POST['synopsis']);
    $price       = intval($_POST['price'] ?? 0); // Menangkap data harga (price)

    // Membuat folder upload jika belum ada
    $target_dir = __DIR__ . "/../assets/book/";
    $target_diir = __DIR__ . "/../assets/cover/";
    if (!is_dir($target_dir)) { 
        mkdir($target_dir, 0777, true); 
    }

    // Proses upload File Buku (PDF)
    $file_path = "";
    if (!empty($_FILES['file_buku']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_buku']['name']);
        if (move_uploaded_file($_FILES['file_buku']['tmp_name'], $target_dir . $file_name)) {
            $file_path = "assets/book/" . $file_name;
        }
    }

    // Proses upload Cover Buku (Gambar)
    $cover_image = "";
    if (!empty($_FILES['cover_buku']['name'])) {
        $cover_name = time() . "_" . basename($_FILES['cover_buku']['name']);
        if (move_uploaded_file($_FILES['cover_buku']['tmp_name'], $target_diir . $cover_name)) {
            $cover_image = "assets/cover/" . $cover_name;
        }
    }

    // Mengirimkan 9 parameter lengkap ke fungsi model
    if (tambahBuku($conn, $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price)) {
        $_SESSION['success'] = "Buku berhasil ditambahkan ke katalog!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan data buku.";
    }
    
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}

function prosesUpdateBuku() {
    global $conn;
    
    $id          = intval($_POST['id']);
    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $publisher   = trim($_POST['publisher']);
    $category_id = intval($_POST['category_id']);
    $synopsis    = trim($_POST['synopsis']);
    $price       = intval($_POST['price'] ?? 0); 
    $bukuLama = ambilBukuById($conn, $id);
    $file_path = $bukuLama['file_path'];
    $cover_image = $bukuLama['cover_image'];
    
     $target_dir = __DIR__ . "/../assets/book/";
    $target_diir = __DIR__ . "/../assets/cover/";;

    if (!empty($_FILES['file_buku']['name'])) {
        $file_name = time() . "_" . basename($_FILES['file_buku']['name']);
        if (move_uploaded_file($_FILES['file_buku']['tmp_name'], $target_dir . $file_name)) {
            $file_path = "assets/book/" . $file_name;
        }
    }

    if (!empty($_FILES['cover_buku']['name'])) {
        $cover_name = time() . "_" . basename($_FILES['cover_buku']['name']);
        if (move_uploaded_file($_FILES['cover_buku']['tmp_name'], $target_diir . $cover_name)) {
            $cover_image = "assets/cover/" . $cover_name;
        }
    }


    if (updateBuku($conn, $id, $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price)) {
        $_SESSION['success'] = "Data buku berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui data buku.";
    }
    
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}

function prosesHapusBuku() {
    global $conn;
    $id = intval($_GET['id'] ?? 0);
    
    if ($id > 0 && hapusBuku($conn, $id)) {
        $_SESSION['success'] = "Buku berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data buku.";
    }
    
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}
?>