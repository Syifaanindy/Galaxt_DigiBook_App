<?php
session_start();
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/transaksiModel.php';

requireRole('admin');

$tgl_mulai = $_GET['tgl_mulai'] ?? null;
$tgl_akhir = $_GET['tgl_akhir'] ?? null;

// Mengubah penamaan variabel ke '$page' agar sinkron dengan partials/pagination.php
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Ambil total data untuk menghitung total halaman
$totalData = hitungTotalTransaksi($conn, $tgl_mulai, $tgl_akhir);
$total_pages = ceil($totalData / $limit);

// Ambil data transaksi spesifik per halaman
$daftarTransaksi = ambilSemuaTransaksi($conn, $tgl_mulai, $tgl_akhir, $limit, $offset);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Transaksi</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/admin/panel.css">
  <link rel="stylesheet" href="../../assets/css/admin/sidebar.css">
  <link rel="stylesheet" href="../../assets/css/admin/pagination.css">
</head>
<body>
  <div class="admin-layout">
        <?php include 'partials/sidebar.php'; ?>
    <main class="main-content">
      <header class="topbar"><h2>Transaksi</h2><p>Monitor status pembayaran dan order buku.</p></header>
      <section class="panel">
        <h3>Filter Transaksi</h3>
        <form class="form-grid" style="grid-template-columns: repeat(3, auto); gap: 25px;">
          <input type="hidden" name="action" value="transaksi"> 
          
          <div class="field">
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?? date('Y-m-01') ?>">
          </div>
          <div class="field">
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?? date('Y-m-d') ?>">
          </div>
          <div class="field" style="display: flex; align-items: flex-end; gap: 10px;">
              <div style="display: flex; gap: 10px; padding-bottom: 2px;"> 
                  <button class="btn btn-primary" type="submit" style="white-space: nowrap; padding: 8px 20px;">
                      Terapkan Filter
                  </button>
                  <a href="transaksi.php" class="btn btn-secondary" style="white-space: nowrap; padding: 8px 20px; text-decoration: none; display: flex; align-items: center;">
                      Reset
                  </a>
              </div>
          </div>
        </form>
      </section>

      <section class="panel">
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>User</th>
                <th>Buku</th>
                <th>Total</th>   
                <th>Tanggal</th> 
                <th>Status</th> 
              </tr>
            </thead>
            <tbody>
                <?php if (!empty($daftarTransaksi)): ?>
                  <?php foreach ($daftarTransaksi as $row) : 
                      $tanggal = date('d-m-Y H:i', strtotime($row['transaction_date']));
                  ?>
                  <tr>
                      <td><?= htmlspecialchars($row['transaction_code']); ?></td>
                      <td><?= htmlspecialchars($row['username']); ?></td>
                      <td><?= htmlspecialchars($row['title']); ?></td>
                      <td>Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                      <td><?= $tanggal; ?> WIB</td>
                      <td>
                          <?php $statusReal = strtolower(trim($row['status'])); ?>
                          <?php if ($statusReal === 'success'): ?>
                              <span class="badge success"><?= ucfirst($statusReal); ?></span>
                          <?php elseif ($statusReal === 'failed'): ?>
                              <span class="badge danger"><?= ucfirst($statusReal); ?></span>
                          <?php else: ?>
                              <span class="badge warning"><?= ucfirst($statusReal); ?></span>
                          <?php endif; ?>
                      </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="6" class="text-center">Data transaksi tidak ditemukan</td></tr>
                <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php 
          $total_data = $totalData; 
          $url_params = [];
          if ($tgl_mulai) $url_params['tgl_mulai'] = $tgl_mulai;
          if ($tgl_akhir) $url_params['tgl_akhir'] = $tgl_akhir;
          
          $target_url = 'transaksi.php' . (!empty($url_params) ? '?' . http_build_query($url_params) : ''); 
          include 'partials/pagination.php'; 
        ?>
      </section>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>