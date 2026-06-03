<?php
class ProfilModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    public function getProfilByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, username, email, picture FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfil($email_lama, $username, $email_baru, $password, $picture) {
        if (!empty($password)) {
            // Jika ganti password
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username = ?, email = ?, password = ?, picture = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssss", $username, $email_baru, $password_hashed, $picture, $email_lama);
        } else {
            // Jika password dikosongkan
            $sql = "UPDATE users SET username = ?, email = ?, picture = ? WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssss", $username, $email_baru, $picture, $email_lama);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}