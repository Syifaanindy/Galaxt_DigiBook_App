<?php
require_once __DIR__ . '/url-helper.php';

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . base_url('views/auth/auth.php'));
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        echo "<h2>403 - Akses Ditolak</h2>";
        echo "<p>Anda tidak memiliki izin mengakses halaman ini.</p>";
        echo "<a href='" . base_url('index.php') . "'>Kembali</a>";
        exit;
    }
}

function sudahLogin() {
    return isset($_SESSION['user_id']);
}
?>