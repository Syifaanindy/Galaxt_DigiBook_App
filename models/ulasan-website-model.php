<?php
class UlasanWebsiteModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    /**
     * Menyimpan ulasan ke tabel web_reviews menggunakan user_id sebagai relasinya
     */
    public function simpanUlasan($rating, $ulasan, $user_id) {
        $sql = "INSERT INTO web_reviews (rating, comment, user_id) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        // i = int (rating), s = string (comment), i = int (user_id)
        $stmt->bind_param("isi", $rating, $ulasan, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}