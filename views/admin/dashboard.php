<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. Load semua helper & database
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/dashboard-admin-models.php';

// 2. Proteksi halaman, pastikan hanya admin yang bisa masuk
requireRole('admin');

// 3. INISIALISASI MODEL DENGAN VARIABEL KONEKSI YANG BENAR
global $conn; // Menggunakan $conn sesuai konfigurasi database.php kamu

// Membuat objek model dengan mengoper koneksi $conn
$model = new DashboardAdminModel($conn); 

// Mengambil data untuk kartu KPI dan Chart
$totalPenjualan = $model->getTotalPenjualan();
$totalOrder     = $model->getTotalOrder();
$rataRata       = $totalOrder > 0 ? ($totalPenjualan / $totalOrder) : 0;

$kategoriData   = $model->getBukuPerKategori();
$bulananData    = $model->getPenjualanBulanan();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Galaxy Digi Book</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/admin/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <?php include 'partials/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="topbar">
                <h2>Selamat Datang, Dashboard Admin</h2>
                <p>Kelola data buku, transaksi, laporan, dan pengguna.</p>
            </header>

            <section class="cards">
                <article class="card">
                    <h3>Total Penjualan</h3>
                    <p><?= number_format($totalPenjualan, 0, ',', '') ?></p>
                </article>
                <article class="card">
                    <h3>Total Order</h3>
                    <p><?= $totalOrder ?></p>
                </article>
                <article class="card">
                    <h3>Rata-Rata</h3>
                    <p><?= number_format($rataRata, 4, '.', '') ?></p>
                </article>
            </section>

            <div class="dashboard-bottom-layout">
                
                <section class="info-box chart-column" style="background: #fff; padding: 24px; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                    <div class="box-header" style="margin-bottom: 16px;">
                        <h3 style="font-size: 18px; color: #1f2937;"><i class="fa-solid fa-chart-line"></i> Monthly Sales</h3>
                    </div>
                    <div class="chart-wrapper" style="position: relative; height: 280px;">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </section>

                <section class="info-box chart-column" style="background: #fff; padding: 24px; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                    <div class="box-header" style="margin-bottom: 16px;">
                        <h3 style="font-size: 18px; color: #1f2937;"><i class="fa-solid fa-book"></i> Category Book</h3>
                    </div>
                    <div class="chart-wrapper" style="position: relative; height: 280px;">
                        <canvas id="categoryBookChart"></canvas>
                    </div>
                </section>
                
            </div>
        </main>
    </div>

    <script>
        // Membaca data kiriman query model dalam format objek JSON
        const phpBulananData = <?= json_encode($bulananData); ?>;
        const labelsBulan = phpBulananData.map(item => item.bulan);
        const dataPenjualan = phpBulananData.map(item => item.total);

        const phpKategoriData = <?= json_encode($kategoriData); ?>;
        const labelsKategori = phpKategoriData.map(item => item.kategori); 
        const dataJumlahBuku = phpKategoriData.map(item => item.jumlah);

        // Rendering Line Chart (Monthly Sales)
        const ctxLine = document.getElementById('monthlySalesChart').getContext('2d');
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: labelsBulan.length ? labelsBulan : ['Jan', 'Feb', 'Mar'],
                datasets: [{
                    label: 'Monthly Sales',
                    data: dataPenjualan.length ? dataPenjualan : [0, 0, 0],
                    borderColor: '#3b82f6',
                    backgroundColor: '#3b82f6',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    tension: 0 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Rendering Bar Chart (Category Book)
        const ctxBar = document.getElementById('categoryBookChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labelsKategori.length ? labelsKategori : ['Kosong'],
                datasets: [{
                    label: 'Total Buku',
                    data: dataJumlahBuku.length ? dataJumlahBuku : [0],
                    backgroundColor: '#3b82f6',
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) { return value + ' Buku'; }
                        }
                    }
                }
            }
        });
    </script>
    <script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>