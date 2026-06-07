<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/url-helper.php';

$authError = $_SESSION['error'] ?? null;
$authSuccess = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../../assets/css/auth/forgot-password.css">
    
    <title>Galxy DigiBook - Forgot Password</title>
</head>
<body>

    <div class="wrapper-card">
        <div class="logo-container">
            <img src="<?php echo base_url('assets/pic/logo.png'); ?>" alt="CosmiRead Logo" class="app-logo">
        </div>

        <form action="<?php echo base_url('index.php?action=proses_lupa_password'); ?>" method="POST">
            <h1>Forgot Password</h1>
            <p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>
            
            <div class="input-box">
                <label for="email">Email Address</label>
                <div class="input-field-wrapper">
                    <i class="fa-solid fa-envelope field-icon"></i>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Send Reset Link</button>
            
            <div class="back-link-box">
                <a href="<?php echo base_url('views/auth/auth.php'); ?>"><i class="fa-solid fa-arrow-left"></i> Back to Sign In</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($authError || $authSuccess): ?>
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: '<?php echo $authError ? "error" : "success"; ?>',
                title: '<?php echo $authError ? "Gagal" : "Berhasil"; ?>',
                text: '<?php echo $authError ?: $authSuccess; ?>',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        </script>
    <?php endif; ?>
</body>
</html>