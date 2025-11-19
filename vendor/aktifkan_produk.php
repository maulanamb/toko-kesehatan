<?php
require_once 'cek_vendor.php'; 

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    
    $sql_update = "UPDATE products SET status_produk = 'Aktif' WHERE product_id = ? AND toko_id = ?";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ii", $product_id, $toko_id_vendor);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header('location: kelola_produk.php?status=aktif_sukses');
        } else {
            throw new Exception("Akses ditolak atau produk tidak ditemukan.");
        }
    } else {
        $error = urlencode($conn->error);
        header("location: kelola_produk.php?status=gagal&error={$error}");
    }
    $stmt->close();
    $conn->close();
    
} else {
    header('location: kelola_produk.php?status=id_tidak_valid');
}
exit();
?>