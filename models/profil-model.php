<?php
class ProfilModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    // Ambil profil berdasarkan email
    public function getProfilByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, username, email, picture FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // TAMBAHAN: Cek apakah email baru sudah dipakai oleh USER LAIN
    // Ini penting banget buat mencegah duplikasi email saat update!
    public function isEmailTaken($email_baru, $email_lama) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND email != ?");
        $stmt->bind_param("ss", $email_baru, $email_lama);
        $stmt->execute();
        $stmt->store_result();
        $rows = $stmt->num_rows;
        $stmt->close();
        return $rows > 0; // Mengembalikan true jika email sudah ada yang pakai
    }

    // HITUNG TOTAL WISHLIST
    public function getTotalWishlist($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total_cart FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total_cart'] ?? 0;
    }

    // HITUNG TOTAL ULASAN
    public function getTotalUlasan($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total_review FROM book_reviews WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total_review'] ?? 0;
    }

    // Update profil
    public function updateProfil($email_lama, $username, $email_baru, $password, $picture) {
        // PERBAIKAN 1: Validasi duplikasi email sebelum melakukan query update
        if ($this->isEmailTaken($email_baru, $email_lama)) {
            return false; 
        }

        $stmt = null; // Inisialisasi awal agar aman saat pemanggilan close()

        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username = ?, email = ?, password = ?, picture = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssss", $username, $email_baru, $password_hashed, $picture, $email_lama);
        } else {
            $sql = "UPDATE users SET username = ?, email = ?, picture = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssss", $username, $email_baru, $picture, $email_lama);
        }

        // PERBAIKAN 2: Pastikan $stmt berhasil di-prepare sebelum dieksekusi
        if ($stmt) {
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }
}