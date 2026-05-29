<?php

  $conn = new mysqli("localhost", "root", "", "galaxy_digibook");

  $view = $_GET['view'] ?? 'book';

  $book_rating = $_GET['book_rating'] ?? '';
  $book_id = $_GET['book_id'] ?? '';

  $web_rating = $_GET['web_rating'] ?? '';

  $book_page = $_GET['book_page'] ?? 1;
  $web_page = $_GET['web_page'] ?? 1;

  $limit = 5;

  $book_offset = ($book_page - 1) * $limit;
  $web_offset = ($web_page - 1) * $limit;

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
      LIMIT $limit OFFSET $book_offset
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

  $bookPages = ceil($bookTotal / $limit);

  $webWhere = "WHERE 1=1";

  if ($web_rating != '') {
      $webWhere .= " AND rating = '$web_rating'";
  }

  $webQuery = "
      SELECT *
      FROM web_reviews
      $webWhere
      ORDER BY id DESC
      LIMIT $limit OFFSET $web_offset
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

  $webPages = ceil($webTotal / $limit);

?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta 
    name="viewport" 
    content="width=device-width, initial-scale=1.0"
>

<title>Reviews Dashboard</title>

<link 
    rel="stylesheet" 
    href="../../assets/css/admin/panel.css"
>

<link 
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
>

</head>

<body>

  <div class="admin-layout">

  <div id="admin-sidebar"></div>

<main class="main-content">

<header class="topbar">

    <h2>Reviews Dashboard</h2>

    <p>Review Buku & Website</p>

</header>

<div class="switch-buttons">

    <a 
        href="?view=book"
        class="<?= $view == 'book' ? 'active' : '' ?>"
    >
        Review Buku
    </a>

    <a 
        href="?view=web"
        class="<?= $view == 'web' ? 'active' : '' ?>"
    >
        Review Website
    </a>

</div>

<?php if($view == 'book'): ?>

<div class="summary-box">

    <div class="summary-card">

        <h3>Total Ulasan Buku</h3>

        <h2><?= $bookTotal ?></h2>

    </div>

    <div class="summary-card">

        <h3>Rata-Rata Rating</h3>

        <h2><?= $bookAvg ?> ⭐</h2>

    </div>

</div>

<section class="panel">

<div class="panel-header">

    <h3>Review Buku</h3>

    <form method="GET" class="filters">

        <input 
            type="hidden" 
            name="view" 
            value="book"
        >

        <select 
            name="book_id" 
            onchange="this.form.submit()"
        >

            <option value="">Semua Buku</option>

            <?php while($book = $books->fetch_assoc()): ?>

            <option 
                value="<?= $book['id'] ?>"
                <?= $book_id == $book['id'] ? 'selected' : '' ?>
            >
                <?= $book['title'] ?>
            </option>

            <?php endwhile; ?>

        </select>

        <select 
            name="book_rating" 
            onchange="this.form.submit()"
        >

            <option value="">Semua Rating</option>
            <option value="5">5 ⭐</option>
            <option value="4">4 ⭐</option>
            <option value="3">3 ⭐</option>
            <option value="2">2 ⭐</option>
            <option value="1">1 ⭐</option>

        </select>

    </form>

</div>

<table>

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

<?php while($row = $bookReviews->fetch_assoc()): ?>

<tr>

    <td>BR-<?= $row['id'] ?></td>

    <td><?= $row['title'] ?></td>

    <td>User <?= $row['user_id'] ?></td>

    <td><?= str_repeat("⭐", $row['rating']) ?></td>

    <td><?= $row['comment'] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<div class="pagination">

<?php 
$prev = $book_page - 1; 
$next = $book_page + 1; 
?>

<a 
    class="page-btn <?= $book_page <= 1 ? 'disabled' : '' ?>"
    href="?view=book&book_page=<?= $prev ?>&book_rating=<?= $book_rating ?>&book_id=<?= $book_id ?>"
>
    ‹
</a>

<span class="page-btn active">

    <?= $book_page ?> / <?= $bookPages ?>

</span>

<a 
    class="page-btn <?= $book_page >= $bookPages ? 'disabled' : '' ?>"
    href="?view=book&book_page=<?= $next ?>&book_rating=<?= $book_rating ?>&book_id=<?= $book_id ?>"
>
    ›
</a>

</div>

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

        <input 
            type="hidden" 
            name="view" 
            value="web"
        >

        <select 
            name="web_rating" 
            onchange="this.form.submit()"
        >

            <option value="">Semua Rating</option>
            <option value="5">5 ⭐</option>
            <option value="4">4 ⭐</option>
            <option value="3">3 ⭐</option>
            <option value="2">2 ⭐</option>
            <option value="1">1 ⭐</option>

        </select>

    </form>

</div>

<table>

  <thead>

    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Rating</th>
        <th>Comment</th>
    </tr>

  </thead>

<tbody>

<?php while($row = $webReviews->fetch_assoc()): ?>

<tr>

    <td>WEB-<?= $row['id'] ?></td>

    <td>User <?= $row['user_id'] ?></td>

    <td><?= str_repeat("⭐", $row['rating']) ?></td>

    <td><?= $row['comment'] ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

  <div class="pagination">

  <?php 
  $prev = $web_page - 1; 
  $next = $web_page + 1; 
  ?>

    <a 
        class="page-btn <?= $web_page <= 1 ? 'disabled' : '' ?>"
        href="?view=web&web_page=<?= $prev ?>&web_rating=<?= $web_rating ?>"
    >
        ‹
    </a>

    <span class="page-btn active">

        <?= $web_page ?> / <?= $webPages ?>

    </span>

    <a 
        class="page-btn <?= $web_page >= $webPages ? 'disabled' : '' ?>"
        href="?view=web&web_page=<?= $next ?>&web_rating=<?= $web_rating ?>"
    >
        ›
    </a>

  </div>

</section>

<?php endif; ?>

</main>

</div>

<script src="../../assets/script/admin/shared-layout.js"></script>

</body>

</html>