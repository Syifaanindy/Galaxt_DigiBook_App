<?php
require_once __DIR__ . '/../models/keranjangModel.php';

function handleCartActions($conn) {
    // Ambil User ID dari session login (Pastikan user sudah login)
    $user_id = $_SESSION['user_id']; 

    if (isset($_GET['action']) && isset($_GET['id'])) {
        $book_id = intval($_GET['id']);

        // ACTION: ADD
        if ($_GET['action'] === 'add') {
            if (!isBookInCart($conn, $user_id, $book_id)) {
                addToCartDB($conn, $user_id, $book_id);
            }
        }

        // ACTION: REMOVE
        if ($_GET['action'] === 'remove') {
            removeFromCartDB($conn, $user_id, $book_id);
        }

        // Setelah proses selesai, tendang balik ke halaman keranjang
        header("Location: keranjang.php");
        exit;
    }
}

?>