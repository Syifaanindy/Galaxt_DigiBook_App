<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Buku - BookStore</title>

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
                <img src="../../assets/img/cover-buku.jpg" class="img-fluid rounded mb-3" alt="Cover Buku">
                <h4>Judul Buku Anda</h4>
                <p class="text-muted">Karya: Kuntowijoyo</p>
                
                <div class="rating mb-2">
                    <i class="fa-solid fa-star text-warning"></i> 4.8/5.0
                </div>
                
                <hr>
                <h5>Ulasan Pembaca</h5>
                <div class="ulasan-list">
                    <p><em>"Buku yang sangat inspiratif!"</em> - Budi</p>
                    <p><em>"Wawasan baru bagi sejarah."</em> - Siti</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <section class="read-book-card p-4">
                            <div class="pdf-toolbar d-flex justify-content-between mb-3">
                                <span class="pdf-meta-chip"><i class="fa-solid fa-file-pdf"></i> PDF Premium</span>
                                <div>
                                    <a href="../../assets/book/book1.pdf" target="_blank" class="btn btn-sm btn-outline-secondary">Buka Tab Baru</a>
                                </div>
                            </div>
                            <div class="pdf-viewer-wrap">
                                <iframe class="pdf-viewer" src="../../assets/book/book1.pdf" width="100%" height="600px"></iframe>
                            </div>
                        </section>
                    </div>
                </div>
            </section>

            <section class="next-books-section py-5 bg-light">
                <div class="container">
                    <h3 class="mb-4">Buku Selanjutnya untuk Anda</h3>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card h-100">
                                <img src="../../assets/img/buku2.jpg" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Judul Buku Lain</h5>
                                    <a href="#" class="btn btn-primary btn-sm">Baca Sekarang</a>
                                </div>
                            </div>
                        </div>
                        </div>
                </div>
            </section>
        </div>
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
</body>
</html>
