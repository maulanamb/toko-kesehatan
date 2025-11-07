<?php
session_start();
require_once 'koneksi.php'; // Pastikan file ini menyediakan variabel $conn

// Ambil semua data produk
$sql = "SELECT product_id, product_name, price, image_url, stock 
        FROM products 
        WHERE stock > 0 
        ORDER BY product_id DESC";
        
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Toko Alat Kesehatan</title>
    
    <style>
        body { 
            font-family: sans-serif; 
            margin: 0; 
            background-color: #f4f4f4; 
        }
        .header {
            background-color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }
        .header .nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-card .info {
            padding: 15px;
            flex-grow: 1;
        }
        .product-card h3 {
            margin-top: 0;
            font-size: 1.2em;
        }
        .product-card .price {
            font-size: 1.1em;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .product-card .actions {
            padding: 15px;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }
        .product-card .btn {
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        .btn-detail {
            background-color: #f0f0f0;
            color: #333;
        }
        .btn-buy {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php" class="logo">Toko Kesehatan</a>
        
        <nav class="nav">
            <a href="keranjang.php">Keranjang</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin/index.php">Dashboard Admin</a>
                    <a href="logout.php" style="color: red;">Logout</a>
                <?php else: ?>
                    <a href="profil.php">Profil Saya</a>
                    <a href="riwayat_pesanan.php">Riwayat</a>
                    
                    <a href="buka_toko.php">Buka Toko</a>
                    <a href="logout.php" style="color: red;">Logout</a>
                <?php endif; ?>

            <?php else: ?>
                <a href="buku_tamu.php">Buku Tamu</a>
                <a href="login.php">Login</a>
                <a href="registrasi.php">Register</a>
            <?php endif; ?>
            
        </nav>
    </header>

    <div class="container">
        <h2>Produk Kami</h2>
        
        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="info">
                            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <div class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                            <small>Stok: <?php echo $product['stock']; ?></small>
                        </div>
                        <div class="actions">
                            <a href="detail_produk.php?id=<?php echo $product['product_id']; ?>" class="btn btn-detail">Lihat Detail</a>
                            
                            <a href="keranjang_tambah.php?id=<?php echo $product['product_id']; ?>" class="btn btn-buy">Beli</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada produk untuk ditampilkan.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>