<?php
class DashboardAdminModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    public function getTotalPenjualan() {
        $query = "SELECT SUM(total_price) as total FROM `transaction`"; 
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        return $result ? $result : 0;
    }

    public function getTotalOrder() {
        $query = "SELECT COUNT(*) as total FROM `transaction`"; 
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getBukuPerKategori() {
        $query = "SELECT c.category_name as kategori, COUNT(b.id) as jumlah 
                  FROM `category` c 
                  LEFT JOIN `books` b ON c.id = b.category_id 
                  GROUP BY c.id, c.category_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPenjualanBulanan() {
        $query = "SELECT MONTHNAME(transaction_date) as bulan, SUM(total_price) as total 
                  FROM `transaction` 
                  GROUP BY MONTH(transaction_date), MONTHNAME(transaction_date)
                  ORDER BY MONTH(transaction_date) ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}