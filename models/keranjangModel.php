<?php
// Cek apakah buku sudah ada di keranjang user
function isBookInCart($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Tambah ke tabel carts
function addToCartDB($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $book_id);
    return $stmt->execute();
}

// Hapus dari tabel carts
function removeFromCartDB($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    return $stmt->execute();
}

// Ambil semua buku di keranjang user (Pakai JOIN biar lengkap)
function getUserCartItems($conn, $user_id) {
    $query = "SELECT b.* FROM books b 
              JOIN cart c ON b.id = c.book_id 
              WHERE c.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>