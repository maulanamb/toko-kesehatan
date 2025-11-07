<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $nama_toko_vendor
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendor - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #2c3e50; /* Warna beda */ color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .sidebar ul li a:hover { color: #1abc9c; }
        .content { flex: 1; padding: 20px; background-color: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; background: white; padding: 15px; margin: -20px -20px 20px -20px; }
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
            <h1>Dashboard</h1>
            <a href="../index.php" target="_blank">Lihat Toko Publik</a>
        </div>

        <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Ini adalah halaman Dashboard Vendor Anda. Anda dapat mengelola produk dan pesanan untuk toko Anda di sini.</p>
        
        </div>

</body>
</html>