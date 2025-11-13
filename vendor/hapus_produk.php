<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    
    // ▼▼▼ PERUBAHAN LOGIKA DARI DELETE KE UPDATE (SOFT DELETE) ▼▼▼
    // Kita tambahkan "AND toko_id = ?" untuk keamanan
    $sql_update = "UPDATE products SET status_produk = 'Diarsip' WHERE product_id = ? AND toko_id = ?";
    // ▲▲▲ SELESAI ▲▲▲
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ii", $product_id, $toko_id_vendor);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header('location: kelola_produk.php?status=hapus_sukses');
        } else {
            // Ini terjadi jika ID produk salah atau bukan milik vendor
            throw new Exception("Akses ditolak atau produk tidak ditemukan.");
        }
    } else {
        $error = urlencode($conn->error);
        header("location: kelola_produk.php?status=hapus_gagal&error={$error}");
    }
    $stmt->close();
    $conn->close();
    
} else {
    header('location: kelola_produk.php?status=id_tidak_valid');
}
exit();
?>