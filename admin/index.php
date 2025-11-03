<?php
// 1. Panggil "Satpam"
// Jika bukan admin, script akan berhenti di sini
require_once 'admin_check.php';

// 2. Jika lolos, kita panggil koneksi database
// (Perhatikan tanda ../ untuk "naik satu folder")
require_once '../koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_orders.php">Kelola Pesanan</a></li>
            <li><a href="manage_products.php">Kelola Produk</a></li>
            <li><a href="manage_users.php">Kelola Pengguna</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Selamat Datang, <?php echo htmlspecialchars($admin_username); ?>!</h1>
            <a href="../logout.php">Logout</a>
        </div>
        
        <p>Ini adalah halaman utama Admin Panel. Silakan pilih menu di samping untuk mengelola toko.</p>

        </div>

</body>
</html>