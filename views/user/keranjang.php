<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../controllers/keranjang-controller.php';
require_once __DIR__ . '/../../models/keranjangModel.php';

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

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger mt-3 mb-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
            </div>

            <section class="cart-card p-4 p-md-5">
                <div id="cartItems">
                    <?php if (!empty($cart_books)) : ?>
                        <div class="select-all-wrapper mb-3 p-3 bg-light rounded-4 d-flex align-items-center" 
                            style="border: 1px dashed #ced4da;">
                            <div class="cart-selection-area pe-3">
                                <input type="checkbox" id="selectAllTop" class="form-check-input" 
                                    style="width: 24px; height: 24px; cursor: pointer;">
                            </div>
                            <label for="selectAllTop" class="fw-bold text-dark mb-0" style="cursor: pointer;">
                                Pilih Semua Buku (<?= count($cart_books) ?>)
                            </label>
                        </div>
                        <?php foreach ($cart_books as $book) : ?>
                            <article class="cart-item cart-item-wrap mb-4 shadow-sm" 
                                style="display: grid !important; 
                                        grid-template-columns: auto 1fr auto !important; 
                                        align-items: center !important; 
                                        text-align: left !important; 
                                        padding: 20px !important; 
                                        gap: 20px !important; 
                                        background: #ffffff !important; 
                                        border-radius: 15px !important;
                                        flex-direction: row !important;">
                                
                                <div class="cart-selection-area pe-3">
                                    <input type="checkbox" class="book-checkbox form-check-input" 
                                        value="<?= $book['id'] ?>" 
                                        data-price="<?= $book['price'] ?>" 
                                        style="width: 24px; height: 24px; cursor: pointer;">
                                </div>

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
                                    
                                </a>

                                <div class="cart-action-area ps-3 d-flex flex-column align-items-end" style="gap: 12px;">
                                    <!-- Harga Buku Dinamis -->
                                    <div class="price-block">
                                        <div class="price-main">Rp <?= number_format($book['price'], 0, ',', '.') ?></div>
                                    </div>

                                    <a href="keranjang.php?action=remove&id=<?= $book['id'] ?>" class="remove-btn btn-remove-cart text-decoration-none">
                                        <i class="fa-regular fa-trash-can me-1"></i>Hapus
                                    </a>
                                </div>

                            </article>
                        <?php endforeach; ?>

                        <div class="checkout-section border-top pt-4 mt-4 d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-0">Total Terpilih:</p>
                                <h4 class="fw-bold text-dark">Rp <span id="totalDisplay">0</span></h4>
                            </div>
                            <form action="transaksi-multiple.php" method="POST">
                                <input type="hidden" name="selected_ids" id="selectedIds">
                                <button type="submit" id="btnCheckout" class="btn btn-outline-cart w-100 py-3 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" disabled>
                                    Checkout (<span id="countSelected">0</span>)
                                </button>
                            </form>
                        </div>

                    <?php else : ?>
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
            // Ambil elemen Select All dan semua checkbox buku
            const selectAllTop = document.getElementById('selectAllTop');
            const checkboxes = document.querySelectorAll('.book-checkbox');
            
            const totalDisplay = document.getElementById('totalDisplay');
            const countSelected = document.getElementById('countSelected');
            const btnCheckout = document.getElementById('btnCheckout');
            const selectedIdsInput = document.getElementById('selectedIds');

            // --- FUNGSI UPDATE SUMMARY ---
            function updateSummary() {
                let total = 0;
                let count = 0;
                let ids = [];

                // Kita harus query ulang di sini karena kalau ada item dihapus, jumlah list berubah
                const currentCheckboxes = document.querySelectorAll('.book-checkbox');
                
                currentCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        total += parseInt(cb.dataset.price);
                        count++;
                        ids.push(cb.value);
                    }
                });

                // Update tampilan angka
                if(totalDisplay) totalDisplay.innerText = total.toLocaleString('id-ID');
                if(countSelected) countSelected.innerText = count;
                
                // Masukkan ID ke hidden input
                if(selectedIdsInput) selectedIdsInput.value = ids.join(',');
                
                // Aktifkan/Matikan tombol checkout
                if(btnCheckout) btnCheckout.disabled = (count === 0);
            }

            // --- LOGIKA SELECT ALL ---
            if (selectAllTop) {
                selectAllTop.addEventListener('change', function() {
                    // Semua checkbox buku ngikutin status Select All
                    const currentCheckboxes = document.querySelectorAll('.book-checkbox');
                    currentCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                    updateSummary();
                });
            }

            // --- LOGIKA CHECKBOX SATUAN ---
            // Pake event delegation supaya kalau ada item baru/hapus tetep jalan
            document.getElementById('cartItems').addEventListener('change', function(e) {
                if (e.target.classList.contains('book-checkbox')) {
                    // Kalau ada satu aja yang gak dicentang, Select All ikut mati
                    if (selectAllTop) {
                        const currentCheckboxes = document.querySelectorAll('.book-checkbox');
                        const allChecked = Array.from(currentCheckboxes).every(cb => cb.checked);
                        selectAllTop.checked = allChecked;
                    }
                    updateSummary();
                }
            });

            // --- LOGIKA HAPUS ITEM ---
            const cartItems = document.getElementById('cartItems');
            cartItems.addEventListener('click', function (event) {
                const removeBtn = event.target.closest('.btn-remove-cart');
                if (!removeBtn) return;
                
                const item = removeBtn.closest('.cart-item-wrap');
                if (item) {
                    item.remove();
                    // Setelah dihapus, hitung ulang totalnya
                    updateSummary();
                    
                    // Cek ulang Select All kalau item abis
                    const currentCheckboxes = document.querySelectorAll('.book-checkbox');
                    if(currentCheckboxes.length === 0 && selectAllTop) {
                        selectAllTop.checked = false;
                    }
                }
            });
        })();
    </script>
</body>
</html>
