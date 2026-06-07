<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/url-helper.php';

$token = $_GET['token'] ?? '';
$isValid = false;
$userData = null;

// Validasi token yang dikirim dari email
if (!empty($token)) {
    $token = mysqli_real_escape_string($conn, $token);
    $currentTime = date("Y-m-d H:i:s");
    
    // Cari token yang cocok dan belum kedaluwarsa
    $query = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token' AND token_expiry > '$currentTime'");
    if (mysqli_num_rows($query) > 0) {
        $isValid = true;
        $userData = mysqli_fetch_assoc($query);
    }
}

// Proses update password saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
    $passwordBaru = trim($_POST['password'] ?? '');
    $konfirmasiPassword = trim($_POST['confirm_password'] ?? '');
    
    // Aturan Kombinasi: Minimal 8 Karakter, 1 Huruf Besar, 1 Huruf Kecil, 1 Angka
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';
    
    if (empty($passwordBaru)) {
        $_SESSION['error'] = "Password baru tidak boleh kosong.";
    } elseif (!preg_match($pattern, $passwordBaru)) {
        $_SESSION['error'] = "Password tidak memenuhi syarat keamanan minimum.";
    } elseif ($passwordBaru !== $konfirmasiPassword) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok dengan password baru.";
    } else {
        // Hash password baru dan hapus token agar tidak bisa digunakan lagi
        $passwordHash = password_hash($passwordBaru, PASSWORD_BCRYPT);
        $userId = $userData['id'];
        
        $update = mysqli_query($conn, "UPDATE users SET password='$passwordHash', reset_token=NULL, token_expiry=NULL WHERE id=$userId");
        
        if ($update) {
            $_SESSION['success'] = "Password berhasil diperbarui! Silakan login.";
            $_SESSION['auth_mode'] = 'login';
            header("Location: " . base_url('views/auth/auth.php'));
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui password. Silakan coba lagi.";
        }
    }
}

$authError = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../../assets/css/auth/reset-password.css">
    
    <title>CosmiRead - Reset Password</title>
</head>
<body>

    <div class="wrapper-card">
        <?php if ($isValid): ?>
            <div class="logo-container" style="margin-bottom: 15px; display: flex; justify-content: center;">
                <img src="<?php echo base_url('assets/pic/logo.png'); ?>" alt="CosmiRead Logo" style="max-height: 65px; width: auto; object-fit: contain;">
            </div>

            <form action="" method="POST" id="resetForm">
                <h1>Set Password Baru</h1>
                <p class="subtitle">Silakan buat kata sandi baru yang kuat untuk mengamankan akun DigiBook Anda.</p>
                
                <div class="input-box">
                    <label for="password">Kata Sandi Baru</label>
                    <div class="input-field-wrapper">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan kata sandi baru" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)"></i>
                    </div>
                </div>

                <div class="input-box">
                    <label for="confirm_password">Konfirmasi Kata Sandi</label>
                    <div class="input-field-wrapper">
                        <i class="fa-solid fa-shield-halved field-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi baru" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                    </div>
                </div>
                
                <div class="requirements-container">
                    <p>Syarat Keamanan:</p>
                    <div class="req-item" id="req-length"><i class="fa-solid fa-circle"></i> Minimal 8 Karakter</div>
                    <div class="req-item" id="req-uppercase"><i class="fa-solid fa-circle"></i> Minimal 1 Huruf Besar (A-Z)</div>
                    <div class="req-item" id="req-lowercase"><i class="fa-solid fa-circle"></i> Minimal 1 Huruf Kecil (a-z)</div>
                    <div class="req-item" id="req-number"><i class="fa-solid fa-circle"></i> Minimal 1 Angka (0-9)</div>
                    <div class="req-item" id="req-match"><i class="fa-solid fa-circle"></i> Konfirmasi Sandi Cocok</div>
                </div>
                
                <button type="submit" class="btn-submit">Perbarui Kata Sandi</button>
            </form>
        <?php else: ?>
            <div class="error-state">
                <div class="logo-container" style="margin-bottom: 25px; display: flex; justify-content: center; opacity: 0.6;">
                    <img src="<?php echo base_url('assets/pic/logo.png'); ?>" alt="CosmiRead Logo" style="max-height: 55px; width: auto; object-fit: contain; filter: grayscale(30%);">
                </div>
                <i class="fa-solid fa-circle-exclamation" style="font-size: 60px; color: #e74c3c; margin-bottom: 15px; display: block;"></i>
                <h2 style="color: #433878; font-weight: 700; font-size: 22px;">Tautan Kadaluwarsa</h2>
                <p class="subtitle" style="margin-top: 10px; margin-bottom: 25px;">Maaf, token keamanan pengaturan ulang sandi Anda sudah tidak valid atau telah melewati batas kedaluwarsa 15 menit.</p>
                <a href="<?php echo base_url('views/auth/lupa-password.php'); ?>" class="btn-back">Minta Tautan Baru</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Fungsi Show/Hide Mata Password
        function togglePasswordVisibility(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                iconElement.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        if (passwordInput && confirmInput) {
            function validatePassword() {
                const val = passwordInput.value;
                const confVal = confirmInput.value;

                updateIndicator('req-length', val.length >= 8);
                updateIndicator('req-uppercase', /[A-Z]/.test(val));
                updateIndicator('req-lowercase', /[a-z]/.test(val));
                updateIndicator('req-number', /\d/.test(val));
                updateIndicator('req-match', val === confVal && confVal !== '');
            }

            function updateIndicator(id, isValid) {
                const item = document.getElementById(id);
                const icon = item.querySelector('i');
                if (isValid) {
                    item.className = 'req-item valid';
                    icon.className = 'fa-solid fa-circle-check';
                } else {
                    item.className = 'req-item';
                    icon.className = 'fa-solid fa-circle';
                }
            }

            passwordInput.addEventListener('input', validatePassword);
            confirmInput.addEventListener('input', validatePassword);

            // Intersept validasi sebelum submit form dijalankan
            document.getElementById('resetForm').addEventListener('submit', function(e) {
                const val = passwordInput.value;
                const confVal = confirmInput.value;
                const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

                if (!pattern.test(val) || val !== confVal) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Keamanan Lemah',
                        text: 'Silakan penuhi semua parameter indikator hijau sebelum memperbarui sandi.',
                        confirmButtonColor: '#433878' /* Warna tombol disamakan dengan tema ungu */
                    });
                }
            });
        }
    </script>

    <?php if ($authError): ?>
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Gagal',
                text: '<?php echo $authError; ?>',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        </script>
    <?php endif; ?>
</body>
</html>