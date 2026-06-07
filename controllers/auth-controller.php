<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/url-helper.php';
require_once __DIR__ . '/../models/user-model.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer library (Sesuaikan path jika letak foldernya berbeda)
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

$aksi = $_GET['action'] ?? '';

if ($aksi === 'login') {
    prosesLogin();
} elseif ($aksi === 'register') {
    prosesRegister();
} elseif ($aksi === 'create-default-admin') {
    prosesCreateDefaultAdmin();
} elseif ($aksi === 'logout') {
    prosesLogout();
} elseif ($aksi === 'proses_lupa_password') { 
    prosesLupaPassword();
} else {
    include __DIR__ . '/../views/auth/auth.php';
}

function prosesLogin() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectAuth();
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username dan password wajib diisi.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    $dataUser = cariUserByUsername($conn, $username);

    if ($dataUser && password_verify($password, $dataUser['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']  = $dataUser['id'];
        $_SESSION['username'] = $dataUser['username'];
        $_SESSION['email']    = $dataUser['email'];
        $_SESSION['role']     = $dataUser['role'];

        redirectByRole($dataUser['role']);
        exit;
    } else {
        $_SESSION['error'] = "Username atau password salah.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }
}

function prosesRegister() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectAuth();
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'user';

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Username, Email, dan Password wajib diisi.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (strlen($username) < 3) {
        $_SESSION['error'] = "Username minimal 3 karakter.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password minimal 6 karakter.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (usernameSudahTerdaftar($conn, $username)) {
        $_SESSION['error'] = "Username sudah terdaftar.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    if (!buatUser($conn, $username, $email, $password, $role)) {
        $_SESSION['error'] = "Registrasi gagal. Silakan coba lagi.";
        $_SESSION['auth_mode'] = 'register';
        redirectAuth();
        exit;
    }

    $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
    $_SESSION['auth_mode'] = 'login';
    redirectAuth();
    exit;
}

function prosesLogout() {
    session_unset();
    session_destroy();
    redirectAuth();
    exit;
}

function prosesCreateDefaultAdmin() {
    global $conn;

    if (adminSudahAda($conn)) {
        $_SESSION['success'] = "Admin default sudah ada. Silakan login.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    if (usernameSudahTerdaftar($conn, 'admin')) {
        $_SESSION['error'] = "Username admin sudah dipakai, tapi role admin belum ada.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    if (!buatUser($conn, 'admin', 'admin123', 'admin')) {
        $_SESSION['error'] = "Gagal membuat admin default.";
        $_SESSION['auth_mode'] = 'login';
        redirectAuth();
        exit;
    }

    $_SESSION['success'] = "Admin default berhasil dibuat. Login dengan username admin dan password admin123.";
    $_SESSION['auth_mode'] = 'login';
    redirectAuth();
    exit;
}

// ⬇️ PROSES LUPA PASSWORD YANG SUDAH TERKONFIGURASI GMAIL PRIBADI ⬇️
function prosesLupaPassword() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . base_url('views/auth/lupa-password.php'));
        exit;
    }

    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));

    if (empty($email)) {
        $_SESSION['error'] = "Email address is required.";
        header("Location: " . base_url('views/auth/lupa-password.php'));
        exit;
    }

    // Periksa apakah email terdaftar di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($query) > 0) {
        $token = bin2hex(random_bytes(32)); 
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Update token ke tabel user
        mysqli_query($conn, "UPDATE users SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'");

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'najasnndy290505@gmail.com';     // Menggunakan Gmail Pribadi Anda
            $mail->Password   = 'ahipnwxpyoelqkld';             // Menggunakan 16 digit Sandi Aplikasi Google Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Parameter kedua diubah menjadi 'Galaxy DigiBook Support'
            $mail->setFrom('najasnndy290505@gmail.com', 'Galaxy DigiBook Support');
            $mail->addAddress($email);

            $linkReset = base_url("views/auth/reset-password.php?token=" . $token);
            
            $mail->isHTML(true);
            $mail->isHTML(true);
            $mail->isHTML(true);
            $mail->Subject = 'Permintaan Atur Ulang Kata Sandi - Galaxy DigiBook'; // Mengubah Subjek Email
            $mail->Body    = "<h3>Halo!,</h3>
                            <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Galaxy DigiBook Anda.</p>
                            <p>Silakan klik tombol di bawah ini untuk membuat kata sandi baru. Tautan ini hanya berlaku selama 15 menit demi keamanan akun Anda:</p>
                            <p><a href='$linkReset' style='background-color: #512da8; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 5px; font-weight: bold;'>Atur Ulang Kata Sandi</a></p>
                            <p>Jika Anda tidak merasa melakukan permintaan ini, Anda dapat mengabaikan email ini dengan aman.</p>
                  <br>
                  <p>Salam hangat,<br><b>Tim Support Galaxy DigiBook</b></p>";
            $mail->send();
            
            $_SESSION['success'] = "The reset link has been sent to your Gmail!";
            header("Location: " . base_url('views/auth/lupa-password.php'));
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send email. Error: {$mail->ErrorInfo}";
            header("Location: " . base_url('views/auth/lupa-password.php'));
            exit;
        }
    } else {
        $_SESSION['error'] = "Email address is not registered!";
        header("Location: " . base_url('views/auth/lupa-password.php'));
        exit;
    }
}

function redirectAuth() {
    header("Location: " . base_url('views/auth/auth.php'));
}

function redirectByRole($role) {
    if ($role === 'admin') {
        header("Location: " . base_url('views/admin/dashboard.php'));
        return;
    }

    header("Location: " . base_url('views/user/dashboard.php'));
}
?>