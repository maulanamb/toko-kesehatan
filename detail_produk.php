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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* CSS Tambahan */
        .product-image img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Toko Kesehatan</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="keranjang.php">Keranjang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buku_tamu.php">Buku Tamu</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Buka Toko</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="registrasi.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <div class="row">
                    <div class="col-lg-6 product-image mb-4 mb-lg-0">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    </div>
                    
                    <div class="col-lg-6 product-details">
                        <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($product['category_name'] ?? 'Tanpa Kategori'); ?></span>
                        <h1 class="h2"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                        <p class="h3 fw-bold text-success mb-3">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        
                        <div class="fs-sm text-muted mb-3">
                            Stok tersedia: <?php echo htmlspecialchars($product['stock']); ?>
                        </div>
                        
                        <div class="mb-4">
                            <?php if ($product['stock'] > 0): ?>
                                <a href="keranjang_tambah.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-lg">
                                    Tambah ke Keranjang
                                </a>
                            <?php else: ?>
                                <a href="#" class="btn btn-secondary btn-lg disabled">
                                    Stok Habis
                                </a>
                            <?php endif; ?>
                        </div>

                        <hr>
                        
                        <h3>Deskripsi Produk</h3>
                        <p class="text-muted" style="white-space: pre-wrap;"><?php echo htmlspecialchars($product['description'] ?? 'Tidak ada deskripsi untuk produk ini.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>