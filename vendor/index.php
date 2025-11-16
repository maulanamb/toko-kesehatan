<?php
// 1. Set variabel khusus halaman
$page_title = "Dashboard Vendor";

// 2. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor, $nama_toko_vendor, dan $conn

// --- ▼▼▼ 3. AMBIL DATA UNTUK KARTU VENDOR ▼▼▼ ---

// 1. Hitung Jumlah Produk (hanya milik vendor ini)
$stmt_produk = $conn->prepare("SELECT COUNT(product_id) as total_produk FROM products WHERE toko_id = ? AND status_produk = 'Aktif'");
$stmt_produk->bind_param("i", $toko_id_vendor);
$stmt_produk->execute();
$total_produk_vendor = $stmt_produk->get_result()->fetch_assoc()['total_produk'];
$stmt_produk->close();

// 2. Hitung Jumlah Pesanan (item pesanan milik vendor ini)
$stmt_pesanan = $conn->prepare(
    "SELECT COUNT(od.order_detail_id) as total_pesanan
     FROM order_details od
     JOIN products p ON od.product_id = p.product_id
     JOIN orders o ON od.order_id = o.order_id
     WHERE p.toko_id = ? AND o.status != 'Dibatalkan'"
);
$stmt_pesanan->bind_param("i", $toko_id_vendor);
$stmt_pesanan->execute();
$total_pesanan_vendor = $stmt_pesanan->get_result()->fetch_assoc()['total_pesanan'];
$stmt_pesanan->close();

$conn->close();
// --- ▲▲▲ SELESAI AMBIL DATA ▲▲▲ ---
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <link rel="icon" type="image/png" href="../images/minilogo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: sans-serif; display: flex; margin: 0; background-color: #f8f9fa; } /* BG abu-abu */
        .sidebar { 
            width: 250px; 
            background: #0F4A86; /* Warna Admin */
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
            background-color: white; /* Header putih */
            padding: 15px 20px; 
            margin: -20px -20px 20px -20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

        /* Tombol Logout Sidebar (Merah) */
        .sidebar ul li.logout-item {
            margin: 20px 0 0 0;
            padding-top: 15px;
            border-top: 1px solid #555;
        }
        .sidebar ul li a.sidebar-logout {
            background-color: #dc3545; 
            color: white !important;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            display: block; 
            transition: background-color 0.2s;
        }
        .sidebar ul li a.sidebar-logout:hover {
            background-color: #bb2d3b;
            color: white !important;
        }

        /* ▼▼▼ 4. CSS KARTU (Sama seperti Admin, tapi 2 kolom) ▼▼▼ */
        .kpi-container {
            display: grid;
            /* 2 kolom saja */
            grid-template-columns: repeat(2, 1fr); 
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
        
        @media (max-width: 576px) {
            .kpi-container {
                grid-template-columns: 1fr; /* 1 kolom di HP */
            }
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
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <a href="../index.php" class="btn-header" target="_blank">Lihat Toko Publik</a>
        </div>

        <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Ini adalah ringkasan untuk toko Anda.</p>
        
        <div class="kpi-container">
            <div class="kpi-card">
                <h3>Jumlah Produk Aktif</h3>
                <div class="nilai"><?php echo $total_produk_vendor; ?></div>
            </div>
            <div class="kpi-card">
                <h3>Jumlah Item Dipesan</h3>
                <div class="nilai"><?php echo $total_pesanan_vendor; ?></div>
            </div>
        </div>
        </div>

</body>
</html>