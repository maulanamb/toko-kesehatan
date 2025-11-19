<?php
session_start();

$batas_waktu = 1800; // 30 menit (1800 detik)

if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset();
        session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir, silakan login kembali.'));
        exit();
    }
}
$_SESSION['waktu_terakhir_aktif'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; 

$sql = "SELECT 
            p.product_id, 
            p.product_code, 
            p.product_name, 
            p.price, 
            c.quantity
        FROM 
            cart_items c
        JOIN 
            products p ON c.product_id = p.product_id
        WHERE 
            c.user_id = ?";
            
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_belanja = 0; 

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $cart_items[] = $row;
        $total_belanja += $row['subtotal'];
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .navbar-brand {
            padding-top: 0; 
            padding-bottom: 0; 
            margin-right: 0.5rem; 
        }
        .navbar-brand img {
            height: 80px; 
            width: auto;
            vertical-align: middle; 
        }
    </style>
    </head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Toko Kesehatan Purnama Logo">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="keranjang.php">Keranjang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buku_tamu.php">Buku Tamu</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        
                        <?php if ($role == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        
                        <?php elseif ($role == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
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
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
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

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <h1 class="mb-4">Keranjang Belanja</h1>
        
        <form action="keranjang_update.php" method="POST">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">No.</th>
                                    <th scope="col" style="min-width: 250px;">Nama Produk</th>
                                    <th scope="col" style="width: 120px;">Jumlah</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">Subtotal</th>
                                    <th scope="col" class="pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cart_items) > 0): ?>
                                    <?php $nomor = 1; ?>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td class="ps-4"><?php echo $nomor++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                <small class="d-block text-muted">(<?php echo htmlspecialchars($item['product_code']); ?>)</small>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       style="width: 80px;"
                                                       name="quantity[<?php echo $item['product_id']; ?>]" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="0">
                                            </td>
                                            <td class="text-nowrap">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                            <td class="text-nowrap">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                            <td class="pe-4">
                                                <a href="keranjang_hapus.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Hapus item ini?');">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <tr class="table-light">
                                        <td colspan="4" class="text-end fw-bold">Total belanja (termasuk pajak):</td>
                                        <td class="fw-bold fs-5 text-nowrap">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></td>
                                        <td class="pe-4"></td>
                                    </tr>
                                
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-5">Keranjang belanja Anda kosong.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <?php if (count($cart_items) > 0): ?>
                <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                    <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                        Update Keranjang
                    </button>
                    
                    <a href="checkout.php" class="btn btn-success btn-lg">
                        Lanjut ke Checkout &raquo;
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

</body>
</html>