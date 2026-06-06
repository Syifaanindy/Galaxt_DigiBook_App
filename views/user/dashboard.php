<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/dashboardUserModel.php';
require_once __DIR__ . '/../../models/detailModel.php';

requireRole('user');

$daftarBuku = ambilBukuTerbatas($conn, 6);
$daftarReview = ambilReviewTerbaik($conn);
$bukuBestSeller = ambilBukuBestSeller($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStore - Galaxy Digi Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/user/p.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
</head>

<body data-page="home">

    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <section class="banner-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <div class="home-slider">
                        <div class="slider-track">
                            <div class="slide"><img src="../../assets/pic/b-1.webp" alt="Buku 1"></div>
                            <div class="slide"><img src="../../assets/pic/b-2.webp" alt="Buku 2"></div>
                            <div class="slide"><img src="../../assets/pic/b-3.webp" alt="Buku 3"></div>
                            <div class="slide"><img src="../../assets/pic/b-4.webp" alt="Buku 4"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="banner-text">
                        <h2>Galaxy Digi Book</h2>
                        <p>Digital Book Store Pilihan mu!</p>
                        <a href="koleksi.php" class="all-btns">Jelajahi Koleksi Kami</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BEST SELLER SECTION -->
    <section id="best-seller" class="best-seller-section py-5">
        <div class="container">
            <div class="section-header-box text-center mb-5">
                <span class="section-subtitle">Kurasi Terbaik</span>
                <h2 class="web-title"> Paling Laris Pekan Ini</h2>
                <p class="section-descmx">Buku-buku berkualitas tinggi yang paling banyak dicari dan dibaca oleh komunitas literasi kami.</p>
            </div>

            <div class="best-grid">
                <?php
            $index = 1;
            
            // Ambil ID User dari session (ganti sesuai nama session di tim kamu, misal $_SESSION['user_id'])
            $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            foreach ($bukuBestSeller as $buku):
                // PANGGIL FUNGSI KAMU DI SINI
                // Kalau user belum login, otomatis dianggap belum memiliki buku (false)
                $sudahMemilikiBuku = false;
                if ($current_user_id) {
                    $sudahMemilikiBuku = userSudahMemilikiBuku($conn, $current_user_id, $buku['id']);
                }
            ?>
                <div class="best-card">
                    <div class="tag-best">Top #<?= $index++ ?></div>
                    <div class="best-header">
                        <div class="best-cover">
                            <img src="../../<?= !empty($buku['cover_image']) ? htmlspecialchars($buku['cover_image']) : 'assets/pic/default.png' ?>"
                                alt="<?= htmlspecialchars($buku['title']) ?>">
                        </div>
                    </div>
                    <div class="best-content">
                        <div class="best-title"><?= htmlspecialchars($buku['title']) ?></div>
                        <div class="best-author">
                            <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($buku['author']) ?>
                        </div>
                        <div class="best-footer">
                            <div class="best-price">Rp <?= number_format($buku['price'], 0, ',', '.') ?></div>
                            <div class="best-actions">
                                
                                <!-- LOGIKANYA DI SINI: KALAU BELUM PUNYA, TAMPILKAN TOMBOL KERANJANG -->
                                <?php if (!$sudahMemilikiBuku): ?>
                                    <a href="keranjang.php?action=add&id=<?= $buku['id'] ?>" class="keranjang-btn" title="Tambah ke Keranjang">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="detail.php?id=<?= $buku['id'] ?>" class="detail-btn">Detail</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- KOLEKSI KAMI SECTION -->
    <section id="koleksi" class="book-sec py-5">
        <div class="container">
            <div style="position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 40px; min-height: 80px;">
                <div class="section-header-box" style="text-align: center;">
                    <span class="section-subtitle text-white-50" style="display: block; margin-bottom: 5px;">Eksplorasi Judul</span>
                    <h2 class="web-title text-white" style="margin: 0;">Seluruh Koleksi Kami</h2>
                </div>
                <a href="koleksi.php" class="btn-selengkapnya" style="position: absolute; right: 0; bottom: 0; text-decoration: none; color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 8px 20px; border-radius: 30px; font-size: 14px; transition: 0.3s;">
                    Lihat Selengkapnya <i class="fas fa-chevron-right" style="font-size: 10px; margin-left: 5px;"></i>
                </a>
            </div>

            <div class="book-list">
                <?php if (empty($daftarBuku)): ?>
                    <p class="text-white">Belum ada koleksi buku.</p>
                <?php else: ?>
                    <?php 
                    // Pastikan id user yang login diambil dulu sebelum loop
                    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                    
                    foreach ($daftarBuku as $book): 
                        // Cek kepemilikan buku menggunakan fungsi kamu
                        $sudahMemilikiBuku = false;
                        if ($current_user_id) {
                            $sudahMemilikiBuku = userSudahMemilikiBuku($conn, $current_user_id, $book['id']);
                        }
                    ?>
                        <div class="book-card">
                            <div class="book-header" style="background: linear-gradient(135deg, #c4b5fd 0%, #93c5fd 100%);">
                                <div class="book-cover">
                                    <img src="../../<?= htmlspecialchars($book['cover_image']) ?>"
                                        alt="<?= htmlspecialchars($book['title']) ?>">
                                </div>
                            </div>
                            <div class="book-content">
                                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                                <div class="book-author">
                                    <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($book['author']) ?>
                                </div>
                                <div class="best-footer">
                                    <div class="best-price">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                    <div class="best-actions">
                                        
                                        <!-- BUNGKUS TOMBOL KERANJANG PAKE LOGIKA SAKTI KAMU -->
                                        <?php if (!$sudahMemilikiBuku): ?>
                                            <a href="keranjang.php?action=add&id=<?= $book['id'] ?>" class="keranjang-btn" title="Tambah ke Keranjang">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="detail.php?id=<?= $book['id'] ?>" class="detail-btn">Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- TENTANG KAMI SECTION -->
    <section id="about-us" class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="about-img-wrapper">
                        <div class="about-accent-box"></div>
                        <div class="about-stats-floating">
                            <h3>10K+</h3>
                            <p>E-Book Aktif</p>
                        </div>
                        <div class="about-card-back"></div>
                        <div class="about-main-graphic d-flex align-items-center justify-content-center text-white">
                            <img src="../../assets/pic/about-store.png" alt="Kantor BookStore"
                                class="img-fluid w-100 h-100" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <span class="section-subtitle">Mengenal Toko Kami</span>
                    <h2 class="web-title mb-4">Membangun Jembatan Ilmu Lewat Literasi Digital</h2>
                    <p class="mb-4">Galaxy Digi Book (BookStore) hadir sebagai solusi modern untuk memenuhi kebutuhan intelektual Anda. Kami percaya bahwa akses terhadap buku-buku berbobot—mulai dari sastra klasik, pemikiran sosiologi, hingga filsafat kontemporer—haruslah fleksibel dan tanpa batas ruang.</p>
                    <div class="row g-3 core-advantages">
                        <div class="col-sm-6">
                            <div class="advantage-item">
                                <div class="adv-icon"><i class="fa-solid fa-bolt"></i></div>
                                <h5>Akses Instan</h5>
                                <p>Beli, unduh, dan baca langsung dalam hitungan detik.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="advantage-item">
                                <div class="adv-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                <h5>File Orisinal</h5>
                                <p>Semua dokumen bersumber resmi dari penerbit terpercaya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="lokasi-toko" class="maps-section py-5 bg-light">
        <div class="container">
            <div class="section-header-box text-center mb-5">
                <span class="section-subtitle">Kunjungi Kami</span>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                        <div class="ratio ratio-21x9" style="min-height: 400px;">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.6543354934684!2d107.6223302!3d-6.9318539!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e87e10d242fb%3A0x3705cc09ac1ed11e!2sbookstore%20Galaxy!5e0!3m2!1sen!2sid!4v1779949383983!5m2!1sen!2sid"
                                width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONI / REVIEW SECTION -->
    <section id="testimoni" class="review-section py-5">
        <div class="container">
            <div class="section-header-box text-center mb-5">
                <span class="section-subtitle">Review Pembaca</span>
                <h2 class="web-title">Apa Kata Mereka?</h2>
                <p class="section-descmx">Ulasan jujur dari para penikmat buku digital di platform kami.</p>
            </div>
            <div class="review-grid">
                <?php foreach ($daftarReview as $rev): ?>
                    <div class="review-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-right"></i></div>
                        <div class="rating-stars mb-2">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rev['rating']) {
                                    echo '<i class="fa-solid fa-star" style="color: #ffc107;"></i>';
                                } else {
                                    echo '<i class="fa-regular fa-star" style="color: #ccc;"></i>';
                                }
                            }
                            ?>
                        </div>
                        <p class="review-text">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <hr class="review-divider">
                        <div class="reviewer-profile">
                            <div class="reviewer-avatar text-bg-primary">
                                <?php if (!empty($rev['picture'])): ?>
                                    <img src="../../assets/img/profile/<?= htmlspecialchars($rev['picture']) ?>" 
                                        alt="<?= htmlspecialchars($rev['username']) ?>" 
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 text-bg-primary rounded-circle" style="font-weight: bold;">
                                        <?= strtoupper(substr($rev['username'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="reviewer-info">
                                <h6 class="m-0"><?= htmlspecialchars($rev['username']) ?></h6>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>

</body>

</html>