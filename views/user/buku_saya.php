<?php
session_start();

include '../../config/database.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil parameter search dan category dari URL
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// 1. AMBIL DAFTAR KATEGORI UNTUK DROPDOWN
// JIKA NAMA TABEL/KOLOM KATEGORI ANDA BERBEDA, UBAH DI SINI:
$categories_options = mysqli_query($conn, "SELECT DISTINCT category_name FROM category WHERE category_name IS NOT NULL AND category_name != '' ORDER BY category_name ASC");

$limit = 4; 

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// 2. QUERY COUNT (Menghitung total data dengan relasi LEFT JOIN)
$count_query = "SELECT COUNT(*) AS total FROM transaction 
                INNER JOIN books ON transaction.book_id = books.id 
                LEFT JOIN category ON books.category_id = category.id 
                WHERE transaction.user_id = '$user_id'";

if (!empty($search)) {
    $count_query .= " AND (books.title LIKE '%$search%' OR books.author LIKE '%$search%')";
}
if (!empty($category_filter)) {
    $count_query .= " AND category.category_name = '$category_filter'";
}

$count_result = mysqli_query($conn, $count_query);
$count_data = mysqli_fetch_assoc($count_result);
$total_data = $count_data['total'];

$total_pages = ceil($total_data / $limit);

$query = "SELECT transaction.*, books.title, books.author, books.cover_image, category.category_name 
          FROM transaction 
          INNER JOIN books ON transaction.book_id = books.id 
          LEFT JOIN category ON books.category_id = category.id 
          WHERE transaction.user_id = '$user_id'";

if (!empty($search)) {
    $query .= " AND (books.title LIKE '%$search%' OR books.author LIKE '%$search%')";
}
if (!empty($category_filter)) {
    $query .= " AND category.category_name = '$category_filter'";
}

