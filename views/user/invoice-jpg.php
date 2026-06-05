<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($transaksi['transaction_code']); ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        #invoice-wrapper {
            max-width: 680px;
            margin: 0 auto;
        }
        #invoice-area {
            background: #ffffff;
            padding: 40px;
            border: 1px solid #eef0f2;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-failed {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>
<body class="bg-light py-5">

    <div id="invoice-wrapper">
        <div class="text-center mb-4">
            <button id="btn-download" class="btn btn-dark btn-sm px-4 py-2" disabled>
                <i class="fa-solid fa-spinner fa-spin me-2"></i> Memproses Gambar JPG...
            </button>
        </div>

        <div id="invoice-area">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h3 class="fw-extrabold text-primary mb-1" style="letter-spacing: -0.5px;">GALAXY DIGIBOOK</h3>
                    <p class="text-muted small mb-0">No. Invoice: <span class="fw-bold text-dark"><?= htmlspecialchars($transaksi['transaction_code']); ?></span></p> 
                </div>
                <div class="col-6 text-end">
                    <h5 class="fw-bold text-secondary mb-1">INVOICE PEMBELIAN</h5>
                    <p class="text-muted small mb-0"><?= date('d M Y • H:i', strtotime($transaksi['transaction_date'])); ?> WIB</p>
                </div>
            </div>
            
            <hr class="text-muted my-4">

            <table class="table table-borderless my-4">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th class="py-3 px-3 text-secondary small fw-bold">ITEM BUKU</th>
                        <th class="py-3 px-3 text-end text-secondary small fw-bold">TOTAL HARGA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3 px-3 fw-semibold text-dark">
                            <i class="fa-solid fa-book text-muted me-2"></i><?= htmlspecialchars($transaksi['title']); ?>
                        </td>
                        <td class="py-3 px-3 text-end fw-bold text-dark">
                            Rp <?= number_format($transaksi['total_price'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="text-muted my-4">

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <span class="small text-secondary d-block">Status Pembayaran</span>
                    <?php if (strtolower($transaksi['status']) === 'success'): ?>
                        <span class="badge badge-success px-3 py-2 rounded-pill fw-bold small"><i class="fa-solid fa-circle-check me-1"></i> BERHASIL</span>
                    <?php else: ?>
                        <span class="badge badge-failed px-3 py-2 rounded-pill fw-bold small"><i class="fa-solid fa-circle-xmark me-1"></i> GAGAL</span>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <span class="small text-secondary d-block">Total Bayar</span>
                    <h4 class="fw-bold text-success mb-0">Rp <?= number_format($transaksi['total_price'], 0, ',', '.'); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const element = document.getElementById('invoice-area');
            const filename = 'Invoice-<?= htmlspecialchars($transaksi['transaction_code']); ?>.jpg';

            setTimeout(() => {
                html2canvas(element, {
                    scale: 2, 
                    useCORS: true 
                }).then(canvas => {
                    const imgData = canvas.toDataURL('image/jpeg', 0.9);
                    
                    const link = document.createElement('a');
                    link.download = filename;
                    link.href = imgData;
                    link.click();
                    
                    const btn = document.getElementById('btn-download');
                    btn.innerHTML = '<i class="fa-solid fa-check me-2"></i> Unduh Selesai! Anda bisa menutup tab ini.';
                    btn.className = "btn btn-success btn-sm px-4 py-2";
                });
            }, 1000);
        });
    </script>
</body>
</html>