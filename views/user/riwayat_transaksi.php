<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../models/invoice.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: " . base_url('views/auth/login.php'));
    exit;
}

$user_id = $_SESSION['user_id'];

$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// PERBAIKAN: Menggunakan nama fungsi hitung yang baru sesuai di file invoice.php
$total_data = hitungTotalTransaksiUser($conn, $user_id);
$total_pages = ceil($total_data / $limit);

// Mengambil data per halaman (Fungsinya sudah kita buatkan di Langkah 1)
$riwayat_transaksi = ambilTransaksiPerHalaman($conn, $user_id, $limit, $offset);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - BookStore</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/d.css">
    <link rel="stylesheet" href="../../assets/css/admin/pagination.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
    <link rel="stylesheet" href="../../assets/css/user/riwayat-transaksi.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        #invoice-capture-area { background: #ffffff; padding: 30px; border-radius: 8px; }
        .badge-success-invoice { background-color: #d1e7dd; color: #0f5132; }
        .badge-failed-invoice { background-color: #f8d7da; color: #842029; }
        .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; color: #fff; }
        .pagination .page-link { color: #333; }
    </style>
</head>
<body data-page="profile">
   <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="history-page-container py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb custom-breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Riwayat Transaksi</li>
                </ol>
            </nav>

            <div class="mb-4">
                <span class="section-subtitle">Aktivitas Pembelian</span>
                <h1 class="history-title fw-extrabold mb-1">Riwayat Transaksi Anda</h1>
                <p class="text-muted mb-0">Lacak semua transaksi, status pembayaran, dan akses ulang invoice kapan saja.</p>
            </div>

            <section class="history-card p-4 p-md-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div class="btn-group history-filter" role="group" aria-label="Filter status transaksi">
                        <button type="button" class="btn btn-outline-cart active" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-outline-cart" data-filter="success">Berhasil</button>
                        <button type="button" class="btn btn-outline-cart" data-filter="failed">Gagal</button>
                    </div>
                    <div class="w-100 w-md-auto" style="max-width: 280px;">
                        <input type="text" id="invoiceSearch" class="form-control checkout-form-control" placeholder="Cari invoice...">
                    </div>
                </div>

                <div id="historyList">
                    <?php if (!empty($riwayat_transaksi)): ?>
                        <?php foreach ($riwayat_transaksi as $transaksi): 
                            $status_class = strtolower($transaksi['status']); 
                            $date_timestamp = strtotime($transaksi['transaction_date']);
                            $fmt_date = date('d M Y • H:i', $date_timestamp) . ' WIB';
                        ?>
                            <article class="transaction-row" data-status="<?php echo $status_class; ?>" data-invoice="<?php echo htmlspecialchars($transaksi['transaction_code']); ?>">
                                <div class="transaction-head">
                                    <span class="invoice-id"><?php echo htmlspecialchars($transaksi['transaction_code']); ?></span>
                                    <?php if ($status_class === 'success'): ?>
                                        <span class="status-badge status-success"><i class="fa-solid fa-circle-check me-1"></i> Berhasil</span>
                                    <?php else: ?>
                                        <span class="status-badge status-failed"><i class="fa-solid fa-circle-xmark me-1"></i> Gagal</span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="book-chip"><i class="fa-solid fa-book"></i> <?php echo htmlspecialchars($transaksi['title']); ?></span>
                                    <small class="text-muted"><?php echo $fmt_date; ?></small>
                                </div>
                                <div class="transaction-foot">
                                    <strong>Total: Rp <?php echo number_format($transaksi['total_price'], 0, ',', '.'); ?></strong>
                                    <div class="d-flex gap-2">
                                        <?php if ($status_class === 'success'): ?>
                                            <a href="<?php echo base_url('views/user/detail.php?id=' . $transaksi['book_id']); ?>" class="btn btn-sm btn-outline-cart">Lihat Buku</a>
                                            <button type="button" class="btn btn-sm btn-primary-buy btn-buka-invoice" 
                                                    data-code="<?php echo htmlspecialchars($transaksi['transaction_code']); ?>"
                                                    data-title="<?php echo htmlspecialchars($transaksi['title']); ?>"
                                                    data-date="<?php echo $fmt_date; ?>"
                                                    data-price="Rp <?php echo number_format($transaksi['total_price'], 0, ',', '.'); ?>"
                                                    data-status="<?php echo $status_class; ?>">
                                                Unduh Invoice
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo base_url('views/user/transaction.php?retry_book=' . $transaksi['book_id']); ?>" class="btn btn-sm btn-outline-cart">Coba Lagi</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Navigasi Halaman" class="mt-4">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>"><i class="fa-solid fa-angle-left"></i></a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>"><i class="fa-solid fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Anda belum memiliki riwayat transaksi apa pun.</p>
                            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-primary-buy btn-sm">Mulai Belanja</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="invoiceModalLabel">Pratinjau Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div id="invoice-capture-area">
                        <div class="row align-items-center mb-3">
                            <div class="col-12 mb-2">
                                <h4 class="fw-bold text-primary mb-0" style="letter-spacing: -0.5px;">GALAXY DIGIBOOK</h4>
                                <small class="text-muted">No. Invoice: <span class="fw-bold text-dark" id="pop-code"></span></small>
                            </div>
                            <div class="col-12 mt-2">
                                <h6 class="fw-bold text-secondary mb-0">INVOICE PEMBELIAN</h6>
                                <small class="text-muted" id="pop-date"></small>
                            </div>
                        </div>
                        <hr class="my-3">
                        <table class="table table-borderless my-2 small">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th class="py-2 px-2 text-secondary fw-bold">ITEM BUKU</th>
                                    <th class="py-2 px-2 text-end text-secondary fw-bold">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 px-2 fw-semibold text-dark" id="pop-title"></td>
                                    <td class="py-2 px-2 text-end fw-bold text-dark" id="pop-price-item"></td>
                                </tr>
                            </tbody>
                        </table>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mt-2 small">
                            <div>
                                <span class="text-secondary d-block mb-1">Status Pembayaran</span>
                                <span id="pop-status-badge" class="badge px-3 py-1.5 rounded-pill fw-bold"></span>
                            </div>
                            <div class="text-end">
                                <span class="text-secondary d-block">Total Bayar</span>
                                <h5 class="fw-bold text-success mb-0" id="pop-total-price"></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btn-proses-jpg" class="btn btn-primary btn-sm px-3">
                        <i class="fa-solid fa-download me-1"></i> Unduh Gambar (.JPG)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="site-footer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/script/user/shared-layout.js"></script>
    
    <script>
        (function () {
            // Filter & Search Client-Side (Opsional untuk data di halaman aktif)
            const filterButtons = document.querySelectorAll('.history-filter .btn');
            const searchInput = document.getElementById('invoiceSearch');
            
            function applyFilter() {
                const rows = Array.from(document.querySelectorAll('#historyList .transaction-row'));
                const keyword = searchInput.value.trim().toLowerCase();
                const activeFilterButton = document.querySelector('.history-filter .btn.active');
                const currentFilter = activeFilterButton ? activeFilterButton.dataset.filter : 'all';

                rows.forEach(function (row) {
                    const status = row.dataset.status;
                    const invoice = row.dataset.invoice.toLowerCase();
                    const passStatus = currentFilter === 'all' || status === currentFilter;
                    const passSearch = !keyword || invoice.includes(keyword);
                    row.style.display = passStatus && passSearch ? '' : 'none';
                });
            }

            filterButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    filterButtons.forEach(function (item) { item.classList.remove('active'); });
                    btn.classList.add('active');
                    applyFilter();
                });
            });
            searchInput.addEventListener('input', applyFilter);

            // Modal Popup & Capture System
            const modalEl = document.getElementById('invoiceModal');
            const bootstrapModal = new bootstrap.Modal(modalEl);
            let activeInvoiceCode = '';

            document.querySelectorAll('.btn-buka-invoice').forEach(btn => {
                btn.addEventListener('click', function() {
                    activeInvoiceCode = this.dataset.code;
                    
                    document.getElementById('pop-code').innerText = activeInvoiceCode;
                    document.getElementById('pop-title').innerHTML = `<i class="fa-solid fa-book text-muted me-2"></i>` + this.dataset.title;
                    document.getElementById('pop-date').innerText = this.dataset.date;
                    document.getElementById('pop-price-item').innerText = this.dataset.price;
                    document.getElementById('pop-total-price').innerText = this.dataset.price;

                    const badge = document.getElementById('pop-status-badge');
                    if (this.dataset.status === 'success') {
                        badge.className = "badge badge-success-invoice px-3 py-2 rounded-pill fw-bold";
                        badge.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> BERHASIL`;
                    } else {
                        badge.className = "badge badge-failed-invoice px-3 py-2 rounded-pill fw-bold";
                        badge.innerHTML = `<i class="fa-solid fa-circle-xmark me-1"></i> GAGAL`;
                    }

                    const btnDownload = document.getElementById('btn-proses-jpg');
                    btnDownload.innerHTML = '<i class="fa-solid fa-download me-1"></i> Unduh Gambar (.JPG)';
                    btnDownload.disabled = false;
                    btnDownload.className = "btn btn-primary btn-sm px-3";

                    bootstrapModal.show();
                });
            });

            document.getElementById('btn-proses-jpg').addEventListener('click', function() {
                const btn = this;
                const element = document.getElementById('invoice-capture-area');
                
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Memproses...';

                setTimeout(() => {
                    html2canvas(element, { scale: 2, useCORS: true }).then(canvas => {
                        const imgData = canvas.toDataURL('image/jpeg', 0.9);
                        const link = document.createElement('a');
                        link.download = 'Invoice-' + activeInvoiceCode + '.jpg';
                        link.href = imgData;
                        link.click();
                        
                        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Berhasil Diunduh!';
                        btn.className = "btn btn-success btn-sm px-3";

                        setTimeout(() => { bootstrapModal.hide(); }, 1000);
                    });
                }, 400);
            });
        })();
    </script>
</body>
</html>