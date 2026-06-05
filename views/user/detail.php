<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/detailModel.php';

requireRole('user');

// 1. Cek apakah ada parameter ID di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id_buku = $_GET['id'];

$buku = ambilDetailBuku($conn, $id_buku);
if (!$buku) {
    header("Location: dashboard.php");
    exit;
}

$ratingData = ambilRatingBuku($conn, $id_buku);

$rataRataRating = $ratingData['avg_rating'];
$totalUlasan = $ratingData['total_ulasan'];

$semuaReview = ambilReviewBuku($conn, $id_buku);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - BookStore</title>


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
</head>

<body>
    <div id="site-navbar"></div>

    <main class="detail-page-container py-5">
        <div class="container">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="koleksi.php">Koleksi Buku</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Produk</li>
                </ol>
            </nav>

            <div class="row g-5">

                <!-- KOLOM KIRI: COVER BUKU -->
                <div class="col-lg-4 text-center text-lg-start">
                    <div class="detail-cover-wrapper position-sticky" style="top: 20px;">
                        <!-- Path gambar disesuaikan dengan database -->
                        <img src="../../<?= !empty($buku['cover_image']) ? htmlspecialchars($buku['cover_image']) : 'assets/pic/default.png' ?>" 
                            alt="<?= htmlspecialchars($buku['title']) ?>" 
                            class="img-fluid main-book-img">
                        <div class="cover-shadow-effect"></div>
                    </div>
                </div>

                <!-- KOLOM KANAN: DETAIL, DETAIL HARGA, SPEKS, TOMBOL BELI & SINOPSIS -->
                <div class="col-lg-8">
                    <div class="detail-info-header">
                        <span class="badge badge-category mb-2">
                            <i class="fa-solid fa-circle-check text-success me-1"></i> Terverifikasi Original
                        </span>
                        
                        <!-- Cetak Judul Buku -->
                        <h1 class="detail-title fw-extrabold mb-1"><?= htmlspecialchars($buku['title']) ?></h1>
                        
                        <!-- Cetak Penulis Buku -->
                        <p class="detail-author fs-5 text-muted mb-3">Karya Penulis: 
                            <span class="fw-semibold text-primary-theme"><?= htmlspecialchars($buku['author']) ?></span>
                        </p>

                        <div class="detail-rating-box d-flex align-items-center gap-2 mb-4">
                            <div class="stars text-warning">
                                <?php 
                                // Mengubah rating desimal ke angka bulat ke bawah (misal 4.5 jadi 4)
                                $bintangBulat = floor($rataRataRating); 
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $bintangBulat) {
                                        // Tampilkan Bintang Penuh
                                        echo '<i class="fa-solid fa-star"></i>';
                                    } elseif ($i == $bintangBulat + 1 && ($rataRataRating - $bintangBulat) >= 0.5) {
                                        // Tampilkan Bintang Setengah (Jika ratingnya .5 ke atas seperti 4.5, 4.7)
                                        echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                    } else {
                                        // Tampilkan Bintang Kosong
                                        echo '<i class="fa-regular fa-star" style="color: #ccc;"></i>';
                                    }
                                }
                                ?>
                            </div>
                            
                            <!-- Tampilkan Angka Rata-rata Rating -->
                            <span class="fw-bold fs-6 mt-1"><?= $rataRataRating > 0 ? $rataRataRating : '0.0' ?></span>
                            
                            <!-- Tampilkan Jumlah Total Ulasan dari Database -->
                            <span class="text-muted fs-7 mt-1">(<?= $totalUlasan ?> Ulasan Pembaca)</span>
                        </div>

                        <!-- Cetak Harga Buku Berformat Rupiah -->
                        <div class="detail-price-box p-4 rounded-4 mb-4">
                            <small class="text-muted d-block text-uppercase letter-spacing-1">Harga Akses Digital</small>
                            <h2 class="display-6 fw-bold text-gradient-price m-0">
                                Rp <?= number_format($buku['price'], 0, ',', '.') ?>
                            </h2>
                        </div>
                    </div>

                    <!-- Spesifikasi Box -->
                    <div class="detail-specs-card p-4 rounded-4 mb-4">
                        <h4 class="fs-6 fw-bold mb-3 text-uppercase letter-spacing-1">Spesifikasi Dokumen</h4>
                        <div class="row g-3 row-cols-2 row-cols-sm-3">
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-regular fa-file-pdf text-danger fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Format File</small>
                                    <span class="fw-bold">PDF Premium</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-solid fa-database text-info fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Ukuran Data</small>
                                    <span class="fw-bold">14.8 MB</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-solid fa-building-columns text-warning fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Penerbit</small>
                                    <span class="fw-bold">Lini Bangsa</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-solid fa-language text-success fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Bahasa</small>
                                    <span class="fw-bold">Indonesia</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-solid fa-fingerprint text-purple fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Proteksi DRM</small>
                                    <span class="fw-bold">Watermark</span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="spec-item">
                                    <i class="fa-solid fa-cloud-arrow-down text-primary fs-4 mb-2"></i>
                                    <small class="text-muted d-block">Akses Unduh</small>
                                    <span class="fw-bold">Selamanya</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI DENGAN BUTTON BAYAR / BELI LANGSUNG -->
                   <div class="detail-action-buttons row g-3 mb-5 justify-content-end">
                        <div class="col-sm-5">
                            <a href="transaksi.php?id=<?= $buku['id']; ?>"
                            class="btn btn-primary-buy w-100 py-3 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-credit-card"></i> Bayar Sekarang
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <a href="keranjang.php?action=add&id=<?= $buku['id'] ?>"
                            class="btn btn-outline-cart w-100 py-3 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-basket-shopping"></i> + Keranjang
                            </a>
                        </div>
                    </div>

                    <!-- SINOPSIS (SEKARANG BERADA DI SEBELAH COVER PADA DESKTOP) -->
                    <div class="synopsis-card p-4 p-md-5 rounded-4">
                        <h3 class="fw-bold mb-4 position-relative section-title-line">Sinopsis Lengkap</h3>
                        <div class="synopsis-content text-secondary fs-6">
                            
                            <?php if (!empty($buku['synopsis'])) : ?>
                                <!-- Jika di database ada sinopsisnya, tampilkan di sini -->
                                <p style="text-align: justify; line-height: 1.8;">
                                    <?= nl2br(htmlspecialchars($buku['synopsis'])) ?>
                                </p>
                            <?php else : ?>
                                <!-- Antispasi kalau admin lupa ngisi sinopsis di database -->
                                <p class="text-muted italic">Sinopsis untuk buku <strong>"<?= htmlspecialchars($buku['title']) ?>"</strong> belum tersedia.</p>
                            <?php endif; ?>
                            
                        </div>
                    </div>

                    <!-- REVIEW PENGGUNA -->
                    <div class="review-card synopsis-card p-4 p-md-5 rounded-4 mt-4">
                        <h3 class="fw-bold mb-4 position-relative section-title-line">Review Pembaca</h3>

                        <div class="review-list-wrapper">
                            <div id="reviewList" class="d-flex flex-column gap-3">
                                
                                <?php if (!empty($semuaReview)) : ?>
                                    <?php foreach ($semuaReview as $r) : ?>
                                        <div class="border rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <!-- Nama User hasil JOIN dari database -->
                                                <strong><?= htmlspecialchars($r['username']) ?></strong>
                                                
                                                <!-- Loop Bintang Dinamis sesuai rating ulasan -->
                                                <span class="text-warning">
                                                    <?php 
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $r['rating']) {
                                                            echo '<i class="fa-solid fa-star"></i>';
                                                        } else {
                                                            echo '<i class="fa-regular fa-star" style="color: #ccc;"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                            <!-- Isi Ulasan/Komentar -->
                                            <p class="mb-0 text-secondary"><?= htmlspecialchars($r['comment']) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <!-- Tampilan manis kalau buku ini belum punya ulasan sama sekali -->
                                    <div class="text-center py-4 text-muted border border-dashed rounded-3">
                                        <i class="fa-regular fa-comments fs-3 mb-2 d-block text-black-50"></i>
                                        Belum ada ulasan untuk buku ini.
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <div id="site-footer"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    <script>
        const reviewForm = document.getElementById('reviewForm');
        const reviewList = document.getElementById('reviewList');

        function renderStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += i <= rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
            }
            return stars;
        }

        reviewForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const nameInput = document.getElementById('reviewerName');
            const ratingInput = document.getElementById('reviewRating');
            const textInput = document.getElementById('reviewText');

            const name = nameInput.value.trim();
            const rating = parseInt(ratingInput.value, 10);
            const reviewText = textInput.value.trim();

            if (!name || !rating || !reviewText) {
                return;
            }

            const reviewItem = document.createElement('div');
            reviewItem.className = 'border rounded-3 p-3';
            reviewItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong></strong>
                    <span class="text-warning">${renderStars(rating)}</span>
                </div>
                <p class="mb-0 text-secondary"></p>
            `;

            reviewItem.querySelector('strong').textContent = name;
            reviewItem.querySelector('p').textContent = reviewText;

            reviewList.prepend(reviewItem);
            reviewForm.reset();
        });
    </script>
</body>

</html>
