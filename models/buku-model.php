<?php
// Fungsi untuk menghitung total seluruh buku di database
function hitungTotalBuku($conn) {
    $query = "SELECT COUNT(*) AS total FROM books";
    $hasil = $conn->query($query);
    if (!$hasil) {
        return 0;
    }
    $data = $hasil->fetch_assoc();
    return (int)$data['total'];
}

// Fungsi untuk mengambil data buku dengan batasan halaman (LIMIT & OFFSET)
function ambilSemuaBukuPaging($conn, $limit, $offset) {
    $query = "SELECT b.*, c.category_name AS category_name 
              FROM books b 
              LEFT JOIN category c ON b.category_id = c.id 
              ORDER BY b.id DESC 
              LIMIT ? OFFSET ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $hasil = $stmt->get_result();
    
    if (!$hasil) {
        return [];
    }
    
    $data = $hasil->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}


function ambilSemuaBuku($conn) {
    $query = "SELECT b.*, c.category_name AS category_name 
              FROM books b 
              LEFT JOIN category c ON b.category_id = c.id 
              ORDER BY b.id DESC";
    $hasil = $conn->query($query);
    if (!$hasil) {
        return [];
    }
    return $hasil->fetch_all(MYSQLI_ASSOC);
}

function ambilSemuaKategori($conn) {
    $query = "SELECT id, category_name FROM category ORDER BY category_name ASC";
    $hasil = $conn->query($query);
    if (!$hasil) {
        return [];
    }
    return $hasil->fetch_all(MYSQLI_ASSOC);
}

function ambilBukuById($conn, $id) {
    $query = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $hasil = $stmt->get_result();
    $data = $hasil->fetch_assoc();
    $stmt->close();
    return $data;
}

function tambahBuku($conn, $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price) {
    $query = "INSERT INTO books (title, author, publisher, synopsis, file_path, cover_image, category_id, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssii", $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price);
    
    $berhasil = $stmt->execute();
    $stmt->close();
    return $berhasil;
}

function updateBuku($conn, $id, $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price) {
    $query = "UPDATE books SET title = ?, author = ?, publisher = ?, synopsis = ?, file_path = ?, cover_image = ?, category_id = ?, price = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssiii", $title, $author, $publisher, $synopsis, $file_path, $cover_image, $category_id, $price, $id);
    
    $berhasil = $stmt->execute();
    $stmt->close();
    return $berhasil;
}

function hapusBuku($conn, $id) {
    $query = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $berhasil = $stmt->execute();
    $stmt->close();
    return $berhasil;
}
?>