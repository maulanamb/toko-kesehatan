<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .sidebar ul li a:hover { color: #1abc9c; }
        .content { flex: 1; padding: 20px; background-color: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; background: white; padding: 15px; margin: -20px -20px 20px -20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-tambah { display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .product-img { max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px; }
        
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Vendor Panel</h2>
        <p>Toko: <strong><?php echo htmlspecialchars($nama_toko_vendor); ?></strong></p>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan Toko</a></li>
            <li><hr style="border-color: #34495e;"></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Produk Anda