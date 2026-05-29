<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil koneksi database dan file model tadi
require_once __DIR__ . '/../config/database.php'; 
require_once __DIR__ . '/../models/dashboard-admin-models.php';

class DashboardAdminController {
    public function index() {
        global $pdo; 

        $model = new DashboardAdminModel($pdo);

        // Siapkan variabel untuk dilempar ke View
        $totalPenjualan = $model->getTotalPenjualan();
        $totalOrder = $model->getTotalOrder();
        $rataRata = $totalOrder > 0 ? ($totalPenjualan / $totalOrder) : 0;
        
        $kategoriData = $model->getBukuPerKategori();
        $bulananData = $model->getPenjualanBulanan();

        // Panggil View dashboard dari dalam Controller
        require_once __DIR__ . '/../views/admin/dashboard.php'; 
    }
}

// Jalankan controllernya langsung
$controller = new DashboardAdminController();
$controller->index();