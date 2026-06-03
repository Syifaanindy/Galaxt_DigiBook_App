<?php
session_start();
require_once __DIR__ . '/config/url-helper.php';

$aksi = $_GET['action'] ?? 'home';

switch ($aksi) {
    case 'login':
    case 'register':
    case 'create-default-admin':
    case 'logout':
        require_once __DIR__ . '/controllers/auth-controller.php';
        break;

    case 'dashboard':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: " . base_url('views/admin/dashboard.php'));
        } else {
            header("Location: " . base_url('views/user/dashboard.php'));
        }
        exit;

    default:
        if (isset($_SESSION['user_id'])) {
            header("Location: " . base_url('index.php?action=dashboard'));
        } else {
            header("Location: " . base_url('views/auth/auth.php'));
        }
        break;
}
?>
