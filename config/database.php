<?php
$host = 'localhost';
$db   = 'galaxy_digibook';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db, 3306);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>