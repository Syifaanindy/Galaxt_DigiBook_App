<?php
// Mendeteksi nama file yang sedang aktif saat ini (contoh: dashboard.php)
$halaman_aktif = basename($_SERVER['PHP_SELF']);
?>

<header class="header">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <img src="../../assets/pic/logo.png" alt="Logo">
            <a class="navbar-brand" href="index.php">Galaxy Digi Book</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                
                <ul class="navbar-nav ms-auto me-5">
<<<<<<< HEAD:views/user/partials/navbar.php
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($halaman_aktif == 'dashboard.php') ? 'active' : ''; ?>" data-nav="home" href="dashboard.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($halaman_aktif == 'koleksi.php') ? 'active' : ''; ?>" data-nav="koleksi" href="koleksi.php">Koleksi Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($halaman_aktif == 'kontak.php') ? 'active' : ''; ?>" data-nav="kontak" href="kontak.php">Ulasan Website</a>
=======
                    <li class="nav-item"><a class="nav-link" data-nav="home" href="dashboard.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" data-nav="koleksi" href="koleksi.php">Koleksi Kami</a>
>>>>>>> Fara:views/user/partials/navbar.html
                    </li>
                </ul>

                <div class="social-links">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle user-menu-toggle" data-nav="profile"
                            aria-label="Menu Pengguna" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end user-menu-dropdown">
                            <li><a class="dropdown-item" href="keranjang.php"><i
                                        class="fa-solid fa-cart-shopping me-2"></i>Keranjang</a></li>
                            
                            <li><a class="dropdown-item" href="buku_saya.php"><i
                                        class="fa-solid fa-book-open-reader me-2"></i>Buku Saya</a></li>
                            <li><a class="dropdown-item" href="riwayat_transaksi.php"><i
                                        class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat Transaksi</a></li>
                            
                            <li><a class="dropdown-item" href="profil.php"><i
                                        class="fa-solid fa-id-card me-2"></i>Akun</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../index.php?action=logout"><i
                                        class="fa-solid fa-right-from-bracket me-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>