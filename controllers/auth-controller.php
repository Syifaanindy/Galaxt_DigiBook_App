<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../models/user-model.php';

$aksi = $_GET['action'] ?? '';

if ($aksi === 'login') {
    prosesLogin();
} elseif ($aksi === 'register') {
    prosesRegister();
} elseif ($aksi === 'create-default-admin') {
    prosesCreateDefaultAdmin();
} elseif ($aksi === 'logout') {
    prosesLogout();
} else {
    include __DIR__ . '/../views/auth/auth.php';
}

function prosesLogin() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectAuth();
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password wajib diisi.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    $dataUser = cariUserByUsername($conn, $username);

    if ($dataUser && password_verify($password, $dataUser['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']  = $dataUser['id'];
        $_SESSION['username'] = $dataUser['username'];
        $_SESSION['role']     = $dataUser['role'];

        redirectByRole($dataUser['role']);
        exit;
    } else {
        $_SESSION['error'] = "Username atau password salah.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }
}

function prosesRegister() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectAuth();
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'user';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Username, Email, dan Password wajib diisi.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (strlen($username) < 3) {
        $_SESSION['error'] = "Username minimal 3 karakter.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password minimal 6 karakter.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (usernameSudahTerdaftar($conn, $username)) {
        $_SESSION['error'] = "Username sudah terdaftar.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (!buatUser($conn, $username, $email, $password, $role)) {
        $_SESSION['error'] = "Registrasi gagal. Silakan coba lagi.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
    $_SESSION['auth_mode'] = 'login';
    redirectAuth();
    exit;
}

function prosesLogout() {
    session_unset();
    session_destroy();
    redirectAuth();
    exit;
}

function prosesCreateDefaultAdmin() {
    global $conn;

    if (adminSudahAda($conn)) {
        $_SESSION['success'] = "Admin default sudah ada. Silakan login.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    if (usernameSudahTerdaftar($conn, 'admin')) {
        $_SESSION['error'] = "Username admin sudah dipakai, tapi role admin belum ada.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    if (!buatUser($conn, 'admin', 'admin123', 'admin')) {
        $_SESSION['error'] = "Gagal membuat admin default.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    $_SESSION['success'] = "Admin default berhasil dibuat. Login dengan username admin dan password admin123.";
    $_SESSION['auth_mode'] = 'login';
    redirectAuth();
    exit;
}

function redirectAuth() {
    header("Location: " . base_url('views/auth/auth.php'));
}

function redirectByRole($role) {
    if ($role === 'admin') {
        header("Location: " . base_url('views/admin/dashboard.php'));
        return;
    }

    header("Location: " . base_url('views/user/dashboard.php'));
}
?>
