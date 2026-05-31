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
    $price       = intval($_POST['price'] ?? 0);

    //buat folder penyimpanan
    $target_dir = __DIR__ . "/../assets/book/";
    $target_diir = __DIR__ . "/../assets/cover/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    if (!is_dir($target_diir)) { mkdir($target_diir, 0777, true); }


    $file_path = "";
    if (!empty($_FILES['file_buku']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_buku']['name'], PATHINFO_EXTENSION));
        
     
        if ($file_ext !== 'pdf') {
            $_SESSION['error'] = "Gagal! Format File Buku wajib berformat PDF.";
            header("Location: " . base_url('views/admin/katalog-buku.php'));
            exit;
        }

        $file_name = time() . "_" . basename($_FILES['file_buku']['name']);
        if (move_uploaded_file($_FILES['file_buku']['tmp_name'], $target_dir . $file_name)) {
            $file_path = "assets/book/" . $file_name;
        }
    }

    $cover_image = "";
    if (!empty($_FILES['cover_buku']['name'])) {
        $cover_ext = strtolower(pathinfo($_FILES['cover_buku']['name'], PATHINFO_EXTENSION));
        $allowed_cover_ext = ['png', 'jpg', 'jpeg'];


        if (!in_array($cover_ext, $allowed_cover_ext)) {
        
            if (!empty($file_path) && file_exists(__DIR__ . "/../" . $file_path)) {
                unlink(__DIR__ . "/../" . $file_path);
            }
            $_SESSION['error'] = "Gagal! Format Cover Buku wajib berupa PNG, JPG, atau JPEG.";
            header("Location: " . base_url('views/admin/katalog-buku.php'));
            exit;
        }

        $cover_name = time() . "_" . basename($_FILES['cover_buku']['name']);
        if (move_uploaded_file($_FILES['cover_buku']['tmp_name'], $target_diir . $cover_name)) {
            $cover_image = "assets/cover/" . $cover_name;
        }
    }

    // Mengirimkan parameter lengkap ke fungsi model
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
    
    $bukuLama    = ambilBukuById($conn, $id);
    $file_path   = $bukuLama['file_path'];
    $cover_image = $bukuLama['cover_image'];
    
    $target_dir  = __DIR__ . "/../assets/book/";
    $target_diir = __DIR__ . "/../assets/cover/";

    
    $new_file_uploaded = false;
    $temp_new_file = "";

    if (!empty($_FILES['file_buku']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_buku']['name'], PATHINFO_EXTENSION));
        
        if ($file_ext !== 'pdf') {
            $_SESSION['error'] = "Gagal! File Buku baru harus berformat PDF.";
            header("Location: " . base_url('views/admin/katalog-buku.php'));
            exit;
        }

        $file_name = time() . "_" . basename($_FILES['file_buku']['name']);
        if (move_uploaded_file($_FILES['file_buku']['tmp_name'], $target_dir . $file_name)) {
            $temp_new_file = "assets/book/" . $file_name;
            $new_file_uploaded = true;
        }
    }


    if (!empty($_FILES['cover_buku']['name'])) {
        $cover_ext = strtolower(pathinfo($_FILES['cover_buku']['name'], PATHINFO_EXTENSION));
        $allowed_cover_ext = ['png', 'jpg', 'jpeg'];

        if (!in_array($cover_ext, $allowed_cover_ext)) {
 
            if ($new_file_uploaded && file_exists(__DIR__ . "/../" . $temp_new_file)) {
                unlink(__DIR__ . "/../" . $temp_new_file);
            }
            $_SESSION['error'] = "Gagal! Cover Buku baru harus berformat PNG, JPG, atau JPEG.";
            header("Location: " . base_url('views/admin/katalog-buku.php'));
            exit;
        }

        $cover_name = time() . "_" . basename($_FILES['cover_buku']['name']);
        if (move_uploaded_file($_FILES['cover_buku']['tmp_name'], $target_diir . $cover_name)) {
            
            if (!empty($bukuLama['cover_image']) && file_exists(__DIR__ . "/../" . $bukuLama['cover_image'])) {
                unlink(__DIR__ . "/../" . $bukuLama['cover_image']);
            }
            $cover_image = "assets/cover/" . $cover_name;
        }
    }

    if ($new_file_uploaded) {
        if (!empty($bukuLama['file_path']) && file_exists(__DIR__ . "/../" . $bukuLama['file_path'])) {
            unlink(__DIR__ . "/../" . $bukuLama['file_path']);
        }
        $file_path = $temp_new_file;
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
    
    if ($id > 0) {
        $buku = ambilBukuById($conn, $id);
        
        if ($buku) {
            if (hapusBuku($conn, $id)) {
                if (!empty($buku['file_path'])) {
                    $physical_file_path = __DIR__ . "/../" . $buku['file_path'];
                    if (file_exists($physical_file_path)) { unlink($physical_file_path); }
                }
                
                if (!empty($buku['cover_image'])) {
                    $physical_cover_path = __DIR__ . "/../" . $buku['cover_image'];
                    if (file_exists($physical_cover_path)) { unlink($physical_cover_path); }
                }
                
                $_SESSION['success'] = "Buku beserta berkas filenya berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data buku dari database.";
            }
        } else {
            $_SESSION['error'] = "Data buku tidak ditemukan.";
        }
    } else {
        $_SESSION['error'] = "ID Buku tidak valid.";
    }
    
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}
?>