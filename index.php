<?php
// Selalu mulai session di baris paling atas
session_start();

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Sertakan file koneksi database
require_once 'koneksi.php';

// 3. Ambil data pengguna dari session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// 4. Ambil semua data produk dari database
$sql = "SELECT product_id, product_name, price, image_url FROM products";
$result = $conn->query($sql);

// Kita akan simpan data produk ke dalam array
$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-bottom: 1px solid #ccc; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 kolom */
            gap: 20px; /* Jarak antar produk */
            padding: 20px;
        }
        .product-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .product-card h3 { margin-top: 0; }
        .product-card .price {
            font-weight: bold;
            color: #d00;
            margin-bottom: 10px;
        }
        .product-card .actions button {
            margin: 0 5px;
            padding: 8px 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="header">
        <div>
            Selamat Datang, <strong><?php echo htmlspecialchars($username); ?></strong>!
            (Role: <?php echo htmlspecialchars($role); ?>)
        </div>
        <div>
            <a href="profil.php" style="margin-right: 15px;">Profil Saya</a>
            <a href="riwayat_pesanan.php" style="margin-right: 15px;">Riwayat Saya</a>
            <a href="keranjang.php" style="margin-right: 15px;">🛒 Keranjang Belanja</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1>Halaman Produk</h1>

    <div class="product-grid">
        <?php
        // 5. Lakukan looping (perulangan) untuk menampilkan setiap produk
        if (count($products) > 0):
            foreach ($products as $product):
        ?>
            <div class="product-card">
                <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : 'https://via.placeholder.com/150'; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                
                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                
                <div class="price">
                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                </div>
                
                <div class="actions">
                    <button>View</button>
                    <a href="keranjang_tambah.php?id=<?php echo $product['product_id']; ?>">
                        <button>Buy</button>
                    </a>
                </div>
            </div>
        <?php
            endforeach;
        else:
            // Tampilkan pesan jika tidak ada produk
        ?>
            <p>Belum ada produk yang tersedia.</p>
        <?php
        endif;
        ?>
    </div>

</body>
</html>