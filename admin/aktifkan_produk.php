<?php
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    
    $sql_update = "UPDATE products SET status_produk = 'Aktif' WHERE product_id = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header('location: kelola_produk.php?status=aktif_sukses');
        exit();
    } else {
        $error = urlencode($conn->error);
        header("location: kelola_produk.php?status=gagal&error={$error}");
        exit();
    }
} else {
    header('location: kelola_produk.php?status=id_tidak_valid');
    exit();
}
?>