<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/buku-user-model.php';

$bukuUserModel = new BukuUserModel($conn);

$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori_id = isset($_GET['kategori']) ? trim($_GET['kategori']) : 'all';
$page        = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;

$limit  = 20; 
$offset = ($page - 1) * $limit;

$listKategori = $bukuUserModel->getAllKategori();
$daftarBuku   = $bukuUserModel->getBukuKoleksi($search, $kategori_id, $limit, $offset);
$totalBuku    = $bukuUserModel->getTotalBukuKoleksi($search, $kategori_id);

$totalPage    = ceil($totalBuku / $limit);