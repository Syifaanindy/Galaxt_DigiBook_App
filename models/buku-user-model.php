<?php
function ambilKoleksiDinamis($conn, $search = '', $category = 'all', $page = 1, $limit = 20) {
    $offset = ($page - 1) * $limit;
    $query = "SELECT * FROM books WHERE 1=1";
    $types = "";
    $bindParams = [];

    // Filter Pencarian Teks
    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR author LIKE ?)";
        $types .= "ss";
        $searchWildcard = "%$search%";
        $bindParams[] = $searchWildcard;
        $bindParams[] = $searchWildcard;
    }

    // Filter Dropdown Kategori
    if (!empty($category) && $category !== 'all') {
        $query .= " AND category_id = ?";
        $types .= "i";
        $bindParams[] = (int)$category;
    }

    // Hitung Total Data untuk Pagination
    $countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
    $stmtCount = $conn->prepare($countQuery);
    if (!empty($types)) {
        $stmtCount->bind_param($types, ...$bindParams);
    }
    $stmtCount->execute();
    $totalBooks = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;

    // Tambahkan Limit & Offset Pemisah Halaman
    $query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
    $types .= "ii";
    $bindParams[] = $limit;
    $bindParams[] = $offset;

    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$bindParams);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    return [
        'books' => $books,
        'total' => $totalBooks,
        'total_pages' => ceil($totalBooks / $limit)
    ];
}

?>