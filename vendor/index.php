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
    <link rel="icon" type="image/png" href="../images/minilogo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { 
            width: 250px; 
            background: #333; /* Warna Admin */
            color: white; 
            min-height: 100vh; 
            padding: 20px; 
            box-sizing: border-box; 
        }
        .sidebar h2 { 
            border-bottom: 1px solid #555; 
            padding-bottom: 10px; 
        }
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
        
        /* Tombol Header (Biru) */
        .btn-header {
            background-color: #0d6efd; 
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-header:hover {
            background-color: #0b5ed7; 
            color: white;
        }

        /* ▼▼▼ 1. TAMBAHKAN CSS TOMBOL LOGOUT SIDEBAR ▼▼▼ */
        .sidebar ul li.logout-item {
            margin: 20px 0 0 0; /* Margin atas untuk memisahkan */
            padding-top: 15px; /* Jarak dari garis */
            border-top: 1px solid #555; /* Garis pemisah */
        }
        .sidebar ul li a.sidebar-logout {
            background-color: #dc3545; /* Merah */
            color: white !important; /* Paksa warna putih */
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            display: block; /* Agar memenuhi <li> */
            transition: background-color 0.2s;
        }
        .sidebar ul li a.sidebar-logout:hover {
            background-color: #bb2d3b; /* Merah lebih gelap */
            color: white !important;
        }
        /* ▲▲▲ SELESAI ▲▲▲ */
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
            
            <li class="logout-item">
                <a href="../logout.php" class="sidebar-logout" onclick="return confirm('Anda yakin ingin logout?');">Logout</a>
            </li>
        </ul>
    </div>
    <div class="content">
        <div class="header">
            <h1>Dashboard</h1>
            <a href="../index.php" class="btn-header">Lihat Toko Publik</a>
        </div>

        <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Ini adalah halaman Dashboard Vendor Anda. Anda dapat mengelola produk dan pesanan untuk toko Anda di sini.</p>
        
    </div>

</body>
</html>