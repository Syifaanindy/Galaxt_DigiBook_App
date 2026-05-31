<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once '../../config/url-helper.php';
require_once '../../models/kategori-model.php';

$kategori = ambilSemuaKategoriLengkap($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kategori Buku</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/panel.css">
</head>
<body>

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100; margin-top: 60px;">
    <?php if (isset($_SESSION['success'])): ?>
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div id="liveToast" class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

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
                                                data-name="<?= htmlspecialchars($item['category_name']); ?>">
                                            Edit
                                        </button>
                                        <a href="<?= base_url('controllers/kategori-controller.php?action=delete&id=' . $item['id']); ?>"
                                           class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
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
<script src="../../assets/script/admin/shared-layout.js"></script>
<script>
    setActiveMenu(); // Memastikan sidebar aktif menyala

    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_category_name').value = this.dataset.name;
        });
    });

    // Otomatis menghilangkan notifikasi melayang setelah 4 detik agar bersih kembali
    setTimeout(() => {
        const toastElement = document.getElementById('liveToast');
        if (toastElement) {
            const toast = new bootstrap.Toast(toastElement);
            toast.hide();
        }
    }, 4000);
</script>
</body>
</html>