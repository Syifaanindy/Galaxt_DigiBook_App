<?php
require_once dirname(__DIR__, 2) . '/controllers/ulasan-website-controller.php';
<<<<<<< HEAD

// Menangkap status dari URL untuk menampilkan pesan
$status = isset($_GET['status']) ? $_GET['status'] : '';
=======
>>>>>>> 7ea7195ea7d74114cb9aee61ea7b3a06b1e3cdb1
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Website - Galaxy Digi Book</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/kontak.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/kontak.css">
</head>
<<<<<<< HEAD
<body data-page="Kontak">
=======

<body data-page="kontak">
>>>>>>> 7ea7195ea7d74114cb9aee61ea7b3a06b1e3cdb1
    <div id="site-navbar"></div>

    <main class="contact-page py-5">
        <section class="container">
            <div class="hero-box mb-4">
                <h1>Ulasan Website</h1>
                <p>Bantu kami meningkatkan kualitas Galaxy Digi Book dengan memberikan ulasan, kritik, atau saran terbaik Anda.</p>
            </div>

<<<<<<< HEAD
        <div class="card p-4 shadow-sm border-0" style="border-radius: 16px;">
            <h3 class="fw-bold mb-3" style="color: #2b256d;">Kirim Ulasan Website</h3>
            
            <?php if($status === 'success'): ?>
                <div class="alert alert-success">Ulasan berhasil dikirim! Terima kasih.</div>
            <?php endif; ?>
            <?php if($status === 'failed'): ?>
                <div class="alert alert-danger">Gagal mengirim ulasan. Silakan coba lagi.</div>
            <?php endif; ?>
            <?php if($status === 'invalid'): ?>
                <div class="alert alert-warning">Mohon isi semua data dengan benar!</div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label延 for="namaInput" class="form-label fw-semibold">Nama</label>
                    <input type="text" id="namaInput" name="nama" class="form-control" placeholder="Tulis nama Anda..." required>
                </div>

                <div class="mb-3">
                    <label for="emailInput" class="form-label fw-semibold">Email</label>
                    <input type="email" id="emailInput" name="email" class="form-control" placeholder="Tulis email Anda..." required>
                </div>

                <div class="mb-3">
                    <label for="ratingSelect" class="form-label fw-semibold">Rating</label>
                    <select id="ratingSelect" name="rating" class="form-select" required>
                        <option value="" disabled selected>Pilih rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                        <option value="2">⭐⭐ (2 - Kurang Puas)</option>
                        <option value="1">⭐ (1 - Buruk)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="commentTextArea" class="form-label fw-semibold">Ulasan</label>
                    <textarea id="commentTextArea" name="ulasan" class="form-control" rows="5" placeholder="Tulis ulasan website kamu..." required></textarea>
                </div>

                <button type="submit" class="btn text-white px-4 py-2" style="background: #2b256d; border-radius: 10px; font-weight: 600;">Kirim Ulasan</button>
            </form>
        </div>
=======
            <div class="row g-4">
                <div class="col-12">
                    <div class="contact-card">
                        <h3>Kirim Ulasan Website</h3>
                        
                        <form class="contact-form" action="../../controllers/ulasan-website-controller.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nama">Nama</label>
                                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Tulis nama Anda" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label" for="rating">Rating</label>
                                    <select id="rating" name="rating" class="form-select" required>
                                        <option value="" disabled selected>Pilih rating</option>
                                        <option value="5">5 - Sangat bagus</option>
                                        <option value="4">4 - Bagus</option>
                                        <option value="3">3 - Cukup</option>
                                        <option value="2">2 - Kurang</option>
                                        <option value="1">1 - Tidak rekomendasi</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label" for="ulasan">Ulasan</label>
                                    <textarea id="ulasan" name="ulasan" class="form-control" rows="5" placeholder="Tulis ulasan website kamu..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="send-btn">Kirim Ulasan</button>
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </section>
>>>>>>> 7ea7195ea7d74114cb9aee61ea7b3a06b1e3cdb1
    </main>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Toast.fire({
                icon: 'success',
                title: '<strong>Berhasil</strong>',
                html: '<span style="color: #545454;">Ulasan berhasil ditambahkan!</span>'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else if (status === 'failed' || status === 'invalid') {
            Toast.fire({
                icon: 'error',
                title: '<strong>Gagal</strong>',
                html: '<span style="color: #545454;">Gagal mengirim ulasan website.</span>'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    </script>
</body>

</html>