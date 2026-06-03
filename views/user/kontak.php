<?php
require_once dirname(__DIR__, 2) . '/controllers/ulasan-website-controller.php';
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
</head>

<body data-page="kontak">
    <div id="site-navbar"></div>

    <main class="contact-page py-5">
        <section class="container">
            <div class="hero-box mb-4">
                <h1>Ulasan Website</h1>
                <p>Bantu kami meningkatkan kualitas Galaxy Digi Book dengan memberikan ulasan, kritik, atau saran terbaik Anda.</p>
            </div>

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