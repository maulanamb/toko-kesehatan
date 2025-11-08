<?php
session_start();
require_once 'koneksi.php'; // Pastikan $conn

// --- 1. Logika untuk Kategori (Filter) ---
$sql_kategori = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result_kategori = $conn->query($sql_kategori);
$kategori_list = $result_kategori->fetch_all(MYSQLI_ASSOC);

// Ambil kategori yang dipilih (jika ada)
$kategori_dipilih = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$nama_kategori_aktif = "Semua Produk"; // Default

// --- 2. Logika untuk Sortir ---
$sort_option = $_GET['sort'] ?? 'terbaru'; // Default 'terbaru'
$order_by_sql = "ORDER BY p.product_id DESC"; // Default

if ($sort_option == 'harga_asc') {
    $order_by_sql = "ORDER BY p.price ASC";
} elseif ($sort_option == 'harga_desc') {
    $order_by_sql = "ORDER BY p.price DESC";
} elseif ($sort_option == 'nama_asc') {
    $order_by_sql = "ORDER BY p.product_name ASC";
}

// --- 3. Logika SQL Utama (Gabungan) ---
$sql_params = [];
$sql_param_types = "";

// Query dasar
$sql_produk = "SELECT p.product_id, p.product_name, p.price, p.image_url, p.stock 
               FROM products p 
               WHERE p.stock > 0";

// Tambahkan filter kategori JIKA dipilih
if ($kategori_dipilih > 0) {
    $sql_produk .= " AND p.category_id = ?";
    $sql_params[] = $kategori_dipilih;
    $sql_param_types .= "i";
    
    // Ambil nama kategori untuk judul
    foreach ($kategori_list as $kat) {
        if ($kat['category_id'] == $kategori_dipilih) {
            $nama_kategori_aktif = $kat['category_name'];
            break;
        }
    }
}

// Tambahkan sorting
$sql_produk .= " " . $order_by_sql;

// Eksekusi query
$stmt = $conn->prepare($sql_produk);
if ($kategori_dipilih > 0) {
    $stmt->bind_param($sql_param_types, ...$sql_params);
}
$stmt->execute();
$result_produk = $stmt->get_result();
$products = $result_produk->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nama_kategori_aktif); ?> - Toko Kesehatan</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .sidebar-kategori .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
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
                    <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="buku_tamu.php">Buku Tamu</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
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
        <div class="row">
            
            <div class="col-lg-3 sidebar-kategori">
                <h4 class="mb-3">Kategori</h4>
                <div class="list-group shadow-sm">
                    <a href="index.php?sort=<?php echo $sort_option; ?>" 
                       class="list-group-item list-group-item-action <?php echo ($kategori_dipilih == 0) ? 'active' : ''; ?>">
                        Semua Produk
                    </a>
                    <?php foreach ($kategori_list as $kategori): ?>
                        <a href="index.php?kategori=<?php echo $kategori['category_id']; ?>&sort=<?php echo $sort_option; ?>" 
                           class="list-group-item list-group-item-action <?php echo ($kategori_dipilih == $kategori['category_id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($kategori['category_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><?php echo htmlspecialchars($nama_kategori_aktif); ?></h2>
                    
                    <form action="index.php" method="GET" class="d-flex">
                        <?php if ($kategori_dipilih > 0): ?>
                            <input type="hidden" name="kategori" value="<?php echo $kategori_dipilih; ?>">
                        <?php endif; ?>
                        
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="terbaru" <?php echo ($sort_option == 'terbaru') ? 'selected' : ''; ?>>Urutkan (Terbaru)</option>
                            <option value="harga_asc" <?php echo ($sort_option == 'harga_asc') ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="harga_desc" <?php echo ($sort_option == 'harga_desc') ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="nama_asc" <?php echo ($sort_option == 'nama_asc') ? 'selected' : ''; ?>>Nama (A-Z)</option>
                        </select>
                    </form>
                </div>
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                        <p class="card-text fw-bold text-success">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                        <p class="card-text"><small class="text-muted">Stok: <?php echo $product['stock']; ?></small></p>
                                    </div>
                                    <div class="card-footer bg-white border-top-0">
                                        <a href="detail_produk.php?id=<?php echo $product['product_id']; ?>" class="btn btn-outline-secondary btn-sm">Lihat Detail</a>
                                        <a href="keranjang_tambah.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm float-end">Beli</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center text-muted">Produk tidak ditemukan untuk filter ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </div>
    </div>

</body>
</html>