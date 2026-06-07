<?php


class KoleksiController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function handleKoleksi() {
        // Proteksi hak akses user halaman katalog
        requireRole('user');

        // Mengambil dan membersihkan parameter input URL (GET)
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $limit = 20;

        // Ambil daftar kategori dari database
        require_once dirname(__DIR__) . '/models/kategori-model.php';
        $daftarKategori = ambilSemuaKategoriLengkap($this->conn);

        // Memanggil fungsi dari models/buku-user-model.php
        $koleksiData = ambilKoleksiDinamis($this->conn, $search, $category, $page, $limit);

        // Parsing data ke View
        return [
            'books'          => $koleksiData['books'],
            'totalBooks'     => $koleksiData['total'],
            'totalPages'     => $koleksiData['total_pages'],
            'page'           => $page,
            'search'         => $search,
            'category'       => $category,
            'daftarKategori' => $daftarKategori
        ];
    }
}