<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../controllers/keranjang-controller.php';

requireRole('user');

handleCartActions($conn);

// Ambil data buku untuk ditampilkan di HTML
$user_id = $_SESSION['user_id'];
$cart_books = getUserCartItems($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - BookStore</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/keranjang.css">
</head>
<body data-page="profile">
    <div id="site-navbar"></div>

    <main class="cart-page-container py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Keranjang</li>
                </ol>
            </nav>

            <div class="mb-4">
                <span class="section-subtitle">Belanja Anda</span>
                <h1 class="cart-title fw-extrabold mb-1">Keranjang Buku Digital</h1>
                <p class="text-muted mb-0">Periksa kembali item Anda sebelum melanjutkan pembayaran.</p>
            </div>

            <section class="cart-card p-4 p-md-5">
                <div id="cartItems">
                    <?php if (!empty($cart_books)) : ?>
                        <!-- Lakukan perulangan untuk menampilkan setiap buku yang ada di keranjang -->
                        <?php foreach ($cart_books as $book) : ?>
                            <article class="cart-item cart-item-wrap mb-4">
                                <a href="detail.php?id=<?= $book['id'] ?>" class="cart-item-link">
                                    <!-- Cover Buku Dinamis -->
                                    <img src="../../<?= !empty($book['cover_image']) ? htmlspecialchars($book['cover_image']) : 'assets/pic/default.png' ?>" 
                                        alt="<?= htmlspecialchars($book['title']) ?>" 
                                        class="cart-cover">
                                    
                                    <div>
                                        <!-- Judul & Penulis Dinamis -->
                                        <h5 class="cart-book-title"><?= htmlspecialchars($book['title']) ?></h5>
                                        <p class="cart-book-meta mb-1"><?= htmlspecialchars($book['author']) ?> • PDF Premium</p>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Klik untuk lihat detail buku
                                        </small>
                                    </div>
                                    
                                    <!-- Harga Buku Dinamis -->
                                    <div class="price-block">
                                        <div class="price-main">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                    </div>
                                </a>
                                
                                <!-- Tombol Hapus mengarah ke action hapus PHP dengan parameter ID buku -->
                                <div class="cart-item-actions">
                                    <a href="keranjang.php?action=remove&id=<?= $book['id'] ?>" class="remove-btn btn-remove-cart text-decoration-none">
                                        <i class="fa-regular fa-trash-can me-1"></i>Hapus
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <!-- State Tampilan jika Keranjang Belanja Kosong -->
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-basket-shopping display-3 mb-3 d-block text-black-50"></i>
                            <h5 class="fw-bold text-dark">Keranjang Belanja Anda Kosong</h5>
                            <p class="mb-4">Anda belum menambahkan buku digital apa pun ke dalam keranjang.</p>
                            <a href="dashboard.php" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                                <i class="fa-solid fa-book-open me-2"></i>Jelajahi Buku
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    <script>
        (function () {
            const cartItems = document.getElementById('cartItems');
            cartItems.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.btn-remove-cart');
                if (!removeBtn) return;
                const item = removeBtn.closest('.cart-item-wrap');
                if (!item) return;
                item.remove();
            });
        })();
    </script>
</body>
</html>
