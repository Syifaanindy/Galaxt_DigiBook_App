<?php
session_start();
require_once __DIR__ . '/../../config/auth-helper.php';
require_once __DIR__ . '/../../config/url-helper.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/user-model.php';

requireRole('admin');

$daftarUser = ambilPengunjung($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - User</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/admin/panel.css">
</head>
<body>
  <div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="main-content">
      <header class="topbar">
        <h2>User</h2>
        <p>Memantau akun pengguna.</p>
      </header>

      <section class="panel table-wrap">
        <div class="actions" style="justify-content:space-between; margin-bottom:14px;">
          <h3 style="margin:0;">Data User</h3>
        </div>
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
            </tr>
          </thead>
            <tbody>
              <?php foreach ($daftarUser as $user): ?>
                <tr>
                  <td><?= htmlspecialchars($user['username']) ?></td>
                  <td><?= htmlspecialchars($user['email']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px;">
          <p id="userPaginationInfo" style="margin:0;color:var(--text-muted);"></p>
          <nav aria-label="User pagination">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" id="userPrevItem"><button class="page-link" id="userPrevBtn" type="button">Previous</button></li>
              <li class="page-item disabled"><span class="page-link" id="userPageLabel">1</span></li>
              <li class="page-item" id="userNextItem"><button class="page-link" id="userNextBtn" type="button">Next</button></li>
            </ul>
          </nav>
        </div>
      </section>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/script/admin/shared-layout.js"></script>
  <script>
    (function () {
      const pageSize = 5;
      let currentPage = 1;
      const body = document.getElementById("userTableBody");
      const info = document.getElementById("userPaginationInfo");
      const label = document.getElementById("userPageLabel");
      const prevBtn = document.getElementById("userPrevBtn");
      const nextBtn = document.getElementById("userNextBtn");
      const prevItem = document.getElementById("userPrevItem");
      const nextItem = document.getElementById("userNextItem");

      function rows() {
        return Array.from(body.querySelectorAll("tr"));
      }

      function pageCount() {
        return Math.max(1, Math.ceil(rows().length / pageSize));
      }

      function render() {
        const allRows = rows();
        const totalPages = pageCount();
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;

        allRows.forEach(function (row, index) {
          row.style.display = index >= start && index < end ? "" : "none";
        });

        info.textContent = "Halaman " + currentPage + " dari " + totalPages + " (Total " + allRows.length + " data)";
        label.textContent = currentPage + " / " + totalPages;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;
        prevItem.classList.toggle("disabled", currentPage === 1);
        nextItem.classList.toggle("disabled", currentPage === totalPages);
      }

      body.addEventListener("click", function (event) {
        const deleteBtn = event.target.closest(".btn-delete");
        if (!deleteBtn) return;
        const row = deleteBtn.closest("tr");
        if (!row) return;
        row.remove();
        render();
      });

      prevBtn.addEventListener("click", function () {
        if (currentPage > 1) {
          currentPage -= 1;
          render();
        }
      });

      nextBtn.addEventListener("click", function () {
        if (currentPage < pageCount()) {
          currentPage += 1;
          render();
        }
      });

      render();
    })();
  </script>
</body>
</html>
