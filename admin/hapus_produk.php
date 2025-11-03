<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

// 1. Ambil ID dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    // 2. Ambil path gambar sebelum dihapus dari DB
    $sql_get_img = "SELECT image_url FROM products WHERE product_id = $product_id";
    $result_img = $conn->query($sql_get_img);
    if ($result_img->num_rows > 0) {
        $row = $result_img->fetch_assoc();
        $gambar_lama = $row['image_url'];
    } else {
        $gambar_lama = null;
    }

    // 3. Siapkan query DELETE
    $sql_delete = "DELETE FROM products WHERE product_id = $product_id";
    
    // 4. Eksekusi query
    if ($conn->query($sql_delete) === TRUE) {
        // 5. Jika hapus DB berhasil, Hapus file gambar
        if (!empty($gambar_lama) && file_exists('../' . $gambar_lama)) {
            unlink('../' . $gambar_lama);
        }
        
        header('location: kelola_produk.php?status=hapus_sukses');
        exit();
    } else {
        // 6. Jika gagal (kemungkinan karena Foreign Key dari order_details)
        $error = urlencode($conn->error);
        header("location: kelola_produk.php?status=hapus_gagal&error={$error}");
        exit();
    }
} else {
    header('location: kelola_produk.php?status=id_tidak_valid');
    exit();
}
?>