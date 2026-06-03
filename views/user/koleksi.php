<?php
require_once dirname(__DIR__, 2) . '/controllers/koleksi-controller.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Kami - Galaxy Digi Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/koleksi.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
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
                <form method="GET" action="koleksi.php">
                    <div class="filter-grid">
                        <div class="search-wrap">
                            <label for="searchInput" class="form-label">Cari Buku</label>
                            <input id="searchInput" type="text" name="search" class="form-control" placeholder="Contoh: Makrifat, Kuntowijoyo" value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
                        </div>

                        <div class="category-wrap">
                            <label for="categorySelect" class="form-label">Kategori</label>
                            <select id="categorySelect" name="kategori" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?= ($kategori_id === 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                                <?php while($kat = $listKategori->fetch_assoc()): ?>
                                    <option value="<?= $kat['id'] ?>" <?= ($kategori_id == $kat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['category_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </section>

            <section>
                <p id="resultInfo" class="result-info" style="display: block;">
                    <?= $totalBuku ?> buku ditemukan · Halaman <?= $page ?>/<?= max(1, $totalPage) ?> · Maks 20 buku/halaman
                </p>
                
                <div id="bookGrid" class="book-grid">
                    <?php if ($daftarBuku->num_rows > 0): ?>
                        <?php while ($buku = $daftarBuku->fetch_assoc()): ?>
                            <div class="book-card-item">
                                <div class="book-card-top">
                                    <img src="../../assets/img/books/<?= !empty($buku['cover']) ? htmlspecialchars($buku['cover']) : 'default-cover.png' ?>" alt="Cover Buku" class="book-cover-img">
                                </div>
                                
                                <div class="book-card-bottom">
                                    <h4 class="book-title"><?= htmlspecialchars($buku['judul']) ?></h4>
                                    <p class="book-author"><?= htmlspecialchars($buku['penulis']) ?></p>
                                    <p class="book-category">Kategori: <?= htmlspecialchars($buku['nama_kategori'] ?? 'Umum') ?></p>
                                    
                                    <div class="book-card-footer">
                                        <span class="book-price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></span>
                                        <a href="detail-buku.php?id=<?= $buku['id'] ?>" class="btn-detail">Detail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5" style="grid-column: 1 / -1; width: 100%;">
                            <i class="fa-solid fa-folder-open text-muted display-4 mb-3"></i>
                            <p class="text-secondary">Tidak ada koleksi buku yang cocok dengan kriteria pencarian Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="pagination" class="pagination-wrap d-flex justify-content-center mt-5">
                    <?php if ($totalPage > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="koleksi.php?search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori_id) ?>&page=<?= $page - 1 ?>"><i class="fa-solid fa-angle-left"></i></a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="koleksi.php?search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori_id) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPage) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="koleksi.php?search=<?= urlencode($search) ?>&kategori=<?= urlencode($kategori_id) ?>&page=<?= $page + 1 ?>"><i class="fa-solid fa-angle-right"></i></a>
                                </li>
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