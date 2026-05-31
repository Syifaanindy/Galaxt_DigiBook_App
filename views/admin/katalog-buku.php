<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Load semua helper, database, dan model buku
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../models/buku-model.php';

// Proteksi halaman, pastikan hanya admin yang bisa masuk
requireRole('admin');

// Ambil data dinamis awal (Kategori)
$list_kategori = ambilSemuaKategori($conn);
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// --- LOGIKA PAGINATION ---
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

$total_buku = hitungTotalBuku($conn); 
$total_pages = ceil($total_buku / $limit);

$list_buku = ambilSemuaBukuPaging($conn, $limit, $offset); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Katalog Buku</title>
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
        <h2>Katalog Buku</h2>
        <p>Tambah, edit, dan hapus data buku.</p>
      </header>

      <section class="panel">
        <div class="actions" style="justify-content:space-between; margin-bottom:14px;">
          <h3 style="margin:0;">List Buku Katalog</h3>
          <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createKatalogModal">Tambah Data</button>
        </div>
        
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="katalogTableBody">
              <?php if (empty($list_buku)): ?>
                  <tr><td colspan="6" style="text-align:center;">Belum ada data buku.</td></tr>
              <?php else: ?>
                  <?php foreach ($list_buku as $buku): ?>
                  <tr>
                      <td>BK-<?= str_pad($buku['id'], 3, '0', STR_PAD_LEFT); ?></td>
                      <td><?= htmlspecialchars($buku['title']); ?></td>
                      <td><?= htmlspecialchars($buku['author']); ?></td>
                      <td><?= htmlspecialchars($buku['category_name'] ?? 'Tanpa Kategori'); ?></td>
                      <td>Rp <?= number_format($buku['price'], 0, ',', '.'); ?></td>
                      <td>
                          <div class="actions" style="gap: 5px;">
                              <a href="<?= base_url('views/admin/detail-buku.php?id=' . $buku['id']); ?>" 
                                 class="btn btn-info btn-sm text-white" title="Detail Buku">
                                 <i class="fa-solid fa-eye"></i>
                              </a>

                              <button class="btn btn-warning btn-sm text-white btn-edit" type="button" 
                                      data-bs-toggle="modal" 
                                      data-bs-target="#editKatalogModal"
                                      data-id="<?= $buku['id']; ?>"
                                      data-title="<?= htmlspecialchars($buku['title'], ENT_QUOTES); ?>"
                                      data-author="<?= htmlspecialchars($buku['author'], ENT_QUOTES); ?>"
                                      data-publisher="<?= htmlspecialchars($buku['publisher'] ?? '', ENT_QUOTES); ?>"
                                      data-category="<?= $buku['category_id']; ?>"
                                      data-price="<?= $buku['price']; ?>"
                                      data-synopsis="<?= htmlspecialchars($buku['synopsis'] ?? '', ENT_QUOTES); ?>"
                                      title="Edit Buku">
                                 <i class="fa-solid fa-pen-to-square"></i>
                              </button>

                              <a href="<?= base_url('controllers/buku-controller.php?action=delete&id=' . $buku['id']); ?>" 
                                 class="btn btn-danger btn-sm btn-delete" title="Hapus Buku">
                                 <i class="fa-solid fa-trash"></i>
                              </a>
                          </div>
                      </td>
                  </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php 
          $total_data = $total_buku; 
          $target_url = 'katalog-buku.php'; 
          include 'partials/pagination.php'; 
        ?>

      </section>
    </main>
  </div>

  <div class="modal fade" id="createKatalogModal" tabindex="-1" aria-labelledby="createKatalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="<?= base_url('controllers/buku-controller.php?action=create'); ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="createKatalogModalLabel">Tambah Buku</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="field"><label class="form-label">Judul Buku</label><input type="text" name="title" class="form-control" required></div>
                <div class="field"><label class="form-label">Penulis</label><input type="text" name="author" class="form-control" required></div>
                <div class="field"><label class="form-label">Penerbit</label><input type="text" name="publisher" class="form-control" required></div>
                <div class="field">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($list_kategori as $kat): ?>
                            <option value="<?= $kat['id']; ?>"><?= htmlspecialchars($kat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="field"><label class="form-label">Harga</label><input type="number" name="price" class="form-control" required></div>
                <div class="field" style="grid-column: span 2;"><label class="form-label">Sinopsis</label><textarea name="synopsis" class="form-control" rows="3"></textarea></div>
                <div class="field"><label class="form-label">File Buku (PDF)</label><input type="file" name="file_buku" id="create-file-buku" accept=".pdf" class="form-control"></div>
                <div class="field"><label class="form-label">Cover Buku (Image)</label><input type="file" name="cover_buku" id="create-cover-buku" accept=".png, .jpg, .jpeg" class="form-control"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editKatalogModal" tabindex="-1" aria-labelledby="editKatalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="<?= base_url('controllers/buku-controller.php?action=update'); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-header">
              <h5 class="modal-title" id="editKatalogModalLabel">Edit Buku</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="field"><label class="form-label">Judul Buku</label><input type="text" name="title" id="edit-title" class="form-control" required></div>
                <div class="field"><label class="form-label">Penulis</label><input type="text" name="author" id="edit-author" class="form-control" required></div>
                <div class="field"><label class="form-label">Penerbit</label><input type="text" name="publisher" id="edit-publisher" class="form-control" required></div>
                <div class="field">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" id="edit-category" class="form-select" required>
                        <?php foreach ($list_kategori as $kat): ?>
                            <option value="<?= $kat['id']; ?>"><?= htmlspecialchars($kat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="field"><label class="form-label">Harga</label><input type="number" name="price" id="edit-price" class="form-control" required></div>
                <div class="field" style="grid-column: span 2;"><label class="form-label">Sinopsis</label><textarea name="synopsis" id="edit-synopsis" class="form-control" rows="3"></textarea></div>
                <div class="field"><label class="form-label">File Buku baru (Kosongkan jika tidak diganti)</label><input type="file" name="file_buku" id="edit-file-buku" accept=".pdf" class="form-control"></div>
                <div class="field"><label class="form-label">Cover Buku baru (Kosongkan jika tidak diganti)</label><input type="file" name="cover_buku" id="edit-cover-buku" accept=".png, .jpg, .jpeg" class="form-control"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Tutup</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Script Hamburger Menu Responsif
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                document.getElementById('admin-sidebar').classList.toggle('show');
            });
        }

        <?php if ($flashSuccess || $flashError): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: <?= json_encode($flashError ? 'error' : 'success'); ?>,
                title: <?= json_encode($flashError ? 'Gagal Upload!' : 'Berhasil'); ?>,
                text: <?= json_encode($flashError ?: $flashSuccess); ?>,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        <?php endif; ?>

        const fileBukuInputs = document.querySelectorAll('#create-file-buku, #edit-file-buku');
        const coverBukuInputs = document.querySelectorAll('#create-cover-buku, #edit-cover-buku');

        fileBukuInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (ext !== 'pdf') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format File Salah!',
                            text: 'File Buku dilarang keras selain format PDF.',
                            confirmButtonColor: '#dc3545'
                        });
                        this.value = ''; 
                    }
                }
            });
        });

        coverBukuInputs.forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const ext = file.name.split('.').pop().toLowerCase();
                    const allowedExt = ['png', 'jpg', 'jpeg'];
                    if (!allowedExt.includes(ext)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Cover Salah!',
                            text: 'Cover Buku wajib berformat Gambar (PNG, JPG, JPEG).',
                            confirmButtonColor: '#dc3545'
                        });
                        this.value = ''; 
                    }
                }
            });
        });

        const tombolEdit = document.querySelectorAll('.btn-edit');
        tombolEdit.forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('edit-id').value = this.getAttribute('data-id');
                document.getElementById('edit-title').value = this.getAttribute('data-title');
                document.getElementById('edit-publisher').value = this.getAttribute('data-publisher');
                document.getElementById('edit-author').value = this.getAttribute('data-author');
                document.getElementById('edit-category').value = this.getAttribute('data-category');
                document.getElementById('edit-price').value = this.getAttribute('data-price');
                document.getElementById('edit-synopsis').value = this.getAttribute('data-synopsis');
            });
        });

        const tombolHapus = document.querySelectorAll('.btn-delete');
        tombolHapus.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus buku?',
                    text: 'Data buku yang dihapus tidak bisa dikembalikan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = this.href;
                    }
                });
            });
        });
    });
  </script>
</body>
</html>