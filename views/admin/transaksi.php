<?php
session_start();
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/transaksiModel.php';

requireRole('admin');

$tgl_mulai = $_GET['tgl_mulai'] ?? null;
$tgl_akhir = $_GET['tgl_akhir'] ?? null;

$daftarTransaksi = ambilSemuaTransaksi($conn, $tgl_mulai, $tgl_akhir);
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
</head>
<body>
  <div class="admin-layout">
        <?php include 'partials/sidebar.php'; ?>
    <main class="main-content">
      <header class="topbar"><h2>Transaksi</h2><p>Monitor status pembayaran dan order buku.</p></header>
      <section class="panel">
        <h3>Filter Transaksi</h3>
        <form class="form-grid" method="GET" action="">
          <input type="hidden" name="action" value="transaksi"> 
          
          <div class="field">
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?? date('Y-m-01') ?>">
          </div>
          <div class="field">
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?? date('Y-m-d') ?>">
          </div>
          <div class="field" style="grid-column: span 3; display: flex; flex-direction: column; justify-content: flex-end;">
              <label>&nbsp;</label>
              <div style="display: flex; gap: 10px; width: 100%;">
                  <button class="btn btn-primary" type="submit" style="flex: 1;">Terapkan Filter</button>
                  <a href="transaksi.php" class="btn btn-secondary" style="flex: 1;">Reset</a>
              </div>
          </div>
        </form>
      </section>
      <section class="panel table-wrap">
        <table>
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
              <?php 
                $daftarTransaksi = ambilSemuaTransaksi($conn, $tgl_mulai, $tgl_akhir);
                foreach ($daftarTransaksi as $row) : 
                    $tanggal = date('d-m-Y H:i', strtotime($row['transaction_date']));
                ?>
                <tr>
                    <td><?= $row['transaction_code']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td><?= $row['title']; ?></td>
                    
                    <td>Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                    
                    <td><?= $tanggal; ?> WIB</td>
                    <td>
                        <?php 
                        // Paksa semua jadi huruf kecil dulu buat pengecekan
                        $statusReal = strtolower(trim($row['status'])); 
                        ?>

                        <?php if ($statusReal === 'success'): ?>
                            <span class="badge success">
                                <?= ucfirst($statusReal); ?> </span>
                        <?php elseif ($statusReal === 'failed'): ?>
                            <span class="badge danger">
                                <?= ucfirst($statusReal); ?> </span>
                        <?php endif; ?>
                    </td>
                </tr>
              <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
  <script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>

