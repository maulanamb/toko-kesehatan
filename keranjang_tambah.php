<?php
// Selalu mulai session
session_start();

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum, arahkan ke halaman login
    header("Location: login.php?error=Anda harus login untuk belanja");
    exit();
}

// 2. Sertakan file koneksi
require_once 'koneksi.php';

// 3. Ambil data dari URL (GET) dan Session
$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

// 4. Validasi apakah product_id ada
if ($product_id) {
    
    // 5. Query canggih: ON DUPLICATE KEY UPDATE
    // Ini adalah cara efisien untuk:
    // - JIKA produk belum ada di keranjang (kombinasi user_id & product_id unik),
    //   maka INSERT dengan quantity = 1.
    // - JIKA produk SUDAH ada di keranjang,
    //   maka UPDATE quantity = quantity + 1.
    
    $sql = "INSERT INTO cart_items (user_id, product_id, quantity) 
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id); // 'i' = integer
    
    if ($stmt->execute()) {
        // Berhasil menambahkan/update keranjang
        // Arahkan pengguna ke halaman keranjang
        header("Location: keranjang.php?message=Produk ditambahkan ke keranjang");
    } else {
        // Gagal
        header("Location: index.php?error=Gagal menambahkan produk");
    }
    
    $stmt->close();
    $conn->close();

} else {
    // Jika tidak ada ID produk di URL
    header("Location: index.php?error=Produk tidak valid");
}

exit(); // Pastikan script berhenti
?>