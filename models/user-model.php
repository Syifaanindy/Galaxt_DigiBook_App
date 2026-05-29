<?php

function cariUserByUsername($conn, $username) {
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    $hasil = $stmt->get_result();
    $user = $hasil->fetch_assoc();
    
    $stmt->close();
    return $user;
}

function usernameSudahTerdaftar($conn, $username) {
    $query = "SELECT id FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $hasil = $stmt->get_result();
    $terdaftar = $hasil->num_rows > 0;

    $stmt->close();
    return $terdaftar;
}

function adminSudahAda($conn) {
    $query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $hasil = $conn->query($query);
    return $hasil && $hasil->num_rows > 0;
}

function ambilSemuaUser($conn) {
    $cekKolom = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
    $adaCreatedAt = $cekKolom && $cekKolom->num_rows > 0;
    
    if ($adaCreatedAt) {
        $query = "SELECT id, username, role, created_at FROM users ORDER BY id ASC";
    } else {
        $query = "SELECT id, username, role, NULL AS created_at FROM users ORDER BY id ASC";
    }
    
    $hasil = $conn->query($query);
    return $hasil->fetch_all(MYSQLI_ASSOC);
}

function buatUser($conn, $username, $email, $password, $role = 'user') {
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ssss", $username, $email, $passwordHash, $role);
    $berhasil = $stmt->execute();
    $stmt->close();

    return $berhasil;
}

function ambilPengunjung($conn) {
    // Cek username dan email saja, karena cuma itu yang ada di DB
    $query = "SELECT username, email FROM users WHERE role = 'user' ORDER BY username ASC";
    
    $hasil = $conn->query($query);
    return $hasil->fetch_all(MYSQLI_ASSOC);
}