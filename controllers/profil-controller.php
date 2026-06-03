<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/profil-model.php';

if (!isset($_SESSION['email'])) {
    header("Location: ../views/user/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_lama = $_SESSION['email'];
    $username   = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email_baru  = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password   = isset($_POST['password']) ? trim($_POST['password']) : '';

    $profilModel = new ProfilModel($conn);

    // 1. Ambil data user lama untuk mengecek foto lama
    $userLama = $profilModel->getProfilByEmail($email_lama);
    $foto_profil = $userLama['picture'] ?? null;

    // 2. Logika Proses Upload Foto Profil
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['picture']['tmp_name'];
        $fileName    = $_FILES['picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Batasi ekstensi file yang valid
        $extensionsBoleh = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $extensionsBoleh)) {
            // Buat nama file baru yang unik (contoh: foto_4_17182910.png)
            $newFileName = 'foto_' . $userLama['id'] . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../assets/img/profile/';

            // Buat folder assets/img/profile jika belum ada otomatis
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Hapus foto profil lama dari folder jika ada
                if (!empty($foto_profil) && file_exists($uploadFileDir . $foto_profil)) {
                    unlink($uploadFileDir . $foto_profil);
                }
                $foto_profil = $newFileName;
            }
        }
    }

    // 3. Eksekusi update data ke database
    $updated = $profilModel->updateProfil($email_lama, $username, $email_baru, $password, $foto_profil);

    if ($updated) {
        // Update session jika email ikut diganti
        $_SESSION['email'] = $email_baru;
        header("Location: ../views/user/profil.php?status=updated");
    } else {
        header("Location: ../views/user/profil.php?status=failed");
    }
    exit;
}