$query .= " ORDER BY transaction.id DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Saya - BookStore</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/koleksi.css">
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/buku-saya.css">
</head>
<body data-page="profile">
    <div id="site-navbar"></div>
    
    <main class="my-books-page-container py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Buku Saya</li>
                </ol>
            </nav>

            <div class="row align-items-center mb-4">
                <div class="col-md-8">
                    <span class="section-subtitle">Library Pribadi</span>
                    <h1 class="my-books-title fw-extrabold mb-1"><b>Koleksi Buku Saya</b></h1>
                    <p class="text-muted mb-0">Semua buku yang sudah Anda beli siap dibaca kapan pun.</p>
                </div>
                <div class="col-md-4 d-flex justify-content-md-end mt-3 mt-md-0">
                    <div class="my-library-stats d-flex align-items-center gap-3">
                        <div class="stats-icon">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div>
                            <div class="stats-label">Total Koleksi</div>
                            <div class="stats-value"><?= $total_data; ?> <span class="stats-unit">Buku</span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <section class="my-books-card p-4 p-md-5">
                
                <div class="search-container-wrap mb-4">
                    <form action="" method="GET" class="row g-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <?php 
                                if ($categories_options && mysqli_num_rows($categories_options) > 0) {
                                    while ($cat = mysqli_fetch_assoc($categories_options)) {
                                        $selected = ($category_filter == $cat['category_name']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($cat['category_name']) . '" ' . $selected . '>' . htmlspecialchars($cat['category_name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button class="btn btn-primary w-100" type="submit">Cari</button>
                            <?php if (!empty($search) || !empty($category_filter)): ?>
                                <a href="buku_saya.php" class="btn btn-secondary" title="Reset Filter">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="book-grid">
                    
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                    ?>
                            <div class="book-card">
                                <div class="book-header">
                                    <div class="book-cover">
                                        <img src="../../assets/pic/<?= htmlspecialchars($row['cover_image'] ?? 'default.jpg'); ?>" alt="Cover Buku">
                                    </div>
                                </div>
                                <div class="book-content">
                                    <div class="book-title" align="center"><?= htmlspecialchars($row['title'] ?? 'Tanpa Judul'); ?></div>
                                    <div class="book-author"><?= htmlspecialchars($row['author'] ?? 'Anonim'); ?></div>
                                    <div class="book-category">Kategori: <?= htmlspecialchars($row['category_name'] ?? '-'); ?></div>
                                    <div class="book-footer d-flex flex-column gap-2 mt-3">
                                        <div class="d-flex gap-2 w-100">
                                            <button type="button" class="btn btn-outline-warning btn-sm flex-fill" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalReview" 
                                                    data-id-buku="<?= $row['book_id']; ?>" 
                                                    data-judul-buku="<?= htmlspecialchars($row['title'] ?? 'Buku'); ?>">
                                                <i class="fa-solid fa-star"></i> Review
                                            </button>
                                            <a href="baca_buku.php?id=<?= $row['book_id']; ?>" class="detail-btn btn-read-book m-0 text-center flex-fill" style="line-height: 2.2;">Baca Buku</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php 
                        } 
                    } else { 
                        echo '<div class="col-12 text-center py-5">';
                        if (!empty($search) || !empty($category_filter)) {
                            echo '<p class="text-muted fs-5">Buku dengan kriteria pencarian Anda tidak ditemukan.</p>
                                  <a href="buku_saya.php" class="btn btn-secondary px-4 py-2 mt-2 d-inline-flex align-items-center justify-content-center">Kembali ke Semua Koleksi</a>';
                        } else {
                            echo '<p class="text-muted fs-5">Anda belum memiliki koleksi buku. Yuk, cari buku favoritmu sekarang!</p>
                                  <a href="koleksi.php" class="btn btn-primary btn-sm px-4 py-2 mt-2" style="border-radius: 50px; background: linear-gradient(135deg, #2e245b, #5640a0); border:none; padding: 10px 20px !important;">Lihat Katalog Buku</a>';
                        }
                        echo '</div>';
                    } 
                    ?>

                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class="pagination justify-content-center m-0">
                            
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-line page-link" href="?page=<?= $page - 1; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $i; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-line page-link" href="?page=<?= $page + 1; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?><?= !empty($category_filter) ? '&category=' . urlencode($category_filter) : ''; ?>">Next</a>
                            </li>

                        </ul>
                    </nav>
                <?php endif; ?>

            </section>
        </div>
    </main>

    <div class="modal fade" id="modalReview" tabindex="-1" aria-labelledby="modalReviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../../models/proses-review.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReviewLabel">Beri Ulasan Buku</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Buku: <strong id="review-book-title" class="text-dark"></strong></p>
                        
                        <input type="hidden" name="id_buku" id="review-id-buku">
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label fw-semibold">Rating Buku</label>
                            <select class="form-select" name="rating" id="rating" required>
                                <option value="">-- Pilih Bintang --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                                <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                <option value="2">⭐⭐ (2 - Kurang)</option>
                                <option value="1">⭐ (1 - Buruk)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="ulasan" class="form-label fw-semibold">Ulasan Anda</label>
                            <textarea class="form-control" name="ulasan" id="ulasan" rows="4" placeholder="Ceritakan pengalaman Anda membaca buku ini..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white fw-bold">Kirim Ulasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const modalReview = document.getElementById('modalReview');
        if (modalReview) {
            modalReview.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const idBuku = button.getAttribute('data-id-buku');
                const judulBuku = button.getAttribute('data-judul-buku');

                const modalTitleSpace = modalReview.querySelector('#review-book-title');
                const modalIdInput = modalReview.querySelector('#review-id-buku');

                modalTitleSpace.textContent = judulBuku;
                modalIdInput.value = idBuku;
            });
        }
    </script>

    <script>
    document.querySelector('#modalReview form').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        let formData = new FormData(this);

        fetch('../../models/proses-review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Tutup modal review
                let modal = bootstrap.Modal.getInstance(document.getElementById('modalReview'));
                modal.hide();
                
                // SweetAlert sukses dengan timer 3 detik
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    showConfirmButton: false, // Menghilangkan tombol OK
                    timer: 3000,              // 3 detik
                    timerProgressBar: true,   // Menampilkan progress bar biar makin estetik
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        });
    });
</script>
</body>
</html>