<?php
// Folder: koleksi-controllers
// File: KoleksiController.php

// Menghubungkan secara dinamis ke folder model kustom Anda
require_once __DIR__ . '/../buku-user-model/BukuUserModel.php';

class KoleksiController {
    private $bookModel;

    public function __construct($dbConnection) {
        $this->bookModel = new BukuUserModel($dbConnection);
    }

    public function index() {
        // Menangkap parameter filter dari URL menggunakan GET
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : 'all'; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20; 

        // Eksekusi penarikan data dari database melalui model
        $result = $this->bookModel->getBuku($search, $category, $page, $limit);

        // Ekstrak data hasil query untuk dilempar ke View
        $books = $result['data'];
        $totalBooks = $result['total'];
        $totalPages = $result['total_pages'];

        // Memanggil file view HTML/PHP koleksi Anda
        require __DIR__ . '/../views/user/koleksi.php';
    }
}