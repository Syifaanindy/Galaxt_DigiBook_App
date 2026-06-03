<?php
require_once dirname(__DIR__, 2) . '/controllers/ulasan-controller.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Website - Galaxy Digi Book</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/user/layout-shared.css">
</head>
<body>
    <div id="site-navbar"></div>

    <main class="container py-5">
        <div class="hero-box mb-4">
            <h1>Ulasan Website</h1>
            <p>Bantu kami meningkatkan kualitas Galaxy Digi Book dengan memberikan ulasan, kritik, atau saran terbaik Anda.</p>
        </div>

        <div class="card p-4 shadow-sm border-0" style="border-radius: 16px;">
            <h3 class="fw-bold mb-3" style="color: #2b256d;">Kirim Ulasan Website</h3>
            
            <?php if(!empty($pesanSukses)): ?>
                <div class="alert alert-success"><?= $pesanSukses ?></div>
            <?php endif; ?>
            <?php if(!empty($pesanGagal)): ?>
                <div class="alert alert-danger"><?= $pesanGagal ?></div>
            <?php endif; ?>

            <form method="POST" action="kontak.php">
                <div class="mb-3">
                    <label for="ratingSelect" class="form-label fw-semibold">Rating</label>
                    <select id="ratingSelect" name="rating" class="form-select" required>
                        <option value="" disabled selected>Pilih rating</option>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                        <option value="2">⭐⭐ (2 - Kurang Puas)</option>
                        <option value="1">⭐ (1 - Buruk)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="commentTextArea" class="form-label fw-semibold">Ulasan</label>
                    <textarea id="commentTextArea" name="comment" class="form-control" rows="5" placeholder="Tulis ulasan website kamu..." required></textarea>
                </div>

                <button type="submit" class="btn text-white px-4 py-2" style="background: #2b256d; border-radius: 10px; font-weight: 600;">Kirim Ulasan</button>
            </form>
        </div>
    </main>

    <div id="site-footer"></div>
</body>
</html>