<?php
session_start();
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/user-model.php';

requireRole('admin');

// --- LOGIKA PAGINATION ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$limit = 5;
$offset = ($page - 1) * $limit;

// Menghitung total pengunjung untuk pagination
$totalData = hitungTotalPengunjung($conn);
$total_pages = ceil($totalData / $limit);

// Mengambil data pengunjung khusus halaman ini
$daftarUser = ambilPengunjungPaging($conn, $limit, $offset);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - User</title>
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
      <header class="topbar">
        <h2>User</h2>
        <p>Memantau akun pengguna.</p>
      </header>

      <section class="panel">
        <div class="actions mb-3">
          <h3 style="margin:0;">Data User</h3>
        </div>
        
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody id="userTableBody"> 
              <?php if (!empty($daftarUser)): ?>
                <?php foreach ($daftarUser as $user): ?>
                  <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="2" class="text-center">Belum ada data user.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php 
          $total_data = $totalData; 
          $target_url = 'user.php'; // Sesuaikan jika nama file kamu berbeda, misal 'users.php'
          include 'partials/pagination.php'; 
        ?>

      </section>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>