<?php
// Folder: buku-user-model
// File: BukuUserModel.php

class BukuUserModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Mengambil data buku berdasarkan filter kata kunci dan kategori
     */
    public function getBuku($search = '', $category = 'all', $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Query dasar
        $query = "SELECT * FROM buku WHERE 1=1";
        $params = [];

        // 1. Filter Pencarian Teks (Judul atau Penulis)
        if (!empty($search)) {
            $query .= " AND (judul LIKE ? OR penulis LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // 2. Filter Kategori Fleksibel
        // Menggunakan LOWER agar value 'sastra' dari HTML cocok dengan 'Sastra Indonesia' atau 'sastra' di DB Admin
        if (!empty($category) && $category !== 'all') {
            $query .= " AND (LOWER(kategori) LIKE ? OR LOWER(kategori) = ?)";
            $params[] = "%" . strtolower($category) . "%";
            $params[] = strtolower($category);
        }

        // Hitung total data untuk info teks jumlah buku di UI
        $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalRows = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Tambahkan limit dan offset untuk pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $this->db->prepare($query);
        
        // Bind parameter untuk mencegah SQL Injection dan ketidakcocokan tipe data PDO
        foreach ($params as $key => $val) {
            $stmt->bindValue($key + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $books,
            'total' => $totalRows,
            'current_page' => $page,
            'total_pages' => ceil($totalRows / $limit)
        ];
    }
}