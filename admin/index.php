<?php
$page_title = "Dashboard"; 

require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$result_pesanan = $conn->query("SELECT COUNT(order_id) as total_pesanan FROM orders WHERE status != 'Dibatalkan'");
$total_pesanan = $result_pesanan->fetch_assoc()['total_pesanan'];

$result_produk = $conn->query("SELECT COUNT(product_id) as total_produk FROM products WHERE status_produk = 'Aktif'");
$total_produk = $result_produk->fetch_assoc()['total_produk'];

$result_kategori = $conn->query("SELECT COUNT(category_id) as total_kategori FROM categories");
$total_kategori = $result_kategori->fetch_assoc()['total_kategori'];

$result_user = $conn->query("SELECT COUNT(user_id) as total_user FROM users WHERE role != 'admin'");
$total_user = $result_user->fetch_assoc()['total_user'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/minilogo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #0F4A86; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .btn-logout {
            background-color: #dc3545; color: white; padding: 8px 12px;
            text-decoration: none; border-radius: 5px; font-weight: bold;
        }
        .btn-logout:hover { background-color: #bb2d3b; color: white; }

        .kpi-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr); 
            gap: 20px;
            margin-top: 20px;
        }
        .kpi-card {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        .kpi-card h3 {
            margin-top: 0;
            font-size: 1.1em;
            color: #555;
            font-weight: 600;
        }
        .kpi-card .nilai {
            font-size: 2.5em;
            font-weight: bold;
            color: #0d6efd; 
        }
        
        @media (max-width: 992px) {
            .kpi-container {
                grid-template-columns: repeat(2, 1fr); /* 2 kolom di tablet */
            }
        }
        @media (max-width: 576px) {
            .kpi-container {
                grid-template-columns: 1fr; /* 1 kolom di HP */
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
            <li><a href="laporan.php">Laporan Bulanan</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <a href="../logout.php" class="btn-logout">LOGOUT</a>
        </div>

        <h2>Selamat Datang di Admin Panel</h2>
        <p>Silakan pilih menu di sebelah kiri untuk mulai mengelola website Anda.</p>
        
        <div class="kpi-container">
            <div class="kpi-card">
                <h3>Total Pesanan</h3>
                <div class="nilai"><?php echo $total_pesanan; ?></div>
            </div>
            <div class="kpi-card">
                <h3>Produk Aktif</h3>
                <div class="nilai"><?php echo $total_produk; ?></div>
            </div>
            <div class="kpi-card">
                <h3>Total Kategori</h3>
                <div class="nilai"><?php echo $total_kategori; ?></div>
            </div>
            <div class="kpi-card">
                <h3>Total Pengguna</h3>
                <div class="nilai"><?php echo $total_user; ?></div>
            </div>
        </div>
        </div>

</body>
</html>