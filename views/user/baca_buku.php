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
    <div id="site-navbar"></div>

    <main class="read-book-page-container py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item"><a href="buku_saya.html">Buku Saya</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Baca Buku</li>
                </ol>
            </nav>

            <div class="mb-4">
                <span class="section-subtitle">Reader Mode</span>
                <h1 class="read-book-title fw-extrabold mb-1">Makrifat Daun: Daun Makrifat</h1>
                <p class="text-muted mb-0">Baca nyaman langsung dari library digital Anda.</p>
            </div>

            <section class="read-book-card p-4 p-md-5">
                <div class="pdf-toolbar">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="pdf-meta-chip"><i class="fa-solid fa-file-pdf"></i> PDF Premium</span>
                        <span class="pdf-meta-chip"><i class="fa-solid fa-user-pen"></i> Kuntowijoyo</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="../../assets/book/book1.pdf" target="_blank" class="btn btn-outline-cart btn-sm">
                            <i class="fa-solid fa-up-right-from-square me-1"></i>Buka Tab Baru
                        </a>
                        <a href="../../assets/book/book1.pdf" download class="btn btn-primary-buy btn-sm">
                            <i class="fa-solid fa-download me-1"></i>Unduh
                        </a>
                    </div>
                </div>

                <div class="pdf-viewer-wrap">
                    <iframe class="pdf-viewer" src="../../assets/book/book1.pdf#toolbar=1&navpanes=0&scrollbar=1" title="Baca Buku PDF"></iframe>
                </div>
            </section>
        </div>
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
</body>
</html>
