<?php
session_start();

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/models/profil-model.php';

if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = 'admin@local.test'; // Akun simulasi
}

$email_login = $_SESSION['email'];

// 1. Ambil informasi utama akun user (id, username, email, picture)
$queryUser = $conn->prepare("SELECT id, username, email, picture FROM users WHERE email = ?");
$queryUser->bind_param("s", $email_login);
$queryUser->execute();
$dataUser = $queryUser->get_result()->fetch_assoc();
$userId = $dataUser['id'] ?? 0;

$user_name   = $dataUser['username'] ?? 'User Galaxy';
$user_email  = $dataUser['email'] ?? $email_login;
$user_avatar = $dataUser['picture'] ?? null; // Tampung nama file gambar

// 2. Hitung total item di Cart milik user
$queryCart = $conn->prepare("SELECT COUNT(*) AS total_cart FROM cart WHERE user_id = ?");
$queryCart->bind_param("i", $userId);
$queryCart->execute();
$dataCart = $queryCart->get_result()->fetch_assoc();
$totalWishlist = $dataCart['total_cart'] ?? 0;

// 3. Hitung total ulasan buku yang pernah ditulis oleh user ini
$queryReview = $conn->prepare("SELECT COUNT(*) AS total_review FROM book_reviews WHERE user_id = ?");
$queryReview->bind_param("i", $userId);
$queryReview->execute();
$dataReview = $queryReview->get_result()->fetch_assoc();
$totalUlasan = $dataReview['total_review'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Galaxy Digi Book</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="../../assets/css/user/user.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <style>
        .btn-edit-profile { background: #2b256d; color: white; border: none; padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: 0.3s; }
        .btn-edit-profile:hover { background: #1f1b52; color: white; }
        .btn-save-modal { background: #2b256d; color: white; border-radius: 8px; font-weight: 600; }
        .btn-save-modal:hover { background: #1f1b52; color: white; }
        .profile-avatar { width: 90px; height: 90px; border-radius: 50%; background: #2b256d; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; overflow: hidden; border: 3px solid rgba(255,255,255,0.4); }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>

<body data-page="profile">
    <div id="site-navbar"></div>

    <main class="profile-page">
        <section class="container py-5">
            <div class="profile-hero">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($user_avatar) && file_exists(__DIR__ . '/../../assets/img/profile/' . $user_avatar)): ?>
                            <img src="../../assets/img/profile/<?= htmlspecialchars($user_avatar) ?>" alt="Foto Profil">
                        <?php else: ?>
                            <?php 
                                $words = explode(" ", $user_name);
                                echo strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                            ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="profile-name"><?= htmlspecialchars($user_name) ?></h1>
                        <p class="profile-email mb-1"><?= htmlspecialchars($user_email) ?></p>
                        <div class="profile-meta">
                            <span><i class="fa-solid fa-book-open-reader me-2"></i><?= $totalWishlist ?> Buku Tersimpan</span>
                            <span><i class="fa-solid fa-clock-rotate-left me-2"></i>Aktif sejak 2026</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-7">
                    <div class="profile-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title m-0">Informasi Akun</h2>
                            <button class="btn-edit-profile" data-bs-toggle="modal" data-bs-target="#modalEditProfil">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Akun
                            </button>
                        </div>
                        
                        <ul class="detail-list">
                            <li><span>Username</span><strong><?= htmlspecialchars($user_name) ?></strong></li>
                            <li><span>Email Pengguna</span><strong><?= htmlspecialchars($user_email) ?></strong></li>
                            <li><span>Password</span><strong>********</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="profile-card">
                        <h2 class="card-title">Aktivitas Singkat</h2>
                        <div class="activity-item">
                            <i class="fa-solid fa-bookmark"></i>
                            <div>
                                <p class="activity-title">Wishlist</p>
                                <small><?= $totalWishlist ?> buku menunggu checkout</small>
                            </div>
                        </div>
                        <div class="activity-item">
                            <i class="fa-solid fa-star"></i>
                            <div>
                                <p class="activity-title">Ulasan Diberikan</p>
                                <small><?= $totalUlasan ?> ulasan buku terkirim</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalEditProfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Ubah Informasi Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../controllers/profil-controller.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Username Baru</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user_name) ?>" required style="border-radius: 8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Email Baru</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>" required style="border-radius: 8px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Foto Profil Baru (Format: JPG/PNG)</label>
                            <input type="file" name="picture" class="form-control" accept="image/png, image/jpeg, image/jpg" style="border-radius: 8px;">
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-secondary small fw-semibold">Password Baru (Kosongkan jika tidak diganti)</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password baru jika ingin mengubah" style="border-radius: 8px;">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3 fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-save-modal px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>

    <script>
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'updated') {
            Toast.fire({ icon: 'success', title: '<strong>Berhasil</strong>', html: '<span style="color: #545454;">Data akun berhasil diperbarui!</span>' })
            .then(() => { window.history.replaceState({}, document.title, window.location.pathname); });
        } else if (urlParams.get('status') === 'failed') {
            Toast.fire({ icon: 'error', title: '<strong>Gagal</strong>', html: '<span style="color: #545454;">Gagal memperbarui data akun.</span>' })
            .then(() => { window.history.replaceState({}, document.title, window.location.pathname); });
        }
    </script>
</body>
</html>