<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// KODE TAMBAHAN 1: IMPORT KONEKSI, MODEL, & PENGAMAN LOGIN
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/buku-model.php'; // Digunakan untuk fungsi ambilBukuById

// Proteksi: Pastikan user sudah login sebelum bayar
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silahkan login terlebih dahulu.";
    header("Location: login.php"); // Sesuaikan dengan nama halaman login user kamu
    exit;
}

// Tangkap ID Buku dari URL parameter ?id=...
$id_buku = intval($_GET['id'] ?? 0);
$buku = ambilBukuById($conn, $id_buku);

// Validasi jika buku tidak ditemukan
if (!$buku) {
    $_SESSION['error'] = "Data buku untuk transaksi tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Hitung total harga (Harga buku + Biaya layanan 2500)
$biaya_layanan = 2500;
$total_harga = $buku['price'] + $biaya_layanan;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - BookStore</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/transaction.css">
</head>

<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="transaction-page-container py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.html#koleksi">Koleksi Buku</a></li>
                    <li class="breadcrumb-item"><a href="detail.html">Detail Produk</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
                </ol>
            </nav>

            <div class="mb-4">
                <span class="section-subtitle">Checkout Aman</span>
                <h1 class="transaction-title fw-extrabold">Selesaikan Pembayaran</h1>
                <p class="text-muted mb-0">Lengkapi data pembayaran Anda dan konfirmasi transaksi dalam satu langkah.</p>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger mt-3 mb-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <section class="checkout-card p-4 p-md-5 mb-4">
                        <h5 class="fw-bold mb-3">Informasi Pembeli</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" class="form-control checkout-form-control"
                                    placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control checkout-form-control"
                                    placeholder="nama@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. WhatsApp</label>
                                <input type="text" class="form-control checkout-form-control"
                                    placeholder="+62 8xx xxxx xxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catatan (Opsional)</label>
                                <input type="text" class="form-control checkout-form-control"
                                    placeholder="Contoh: butuh invoice">
                            </div>
                        </div>
                    </section>

                    
                </div>

                <div class="col-lg-4">
                    <aside class="checkout-card p-4 summary-sticky">
                        <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>

                        <div class="d-flex gap-3 align-items-start mb-3">
                            <?php if (!empty($buku['cover_image']) && file_exists(__DIR__ . '/../../' . $buku['cover_image'])): ?>
                                <img src="../../<?= $buku['cover_image']; ?>" alt="Cover Buku" class="summary-cover">
                            <?php else: ?>
                                <img src="../../assets/pic/b-1.png" alt="Cover Buku Default" class="summary-cover">
                            <?php endif; ?>
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($buku['title']); ?></h6>
                                <small class="text-muted d-block"><?= htmlspecialchars($buku['author']); ?></small>
                                <small class="text-muted">PDF Premium • Akses Selamanya</small>
                            </div>
                        </div>

                        <div class="price-row">
                            <span>Harga Buku</span>
                            <strong>Rp <?= number_format($buku['price'], 0, ',', '.'); ?></strong>
                        </div>
                        <div class="price-row">
                            <span>Biaya Layanan</span>
                            <strong id="feeAmount">Rp <?= number_format($biaya_layanan, 0, ',', '.'); ?></strong>
                        </div>
                        <div class="price-row">
                            <span>Diskon</span>
                            <strong class="text-success">- Rp 0</strong>
                        </div>
                        <div class="price-row total">
                            <span>Total Pembayaran</span>
                            <span id="totalAmount">Rp <?= number_format($total_harga, 0, ',', '.'); ?></span>
                        </div>

                        <form action="../../controllers/transaksi-controller.php" method="POST">
                            <input type="hidden" name="book_id" value="<?= $buku['id']; ?>">
                            <input type="hidden" name="price" value="<?= $total_harga; ?>">
                            
                            <button type="submit" name="aksi_bayar"
                                class="btn btn-primary-buy w-100 py-3 fw-bold rounded-3 mt-3 d-flex align-items-center justify-content-center gap-2">
                                <i class="fa-solid fa-lock"></i> Bayar Sekarang
                            </button>
                        </form>
                    </aside>
                </div>
            </div>
        </div>
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    <script>
        (function () {
            // JavaScript disesuaikan dengan nilai dinamis PHP agar format tetap rapi
            const bookPrice = <?= $buku['price']; ?>;
            const serviceFee = <?= $biaya_layanan; ?>;
            const feeAmount = document.getElementById('feeAmount');
            const totalAmount = document.getElementById('totalAmount');
            feeAmount.textContent = 'Rp ' + serviceFee.toLocaleString('id-ID');
            totalAmount.textContent = 'Rp ' + (bookPrice + serviceFee).toLocaleString('id-ID');
        })();
    </script>
</body>

</html>