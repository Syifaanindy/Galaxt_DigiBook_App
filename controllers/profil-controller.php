<?php
session_start();

require_once dirname(__DIR__, 1) . '/config/database.php';
require_once dirname(__DIR__, 1) . '/models/profil-model.php';

if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'admin@local.test';
}

$email_lama = $_SESSION['email'];

// Instansiasi class model
$profilModel = new ProfilModel($conn);

// Cari data user lama untuk mempertahankan gambar lama jika user tidak upload gambar baru
$userLama = $profilModel->getProfilByEmail($email_lama);

// KUNCI NAMA FILE LAMA DI SINI (Ambil hanya nama filenya saja, bukan path lengkap)
$picture_lama = $userLama['picture'] ?? null; 
$picture      = $picture_lama;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $email_baru = trim($_POST['email']);
    $password   = ''; // Field password dihapus dari form, selalu kosong

    // --- PROSES UPLOAD GAMBAR BARU ---
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['picture']['tmp_name'];
        $fileName      = $_FILES['picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Membuat nama unik file gambar baru
            $newFileName = 'profile_' . time() . '_' . uniqid() . '.' . $fileExtension;
            
            // Lokasi folder penyimpanan gambar profile
            $uploadFileDir = dirname(__DIR__, 1) . '/assets/img/profile/';
            
            // Generate otomatis folder jika belum ada di server
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // HAPUS BERKAS GAMBAR LAMA MENGGUNAKAN VARIABEL YANG SUDAH DIKUNCI DI AWAL
                if (!empty($picture_lama) && file_exists($uploadFileDir . $picture_lama)) {
                    @unlink($uploadFileDir . $picture_lama);
                }
                // Ubah variabel gambar ke nama file yang baru
                $picture = $newFileName;
            }
        }
    }

    // --- PANGGIL METHOD UPDATE DARI MODEL ANDA ---
    $isUpdated = $profilModel->updateProfil($email_lama, $username, $email_baru, $password, $picture);

    if ($isUpdated) {
        // Segarkan session email dengan email yang baru diperbarui
        $_SESSION['email'] = $email_baru;
        
        // Redirect kembali ke view dengan alert sukses
        header("Location: ../views/user/profil.php?status=updated");
        exit();
    } else {
        // Redirect kembali ke view dengan alert gagal
        header("Location: ../views/user/profil.php?status=failed");
        exit();
    }
} else {
    header("Location: ../views/user/profil.php");
    exit();
}