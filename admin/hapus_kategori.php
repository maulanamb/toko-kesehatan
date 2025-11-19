<?php
session_start();
require_once 'cek_admin.php'; 

require_once '../koneksi.php'; 

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($category_id > 0) {
    $sql = "DELETE FROM categories WHERE category_id = $category_id";
    
    if ($conn->query($sql) === TRUE) {
        header('location: kelola_kategori.php?status=hapus_sukses');
        exit();
    } else {
        $error = urlencode($conn->error);
        header("location: kelola_kategori.php?status=hapus_gagal&error={$error}");
        exit();
    }
} else {
    header('location: kelola_kategori.php?status=id_tidak_valid');
    exit();
}
?>