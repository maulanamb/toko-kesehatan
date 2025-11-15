<?php
// 1. Set variabel khusus halaman
$page_title = "Dashboard"; 

// 2. Panggil Satpam
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; // Panggil koneksi (meskipun tidak dipakai, untuk konsistensi)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/minilogo.png"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #12b05f; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
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
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        
        /* CSS STATUS VENDOR */
        .status-vendor {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
        }
        .status-pending { background-color: #ffc107; color: #333; }
        .status-approved { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
        
        /* CSS TOMBOL LOGOUT */
        .btn-logout {
            background-color: #dc3545; 
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-logout:hover {
            background-color: #bb2d3b; 
            color: white;
        }

        /* CSS BANTUAN */
        .table .btn-sm {
            margin: 2px;
        }
        .text-center {
            text-align: center !important;
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
            <h1><?php echo htmlspecialchars($page_title); ?></h1> <a href="../logout.php" class="btn-logout">LOGOUT</a>
        </div>

        <h2>Selamat Datang di Admin Panel</h2>
        <p>Silakan pilih menu di sebelah kiri untuk mulai mengelola website Anda.</p>
    </div>

</body>
</html>