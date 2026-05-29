<?php

function ambilSemuaKategoriLengkap($conn) {
    $query = "
        SELECT 
            c.id,
            c.category_name,
            COUNT(b.id) AS total_buku
        FROM category c
        LEFT JOIN books b ON b.category_id = c.id
        GROUP BY c.id
        ORDER BY c.id DESC
    ";

    $hasil = $conn->query($query);

    if (!$hasil) {
        return [];
    }

    return $hasil->fetch_all(MYSQLI_ASSOC);
}

function tambahKategori($conn, $category_name) {
    $query = "INSERT INTO category (category_name) VALUES (?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $category_name);

    $berhasil = $stmt->execute();

    $stmt->close();

    return $berhasil;
}

function ambilKategoriById($conn, $id) {
    $query = "SELECT * FROM category WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    $stmt->execute();

    $hasil = $stmt->get_result();
    $data = $hasil->fetch_assoc();

    $stmt->close();

    return $data;
}

function updateKategori($conn, $id, $category_name) {
    $query = "UPDATE category SET category_name = ? WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $category_name, $id);

    $berhasil = $stmt->execute();

    $stmt->close();

    return $berhasil;
}

function hapusKategori($conn, $id) {
    $query = "DELETE FROM category WHERE id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    $berhasil = $stmt->execute();

    $stmt->close();

    return $berhasil;
}

?>