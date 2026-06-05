<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/buku-user-model.php';
require_once __DIR__ . '/../../controllers/koleksi-controller.php';

$controller = new KoleksiController($conn);
$data = $controller->handleKoleksi();

$books = $data['books'];
$totalBooks = $data['totalBooks'];
$totalPages = $data['totalPages'];
$page = $data['page'];
$search = $data['search'];
$category = $data['category'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Kami - Galaxy Digi Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/koleksi.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">

    <style>
        /* Pengaturan Grid Utama - 4 per baris */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            width: 100%;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 1200px) {
            .book-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 992px) {
            .book-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 576px) {
            .book-grid { grid-template-columns: 1fr; }
        }

        .book-card-wrapper {
            width: 100%;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .book-card-wrapper:hover {
            transform: translateY(-12px) scale(1.01);
        }

        .book-card-wrapper .detail-btn {
            padding: 10px 20px;
            background: #2e245b;
            color: white;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .book-card-wrapper .detail-btn:hover {
            background: transparent;
            color: #2e245b;
            border-color: #2e245b;
        }

        .book-card-wrapper .keranjang-btn {
            width: 42px;
            height: 42px;
            background: #f0ebff;
            color: #2e245b;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1rem;
            text-decoration: none;
        }

        .book-card-wrapper .keranjang-btn:hover {
            background: #2e245b;
            color: white;
            transform: scale(1.05);
        }

        /* Pengaturan Footer Card */
        .book-card-wrapper .best-footer {
            display: flex;
            flex-direction: column;
            width: 100%;
            align-items: flex-start; /* Memastikan isi di dalamnya rata kiri secara bawaan */
        }

        .book-card-wrapper .best-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end; /* Memaksa tombol rata kanan */
            width: 100%;
        }
    </style>
</head>

<body data-page="koleksi">
    <div id="site-navbar"></div>

    <main class="collection-page">
        <section class="container py-5">
            <div class="hero-box mb-4">
                <h1>Koleksi Buku</h1>
                <p>Cari buku berdasarkan judul/penulis dan filter berdasarkan kategori.</p>
            </div>

            <section class="filters mb-4">
                <form method="GET" action="" class="filter-grid">
                    <div class="search-wrap">
                        <label for="searchInput" class="form-label">Cari Buku</label>
                        <input id="searchInput" name="search" type="text" class="form-control"
                            placeholder="Contoh: Makrifat, Kuntowijoyo" value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <div class="category-wrap">
                        <label for="categorySelect" class="form-label">Kategori</label>
                        <select id="categorySelect" name="category" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= ($category == 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                            <option value="sastra" <?= ($category == 'sastra') ? 'selected' : '' ?>>Sastra Indonesia</option>
                            <option value="filsafat" <?= ($category == 'filsafat') ? 'selected' : '' ?>>Filsafat</option>
                            <option value="sosiologi" <?= ($category == 'sosiologi') ? 'selected' : '' ?>>Sosiologi</option>
                            <option value="agama" <?= ($category == 'agama') ? 'selected' : '' ?>>Agama</option>
                            <option value="sejarah" <?= ($category == 'sejarah') ? 'selected' : '' ?>>Sejarah</option>
                        </select>
                    </div>
                    <button type="submit" style="display: none;"></button>
                </form>
            </section>

            <section>
                <p id="resultInfo" class="result-info mb-4">
                    <?= $totalBooks ?> buku ditemukan · Halaman <?= $page ?>/<?= max(1, $totalPages) ?> · Maks 20 buku/halaman
                </p>

                <!-- Grid Buku -->
                <div id="bookGrid" class="book-grid">
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $buku): ?>
                            <div class="book-card-wrapper">
                                <div class="card h-100 border-0 shadow-sm d-flex flex-column"
                                    style="border-radius: 20px; overflow: hidden;">

                                    <!-- HEADER GRADIENT -->
                                    <div
                                        style="height: 160px; background: linear-gradient(135deg, #ede9fe 0%, #dbeafe 100%); position: relative; border-bottom-left-radius: 50% 20px; border-bottom-right-radius: 50% 20px; flex-shrink: 0;">
                                        <?php
                                        $image = !empty($buku['cover_image'])
                                            ? '../../' . $buku['cover_image']
                                            : '../../assets/img/default-cover.jpg';
                                        ?>
                                        <img src="<?= htmlspecialchars($image) ?>"
                                            alt="<?= htmlspecialchars($buku['title'] ?? '') ?>"
                                            style="width: 110px; height: 160px; object-fit: cover; position: absolute; left: 50%; top: 20px; transform: translateX(-50%); border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.2);">
                                    </div>

                                    <div class="p-3 d-flex flex-column flex-grow-1" style="padding-top: 50px !important;">
                                        <h5 class="fw-bold mb-3 text-dark"
                                            style="font-size: 1rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 42px;">
                                            <?= htmlspecialchars($buku['title'] ?? '') ?>
                                        </h5>

                                        <!-- AUTHOR + IKON USER -->
                                        <p class="text-muted small mb-1">
                                            <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($buku['author'] ?? '') ?>
                                        </p>

                                        <p class="text-secondary small mb-3">
                                            Penerbit: <?= htmlspecialchars($buku['publisher'] ?? '-') ?>
                                        </p>

                                        <!-- FOOTER CARD -->
                                        <div class="best-footer mt-auto">
                                            <!-- Penambahan class 'w-100' dan 'text-start' agar harga mutlak rata kiri -->
                                            <div class="best-price mb-2 w-100 text-start" style="font-weight: 800; font-size: 1.1rem; color: #2e245b;">
                                                Rp <?= number_format(($buku['price'] ?? 50000), 0, ',', '.') ?>
                                            </div>

                                            <div class="best-actions">
                                                <a href="keranjang.php?action=add&id=<?= $buku['id'] ?>" class="keranjang-btn"
                                                    title="Tambah ke Keranjang">
                                                    <i class="fa-solid fa-cart-shopping"></i>
                                                </a>
                                                <a href="detail.php?id=<?= $buku['id'] ?>" class="detail-btn">Detail</a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info w-100 text-center" style="border-radius: 15px; grid-column: 1 / -1;">
                            Tidak ada koleksi buku yang ditemukan untuk kategori atau kata kunci tersebut.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <div id="pagination" class="pagination-wrap mt-5 d-flex justify-content-center">
                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </section>
        </section>
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
</body>

</html>