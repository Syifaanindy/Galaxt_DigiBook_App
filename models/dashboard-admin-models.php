<?php
class DashboardAdminModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    // 1. Total Pendapatan
    public function getTotalPenjualan() {
        $query = "SELECT SUM(total_price) as total FROM `transaction`"; 
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        // Gaya MySQLi untuk mengambil satu data tunggal
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ? $row['total'] : 0;
    }

    // 2. Total Order
    public function getTotalOrder() {
        $query = "SELECT COUNT(*) as total FROM `transaction`"; 
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        // Gaya MySQLi
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ? $row['total'] : 0;
    }

    // 3. Rekap jumlah buku berdasarkan kategori
    public function getBukuPerKategori() {
        $query = "SELECT c.category_name as kategori, COUNT(b.id) as jumlah 
                  FROM `category` c 
                  LEFT JOIN `books` b ON c.id = b.category_id 
                  GROUP BY c.id, c.category_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        // Gaya MySQLi untuk mengambil banyak baris data (Array)
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }

    // 4. Penjualan bulanan
    public function getPenjualanBulanan() {
        $query = "SELECT MONTHNAME(transaction_date) as bulan, SUM(total_price) as total 
                  FROM `transaction` 
                  GROUP BY MONTH(transaction_date), MONTHNAME(transaction_date)
                  ORDER BY MONTH(transaction_date) ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        // Gaya MySQLi
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    }
}