<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
$cover_path = '../../assets/cover/';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query Data Buku
$query = "SELECT b.* FROM books b
          INNER JOIN transaction_items ti ON b.id = ti.book_id
          INNER JOIN transactions t ON ti.transaction_id = t.id
          WHERE b.id = ? AND t.user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();

if (!$buku) {
    echo "<script>alert('Akses ditolak!'); window.location.href='buku_saya.php';</script>";
    exit;
}

$query_next = "SELECT b.id, b.title, b.cover_image, b.author
               FROM books b
               INNER JOIN transaction_items ti ON b.id = ti.book_id
               INNER JOIN transactions t ON ti.transaction_id = t.id
               WHERE t.user_id = ? AND b.id != ? 
               GROUP BY b.id";

$stmt_next = $conn->prepare($query_next);
$stmt_next->bind_param("ii", $user_id, $id);
$stmt_next->execute();
$buku_lainnya = $stmt_next->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Buku - <?php echo htmlspecialchars($buku['title']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/baca-buku.css">
</head>
<body data-page="profile">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="read-book-page-container py-5">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb custom-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="buku_saya.php">Buku Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Baca Buku</li>
            </ol>
        </nav>

        <div class="mb-4">
            <span class="section-subtitle"><h3><b>Reader Mode</b></h3></span>
            <p class="text-muted mb-0">Baca nyaman langsung dari library digital Anda.</p>
        </div>

        <section class="read-book-content py-4">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card p-4 mb-4">
                        <img src="<?= $cover_path . basename($buku['cover_image'] ?? 'default.jpg'); ?>" 
                            alt="Cover Buku" 
                            onerror="this.src='../../assets/cover/default.jpg';">
                        <h4><?php echo htmlspecialchars($buku['title']); ?></h4>
                        <p class="text-muted">Karya: <?php echo htmlspecialchars($buku['author']); ?></p>
                        <div class="rating mb-2">
                            <i class="fa-solid fa-star text-warning"></i> <?php echo $buku['rating'] ?? '5.0'; ?>/5.0
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <section class="read-book-card p-4">
                        <div class="pdf-toolbar d-flex justify-content-between mb-3 align-items-center">
                            <span class="pdf-meta-chip"><i class="fa-solid fa-file-pdf"></i> PDF Premium</span>
                            
                            <div class="d-flex gap-2">
                                <?php if (!empty($buku['file_path'])): ?>
                                    <a href="../../<?php echo htmlspecialchars($buku['file_path']); ?>" download class="btn btn-glow-purple"><i class="fa-solid fa-download"></i> Unduh</a>
                                    <a href="../../<?php echo htmlspecialchars($buku['file_path']); ?>" target="_blank" class="btn-new-tab">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 8px;"></i> 
                                        Buka
                                    </a>
                                <?php else: ?>
                                    <span class="text-danger">File tidak tersedia</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="pdf-viewer-wrap">
                            <?php if (!empty($buku['file_path'])): ?>
                                <iframe
                                    class="pdf-viewer"
                                    src="../../<?php echo htmlspecialchars($buku['file_path']); ?>#toolbar=0&navpanes=0&scrollbar=0"
                                    width="100%"
                                    height="800px">
                                </iframe>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">File PDF belum diunggah.</div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <section class="next-books-section">
            <div class="next-books-wrapper"> <h3 class="section-title"><b>Buku Selanjutnya</b></h3>
                <div class="book-grid">
                    <?php while($row = $buku_lainnya->fetch_assoc()): ?>
                    <div class="next-book-card">
                        <img src="<?= $cover_path . basename($row['cover_image'] ?? 'default.jpg'); ?>" 
                                alt="Cover Buku" 
                                onerror="this.src='../../assets/cover/default.jpg';">
                        <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p><strong>Penulis:</strong> <?php echo htmlspecialchars($row['author'] ?? 'Tidak diketahui'); ?></p>
                        <div class="card-actions">
                            <button
                                type="button"
                                class="btn-review"
                                data-bs-toggle="modal"
                                data-bs-target="#modalReview"
                                data-id-buku="<?= $row['id']; ?>"
                                data-judul-buku="<?= htmlspecialchars($row['title']); ?>">
                                <i class="fa-solid fa-star"></i> Review
                            </button>
                            <a href="baca_buku.php?id=<?php echo $row['id']; ?>" class="btn-read">Baca Buku</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    </div>
</main>
    <div class="modal fade" id="modalReview" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../models/proses-review.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Beri Ulasan Buku</h5>
                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p>
                        Buku:
                        <strong id="review-book-title"></strong>
                    </p>

                    <input
                        type="hidden"
                        name="id_buku"
                        id="review-id-buku">

                    <div class="mb-3">
                        <label class="form-label">
                            Rating
                        </label>

                        <select
                            class="form-select"
                            name="rating"
                            required>

                            <option value="">Pilih Rating</option>
                            <option value="5">⭐⭐⭐⭐⭐</option>
                            <option value="4">⭐⭐⭐⭐</option>
                            <option value="3">⭐⭐⭐</option>
                            <option value="2">⭐⭐</option>
                            <option value="1">⭐</option>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ulasan
                        </label>

                        <textarea
                            class="form-control"
                            name="ulasan"
                            rows="4"
                            required></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning text-white">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    <div id="site-footer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    <script>
const modalReview =
document.getElementById('modalReview');

if(modalReview){

    modalReview.addEventListener(
        'show.bs.modal',
        function(event){

            const button =
            event.relatedTarget;

            document.getElementById(
                'review-book-title'
            ).textContent =
            button.getAttribute(
                'data-judul-buku'
            );

            document.getElementById(
                'review-id-buku'
            ).value =
            button.getAttribute(
                'data-id-buku'
            );
        }
    );
}
</script>
</body>
</html>