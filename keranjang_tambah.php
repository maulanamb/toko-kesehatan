<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Anda harus login untuk belanja");
    exit();
}

require_once 'koneksi.php';

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($product_id) {
    
    
    $sql = "INSERT INTO cart_items (user_id, product_id, quantity) 
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id); 
    
    if ($stmt->execute()) {
        header("Location: keranjang.php?message=Produk ditambahkan ke keranjang");
    } else {
        header("Location: index.php?error=Gagal menambahkan produk");
    }
    
    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php?error=Produk tidak valid");
}

exit(); 
?>