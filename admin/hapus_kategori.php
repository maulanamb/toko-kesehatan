<?php
session_start();
// Pastikan Anda mengaktifkan 'cek_admin.php' saat sudah production
require_once 'cek_admin.php'; 

require_once '../koneksi.php'; 

// 1. Ambil ID dari URL dan pastikan itu angka
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id > 0) {
    // 2. Siapkan query DELETE
    $sql = "DELETE FROM categories WHERE category_id = $category_id";
    
    // 3. Eksekusi query
    if ($conn->query($sql) === TRUE) {
        // 4. Jika berhasil, kembali ke halaman kategori dengan status sukses
        header('location: kelola_kategori.php?status=hapus_sukses');
        exit();
    } else {
        // 5. Jika gagal, kembali dengan pesan error
        // Ini SANGAT MUNGKIN terjadi jika ada produk yang masih terkait dengan kategori ini
        $error = urlencode($conn->error);
        header("location: kelola_kategori.php?status=hapus_gagal&error={$error}");
        exit();
    }
} else {
    // 6. Jika ID tidak valid (bukan angka atau 0)
    header('location: kelola_kategori.php?status=id_tidak_valid');
    exit();
}
?>