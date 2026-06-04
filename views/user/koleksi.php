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
                <form method="GET" action="" class="filter-grid">
                    <div class="search-wrap">
                        <label for="searchInput" class="form-label">Cari Buku</label>
                        <input id="searchInput" name="search" type="text" class="form-control" 
                               placeholder="Contoh: Makrifat, Kuntowijoyo" 
                               value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>

                    <div class="category-wrap">
                        <label for="categorySelect" class="form-label">Kategori</label>
                        <select id="categorySelect" name="category" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= (($category ?? 'all') == 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                            <option value="sastra" <?= (($category ?? 'all') == 'sastra') ? 'selected' : '' ?>>Sastra Indonesia</option>
                            <option value="filsafat" <?= (($category ?? 'all') == 'filsafat') ? 'selected' : '' ?>>Filsafat</option>
                            <option value="sosiologi" <?= (($category ?? 'all') == 'sosiologi') ? 'selected' : '' ?>>Sosiologi</option>
                            <option value="agama" <?= (($category ?? 'all') == 'agama') ? 'selected' : '' ?>>Agama</option>
                            <option value="sejarah" <?= (($category ?? 'all') == 'sejarah') ? 'selected' : '' ?>>Sejarah</option>
                        </select>
                    </div>
                    <button type="submit" style="display: none;"></button>
                </form>
            </section>

            <section>
                <p id="resultInfo" class="result-info">
                    <?= $totalBooks ?? 0 ?> buku ditemukan · Halaman <?= $page ?? 1 ?>/<?= max(1, $totalPages ?? 1) ?> · Maks 20 buku/halaman
                </p>
                
                <div id="bookGrid" class="book-grid">
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $buku): ?>
                            <div class="book-card-wrapper" style="width: 250px; display: inline-block; margin-right: 20px; margin-bottom: 20px; vertical-align: top;">
                                <div class="card h-100 border-0 shadow-sm p-3" style="border-radius: 20px;">
                                    <div class="text-center mb-3">
                                        <img src="<?= !empty($buku['cover']) ? $buku['cover'] : '../../assets/img/default-cover.jpg' ?>" 
                                             alt="<?= htmlspecialchars($buku['judul']) ?>" 
                                             class="img-fluid" style="max-height: 180px; border-radius: 10px;">
                                    </div>
                                    <h5 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 1.1rem;"><?= htmlspecialchars($buku['judul']) ?></h5>
                                    <p class="text-muted small mb-1"><?= htmlspecialchars($buku['penulis']) ?></p>
                                    <p class="text-secondary small mb-3">Kategori: <?= htmlspecialchars($buku['kategori']) ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="fw-bold text-primary">Rp <?= number_format(($buku['harga'] ?? 0), 0, ',', '.') ?></span>
                                        <a href="detail-buku.php?id=<?= $buku['id'] ?>" class="btn btn-dark btn-sm px-3" style="background-color: #211c4a; border-radius: 8px;">Detail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info w-100 text-center" style="border-radius: 15px;">
                            Tidak ada koleksi buku yang ditemukan untuk kategori ini.
                        </div>
                    <?php endif; ?>
                </div>

                <div id="pagination" class="pagination-wrap mt-4 d-flex justify-content-center">
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category ?? 'all') ?>&page=<?= $i ?>"><?= $i ?></a>
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
    <script src="../../assets/script/user/koleksi.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
</body>

</html>