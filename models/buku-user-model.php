<?php
class BukuUserModel {
    private $db;

    public function __construct($databaseConnection) {
        $this->db = $databaseConnection;
    }

    /**
     * Mengambil data kategori untuk dropdown filter
     */
    public function getAllKategori() {
        $sql = "SELECT * FROM category";
        return $this->db->query($sql);
    }

    /**
     * Mengambil data dari tabel 'books' menggunakan nama kolom asli database
     * dialiaskan (AS) agar kompatibel dengan penamaan di file tampilan Anda
     */
    public function getBukuKoleksi($search, $kategori_id, $limit, $offset) {
        $sql = "SELECT 
                    b.id,
                    b.title AS judul,
                    b.author AS penulis,
                    b.price AS harga,
                    b.cover_image AS cover,
                    b.category_id,
                    k.category_name AS nama_kategori 
                FROM books b 
                LEFT JOIN category k ON b.category_id = k.id 
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($search)) {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
            $searchParam = "%" . $search . "%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }

        if (!empty($kategori_id) && $kategori_id !== 'all') {
            $sql .= " AND b.category_id = ?";
            $params[] = $kategori_id;
            $types .= "i";
        }

        $sql .= " ORDER BY b.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Menghitung total buku yang sesuai untuk keperluan paginasi
     */
    public function getTotalBukuKoleksi($search, $kategori_id) {
        $sql = "SELECT COUNT(*) as total FROM books b WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($search)) {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
            $searchParam = "%" . $search . "%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }

        if (!empty($kategori_id) && $kategori_id !== 'all') {
            $sql .= " AND b.category_id = ?";
            $params[] = $kategori_id;
            $types .= "i";
        }

        $stmt = $this->db->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['total'] ?? 0;
    }
}