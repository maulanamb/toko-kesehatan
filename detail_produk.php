<?php
session_start();
require_once 'koneksi.php'; // Pastikan file ini menyediakan variabel $conn

// 1. Ambil ID Produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    // Jika tidak ada ID, kembalikan ke beranda
    header("Location: index.php");
    exit();
}

// 2. Ambil detail produk dari database
$sql = "SELECT 
            p.product_id, 
            p.product_name, 
            p.description, 
            p.price, 
            p.stock, 
            p.image_url, 
            c.category_name 
        FROM 
            products p 
        LEFT JOIN 
            categories c ON p.category_id = c.category_id 
        WHERE 
            p.product_id = ?";
            
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    // Jika ID produk tidak ditemukan
    header("Location: index.php?error=Produk tidak ditemukan");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Toko Kesehatan</title>
    
    <style>
        body { 
            font-family: sans-serif; 
            margin: 0; 
            background-color: #f9f9f9; 
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
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 30px;
        }
        
        .product-image {
            flex: 1;
        }
        .product-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        
        .product-details {
            flex: 1.5;
        }
        .product-details h1 {
            margin-top: 0;
            font-size: 2em;
        }
        .product-details .category {
            font-size: 0.9em;
            color: #555;
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .product-details .price {
            font-size: 1.8em;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 20px;
        }
        .product-details .stock {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 20px;
        }
        .product-details .description {
            line-height: 1.6;
            color: #333;
            margin-bottom: 25px;
        }
        .product-details .btn-buy {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.2s;
        }
        .product-details .btn-buy:hover {
            background-color: #0056b3;
        }
        .product-details .btn-disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .product-details .btn-disabled:hover {
            background-color: #ccc;
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
                    <a href="logout.php" style="color: red;">Logout</a>
                <?php endif; ?>

            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="registrasi.php">Register</a>
            <?php endif; ?>
            
        </nav>
    </header>

    <div class="container">
        
        <div class="product-image">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
        
        <div class="product-details">
            <span class="category"><?php echo htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori'); ?></span>
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
            <div class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
            
            <div class="stock">
                Stok tersedia: <?php echo htmlspecialchars($product['stock']); ?>
            </div>
            
            <div class="description">
                <h3>Deskripsi Produk</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Tidak ada deskripsi untuk produk ini.')); ?></p>
                </div>
            
            <?php if ($product['stock'] > 0): ?>
                <a href="keranjang_tambah.php?id=<?php echo $product['product_id']; ?>" class="btn-buy">
                    Tambah ke Keranjang
                </a>
            <?php else: ?>
                <a href="#" class="btn-buy btn-disabled" onclick="return false;">
                    Stok Habis
                </a>
            <?php endif; ?>
            
        </div>
        
    </div>

</body>
</html>