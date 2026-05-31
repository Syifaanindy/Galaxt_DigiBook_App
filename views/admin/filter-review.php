<?php

  $conn = new mysqli("localhost", "root", "", "galaxy_digibook");

  $view = $_GET['view'] ?? 'book';

  $book_rating = $_GET['book_rating'] ?? '';
  $book_id = $_GET['book_id'] ?? '';

  $web_rating = $_GET['web_rating'] ?? '';

  
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  if ($page < 1) { $page = 1; }

  $limit = 5;
  $offset = ($page - 1) * $limit;

  $books = $conn->query("
      SELECT *
      FROM books
      ORDER BY title ASC
  ");

  $bookWhere = "WHERE 1=1";

  if ($book_rating != '') {
      $bookWhere .= " AND br.rating = '$book_rating'";
  }

  if ($book_id != '') {
      $bookWhere .= " AND br.book_id = '$book_id'";
  }

  $bookQuery = "
      SELECT 
          br.*, 
          b.title
      FROM book_reviews br
      JOIN books b ON br.book_id = b.id
      $bookWhere
      ORDER BY br.id DESC
      LIMIT $limit OFFSET $offset
  ";

  $bookReviews = $conn->query($bookQuery);

  $bookTotalQuery = "
      SELECT 
          COUNT(br.id) AS total,
          ROUND(AVG(br.rating), 1) AS avg_rating
      FROM book_reviews br
      JOIN books b ON br.book_id = b.id
      $bookWhere
  ";

  $bookSummary = $conn
      ->query($bookTotalQuery)
      ->fetch_assoc();

  $bookTotal = $bookSummary['total'];
  $bookAvg = $bookSummary['avg_rating'] ?? 0;
  
  
  $book_total_pages = ceil($bookTotal / $limit);

  $webWhere = "WHERE 1=1";

  if ($web_rating != '') {
      $webWhere .= " AND rating = '$web_rating'";
  }

  $webQuery = "
      SELECT *
      FROM web_reviews
      $webWhere
      ORDER BY id DESC
      LIMIT $limit OFFSET $offset
  ";

  $webReviews = $conn->query($webQuery);

  $webTotalQuery = "
      SELECT 
          COUNT(id) AS total,
          ROUND(AVG(rating), 1) AS avg_rating
      FROM web_reviews
      $webWhere
  ";

  $webSummary = $conn
      ->query($webTotalQuery)
      ->fetch_assoc();

  $webTotal = $webSummary['total'];
  $webAvg = $webSummary['avg_rating'] ?? 0;

  
  $web_total_pages = ceil($webTotal / $limit);

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews Dashboard</title>

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
    <h2>Reviews Dashboard</h2>
    <p>Review Buku & Website</p>
</header>

<div class="switch-buttons">
    <a href="?view=book" class="<?= $view == 'book' ? 'active' : '' ?>">Review Buku</a>
    <a href="?view=web" class="<?= $view == 'web' ? 'active' : '' ?>">Review Website</a>
</div>

<?php if($view == 'book'): ?>
<div class="summary-box">
    <div class="summary-card">
        <h4>Total Ulasan Buku</h4>
        <h2><?= $bookTotal ?></h2>
    </div>
    <div class="summary-card">
        <h4>Rata-Rata Rating</h4>
        <h2><?= $bookAvg ?> ⭐</h2>
    </div>
</div>

<section class="panel">
<div class="panel-header">
    <h3>Review Buku</h3>
    <form method="GET" class="filters">
        <input type="hidden" name="view" value="book">
        <select name="book_id" onchange="this.form.submit()">
            <option value="">Semua Buku</option>
            <?php 
            $books->data_seek(0); 
            while($book = $books->fetch_assoc()): 
            ?>
            <option value="<?= $book['id'] ?>" <?= $book_id == $book['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($book['title']) ?>
            </option>
            <?php endwhile; ?>
        </select>

        <select name="book_rating" onchange="this.form.submit()">
            <option value="">Semua Rating</option>
            <?php for($i=5; $i>=1; $i--): ?>
                <option value="<?= $i ?>" <?= $book_rating == $i ? 'selected' : '' ?>><?= $i ?> ⭐</option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="table-wrap">
  <table class="table">
  <thead>
  <tr>
      <th>ID</th>
      <th>Buku</th>
      <th>User</th>
      <th>Rating</th>
      <th>Comment</th>
  </tr>
  </thead>
  <tbody>
  <?php if ($bookReviews->num_rows > 0): ?>
      <?php while($row = $bookReviews->fetch_assoc()): ?>
      <tr>
          <td>BR-<?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td>User <?= htmlspecialchars($row['user_id']) ?></td>
          <td><?= str_repeat("⭐", $row['rating']) ?></td>
          <td><?= htmlspecialchars($row['comment']) ?></td>
      </tr>
      <?php endwhile; ?>
  <?php else: ?>
      <tr>
          <td colspan="5" class="text-center">Belum ada review buku.</td>
      </tr>
  <?php endif; ?>
  </tbody>
  </table>
</div>

<?php if ($book_total_pages > 1): ?>
<nav class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted small">
        <?= min($offset + $limit, $bookTotal) ?> dari <?= $bookTotal ?> data review
    </div>
    <ul class="pagination pagination-sm m-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?view=book&book_id=<?= $book_id ?>&book_rating=<?= $book_rating ?>&page=<?= $page - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $book_total_pages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="?view=book&book_id=<?= $book_id ?>&book_rating=<?= $book_rating ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $book_total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?view=book&book_id=<?= $book_id ?>&book_rating=<?= $book_rating ?>&page=<?= $page + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

</section>
<?php endif; ?>

<?php if($view == 'web'): ?>
<div class="summary-box">
    <div class="summary-card">
        <h3>Total Review Website</h3>
        <h2><?= $webTotal ?></h2>
    </div>
    <div class="summary-card">
        <h3>Rata-Rata Rating</h3>
        <h2><?= $webAvg ?> ⭐</h2>
    </div>
</div>

<section class="panel">
<div class="panel-header">
    <h3>Review Website</h3>
    <form method="GET" class="filters">
        <input type="hidden" name="view" value="web">
        <select name="web_rating" onchange="this.form.submit()">
            <option value="">Semua Rating</option>
            <?php for($i=5; $i>=1; $i--): ?>
                <option value="<?= $i ?>" <?= $web_rating == $i ? 'selected' : '' ?>><?= $i ?> ⭐</option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
          <th>ID</th>
          <th>User</th>
          <th>Rating</th>
          <th>Comment</th>
      </tr>
    </thead>
  <tbody>
  <?php if ($webReviews->num_rows > 0): ?>
      <?php while($row = $webReviews->fetch_assoc()): ?>
      <tr>
          <td>WEB-<?= $row['id'] ?></td>
          <td>User <?= htmlspecialchars($row['user_id']) ?></td>
          <td><?= str_repeat("⭐", $row['rating']) ?></td>
          <td><?= htmlspecialchars($row['comment']) ?></td>
      </tr>
      <?php endwhile; ?>
  <?php else: ?>
      <tr>
          <td colspan="4" class="text-center">Belum ada review website.</td>
      </tr>
  <?php endif; ?>
  </tbody>
  </table>
</div>

<?php if ($web_total_pages > 1): ?>
<nav class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted small">
        <?= min($offset + $limit, $webTotal) ?> dari <?= $webTotal ?> data review
    </div>
    <ul class="pagination pagination-sm m-0">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?view=web&web_rating=<?= $web_rating ?>&page=<?= $page - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $web_total_pages; $i++): ?>
            <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                <a class="page-link" href="?view=web&web_rating=<?= $web_rating ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $web_total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?view=web&web_rating=<?= $web_rating ?>&page=<?= $page + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

</section>
<?php endif; ?>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>