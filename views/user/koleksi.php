<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/buku-model.php';

$books = ambilSemuaBuku($conn);
$booksJson = json_encode($books, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
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
                <div class="filter-grid">
                    <div class="search-wrap">
                        <label for="searchInput" class="form-label">Cari Buku</label>
                        <input id="searchInput" type="text" class="form-control" placeholder="Contoh: Makrifat, Kuntowijoyo">
                    </div>

                    <div class="category-wrap">
                        <label for="categorySelect" class="form-label">Kategori</label>
                        <select id="categorySelect" class="form-select">
                            <option value="all" selected>Semua Kategori</option>
                        </select>
                    </div>
                </div>
            </section>

            <section>
                <p id="resultInfo" class="result-info"></p>
                <div id="bookGrid" class="book-grid"></div>
                <div id="pagination" class="pagination-wrap"></div>
            </section>
        </section>
    </main>

    <div id="site-footer"></div>

    <script>
        window.BOOK_LIST = <?= $booksJson ?: '[]' ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/koleksi.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
</body>

</html>
