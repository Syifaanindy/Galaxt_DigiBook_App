<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once '../../config/url-helper.php';
require_once '../../models/kategori-model.php';

// --- LOGIKA PAGINATION (Pastikan bagian ini aman dan utuh) ---
$limit = 5; // <-- Variabel ini yang tadinya hilang, sekarang sudah kita adakan lagi
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Menghitung total baris kategori untuk menentukan jumlah halaman
$total_kategori = hitungTotalKategori($conn); 

// Mencegah error division by zero jika variabel limit tidak sengaja kosong atau nol
$limit = ($limit < 1) ? 5 : $limit; 
$total_pages = ceil($total_kategori / $limit);

// Mengambil data kategori terbatas (sesuai halaman saat ini)
$kategori = ambilSemuaKategoriLengkapPaging($conn, $limit, $offset);

// Ambil pesan dari session untuk pemicu SweetAlert2
$flashSuccess = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$flashError = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// Hapus session setelah diambil agar tidak duplikat saat di-refresh
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Kategori Buku</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/admin/panel.css">
  <link rel="stylesheet" href="../../assets/css/admin/sidebar.css">
  <link rel="stylesheet" href="../../assets/css/admin/pagination.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <h2>Kategori Buku</h2>
            <p>Tambah, edit, dan hapus kategori buku.</p>
        </header>

        <section class="panel table-wrap">
            <div class="actions d-flex justify-content-between mb-3">
                <h3 class="m-0">Data Kategori</h3>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createKategoriModal">
                    Tambah Data
                </button>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kategoriTableBody">
                    <?php if (!empty($kategori)): ?>
                        <?php foreach ($kategori as $item): ?>
                            <tr>
                                <td>KT-<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?= htmlspecialchars($item['category_name']); ?></td>
                                <td><?= $item['total_buku']; ?></td>
                                <td>
                                    <div class="actions">
                                        <button class="btn btn-soft btn-edit" type="button"
                                                data-bs-toggle="modal" data-bs-target="#editKategoriModal"
                                                data-id="<?= $item['id']; ?>"
                                                data-name="<?= htmlspecialchars($item['category_name'], ENT_QUOTES); ?>">
                                            Edit
                                        </button>
                                        <a href="<?= base_url('controllers/kategori-controller.php?action=delete&id=' . $item['id']); ?>"
                                           class="btn btn-danger btn-delete">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Data kategori belum ada</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </section>
    </main>
</div>

<div class="modal fade" id="createKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('controllers/kategori-controller.php?action=create'); ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editKategoriModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= base_url('controllers/kategori-controller.php?action=update'); ?>">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="category_name" id="edit_category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/script/admin/shared-layout.js"></script>

<script>
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_category_name').value = this.dataset.name;
        });
    });

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const targetUrl = this.href;
            
            Swal.fire({
                icon: 'warning',
                title: 'Hapus kategori?',
                text: 'Kategori yang dihapus berisiko mengganggu relasi data buku.',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }
            });
        });
    });
</script>

<?php if ($flashSuccess): ?>
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Berhasil',
        text: '<?= addslashes($flashSuccess); ?>',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
</script>
<?php endif; ?>

<?php if ($flashError): ?>
<script>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Gagal Proses!',
        text: '<?= addslashes($flashError); ?>',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
</script>
<?php endif; ?>
</body>
</html>