<?php
class UlasanWebsiteModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    /**
     * Menyimpan ulasan ke tabel web_reviews sesuai struktur kolom di phpMyAdmin
     */
    public function simpanUlasan($nama, $email, $rating, $ulasan) {
        // Query disesuaikan 100% dengan kolom database: nama, email, rating, comment
        $sql = "INSERT INTO web_reviews (nama, email, rating, comment) VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        // s = string (nama), s = string (email), i = integer (rating), s = string (ulasan)
        $stmt->bind_param("ssis", $nama, $email, $rating, $ulasan);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}