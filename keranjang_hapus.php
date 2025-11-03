<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($product_id) {
    // Hapus item dari keranjang untuk user ini
    $sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    
    $stmt->close();
}

$conn->close();

// Kembalikan ke halaman keranjang
header("Location: keranjang.php?message=Item dihapus");
exit();
?>