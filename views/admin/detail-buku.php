<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/buku-model.php';

requireRole('admin');

$id_buku = intval($_GET['id'] ?? 0);
$buku = ambilBukuById($conn, $id_buku);

if (!$buku) {
    $_SESSION['error'] = "Data buku tidak ditemukan.";
    header("Location: " . base_url('views/admin/katalog-buku.php'));
    exit;
}

$list_kategori = ambilSemuaKategori($conn);
$nama_kategori = "Tanpa Kategori";
foreach ($list_kategori as $kat) {
    if ($kat['id'] == $buku['category_id']) {
        $nama_kategori = $kat['category_name'];
        break;
    }
}

// DINAMIS: Ambil rata-rata rating dari tabel book_reviews
$query_rating = mysqli_query($conn, "SELECT AVG(rating) as rata_rating FROM book_reviews WHERE book_id = $id_buku");
$data_rating = mysqli_fetch_assoc($query_rating);
$rating_angka = $data_rating['rata_rating'];

// Format tampilan rating (jika belum ada review, set ke 0.0)
$tampilan_rating = (!is_null($rating_angka)) ? number_format($rating_angka, 1, '.', '') . " / 5.0" : "Belum Ada Rating";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Katalog Buku</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../../assets/css/admin/panel.css">
    <link rel="stylesheet" href="../../assets/css/admin/sidebar.css">
        <link rel="stylesheet" href="../../assets/css/admin/detail.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>
    
    <main class="main-content">
      <section class="panel">
        <h2 class="main-title">Informasi Detail Buku</h2>
        
        <div class="book-detail-container">
            <div class="book-left-side">
                <div class="book-cover-wrapper">
                    <?php if (!empty($buku['cover_image']) && file_exists(__DIR__ . '/../../' . $buku['cover_image'])): ?>
                        <img src="<?= base_url($buku['cover_image']); ?>" class="book-cover-detail" alt="Cover Buku">
                    <?php else: ?>
                        <div class="no-cover-box">
                            <i class="fa-regular fa-image fa-3x mb-2"></i>
                            <p class="m-0">Tidak ada cover</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($buku['file_path']) && file_exists(__DIR__ . '/../../' . $buku['file_path'])): ?>
                    <a href="<?= base_url($buku['file_path']); ?>" class="pdf-card-download" download>
                        <div class="pdf-icon-box">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div class="pdf-info-text">
                            <span class="pdf-filename"><?= htmlspecialchars(basename($buku['file_path'])); ?></span>
                            <span class="pdf-sub">Format Dokumen Digital</span>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="pdf-card-download disabled">
                        <div class="pdf-icon-box bg-secondary">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div class="pdf-info-text">
                            <span class="pdf-filename">PDF Belum Tersedia</span>
                            <span class="pdf-sub">Berkas tidak ditemukan</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="book-right-side">
                <div class="form-group-detail">
                    <label>Judul</label>
                    <div class="form-control-static"><?= htmlspecialchars($buku['title']); ?></div>
                </div>

                <div class="form-group-detail">
                    <label>Penulis</label>
                    <div class="form-control-static"><?= htmlspecialchars($buku['author']); ?></div>
                </div>

                <div class="row-inputs">
                    <div class="form-group-detail flex-1">
                        <label>Harga</label>
                        <div class="form-control-static">Rp <?= number_format($buku['price'], 0, ',', '.'); ?></div>
                    </div>

                    <div class="form-group-detail flex-1">
                        <label>Kategori</label>
                        <div class="form-control-static select-style"><?= htmlspecialchars($nama_kategori); ?></div>
                    </div>

                    <div class="form-group-detail flex-1">
                        <label>Rating</label>
                        <div class="form-control-static rating-style">
                            <i class="fa-solid fa-star me-2" style="color: #f1c40f;"></i><?= $tampilan_rating; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group-detail">
                    <label>Sinopsis</label>
                    <div class="form-control-static textarea-style">
                        <?= !empty($buku['synopsis']) ? htmlspecialchars($buku['synopsis']) : 'Tidak ada sinopsis untuk buku ini.'; ?>
                    </div>
                </div>

                <div class="action-buttons-wrapper" style="margin-top: 30px;">
                    <a href="<?= base_url('views/admin/katalog-buku.php'); ?>" class="btn-custom btn-back" style="display: inline-flex; align-items: center; justify-content: center; padding: 12px 30px; background-color: #433878; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 12px rgba(67, 56, 120, 0.15); transition: all 0.3s ease;">
                        <i class="fa-solid fa-arrow-left me-2"></i>Kembali Ke Katalog
                    </a>
                </div>
            </div>
        </div>
      </section>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>