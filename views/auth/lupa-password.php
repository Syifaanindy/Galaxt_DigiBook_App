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
    <link rel="stylesheet" href="../../assets/css/auth/login.css"> <title>CosmiRead - Forgot Password</title>
</head>
<body>
    <div class="container" id="container" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
        <div style="padding: 40px; width: 100%; max-width: 400px; text-align: center;">
            <form action="<?php echo base_url('index.php?action=proses_lupa_password'); ?>" method="POST">
                <h1>Forgot Password</h1>
                <p style="margin: 20px 0; color: #555; font-size: 14px;">Enter your email address and we'll send you a link to reset your password.</p>
                
                <input type="email" name="email" placeholder="Enter your registered email" required style="width: 100%; padding: 10px; margin: 8px 0; background-color: #eee; border: none; outline: none; border-radius: 8px;">
                
                <button type="submit" style="margin-top: 15px; background-color: #512da8; color: #fff; font-size: 12px; padding: 10px 45px; border: 1px solid transparent; border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; cursor: pointer;">Send Reset Link</button>
                
                <div style="margin-top: 20px;">
                    <a href="<?php echo base_url('views/auth/login.php'); ?>" style="color: #333; font-size: 13px; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to Sign In</a>
                </div>
            </form>
        </div>
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