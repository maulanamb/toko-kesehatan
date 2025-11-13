<?php
session_start();

// --- ▼▼▼ LOGIKA LOGOUT OTOMATIS (Poin 3) ▼▼▼ ---
$batas_waktu = 1800; // 30 menit (1800 detik)

if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset();
        session_destroy();
        // Arahkan ke login dengan pesan
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir, silakan login kembali.'));
        exit();
    }
}
// Reset timer setiap kali halaman dimuat
$_SESSION['waktu_terakhir_aktif'] = time();
// --- ▲▲▲ SELESAI LOGIKA LOGOUT ▲▲▲ ---


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Ambil role untuk navigasi

// --- 1. Ambil data keranjang (mirip keranjang.php) ---
$sql_cart = "SELECT p.product_id, p.product_name, p.price, c.quantity
             FROM cart_items c
             JOIN products p ON c.product_id = p.product_id
             WHERE c.user_id = ?";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

$cart_items = [];
$total_belanja = 0;
if ($result_cart->num_rows > 0) {
    while($row = $result_cart->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $cart_items[] = $row; // Simpan juga product_id
        $total_belanja += $row['subtotal'];
    }
} else {
    // Jika keranjang kosong, jangan biarkan checkout
    header("Location: keranjang.php?error=Keranjang Anda kosong");
    exit();
}
$stmt_cart->close();

// --- 2. Ambil data alamat pengguna ---
$sql_user = "SELECT address, city, contact_no FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
$conn->close();

// Gabungkan alamat
$alamat_pengiriman = ($user['address'] ?? '') . ", " . ($user['city'] ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

                        <?php else: // JIKA CUSTOMER BIASA ?>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Konfirmasi Pesanan</h1>
            <a href="keranjang.php" class="btn btn-outline-primary">&laquo; Kembali ke Keranjang</a>
        </div>
        
        <form action="proses_order.php" method="POST">
            <div class="row g-4">
            
                <div class="col-lg-7">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0">Alamat Pengiriman</h2>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong><?php echo htmlspecialchars($username); ?></strong><br>
                                <?php echo htmlspecialchars($user['contact_no'] ?? 'No. Kontak belum diatur'); ?><br>
                                <?php echo htmlspecialchars($user['address'] ?? 'Alamat belum diatur'); ?><br>
                                <?php echo htmlspecialchars($user['city'] ?? ''); ?>
                            </p>
                            <a href="profil.php">Ubah Alamat di Profil</a>
                            <input type="hidden" name="shipping_address" value="<?php echo htmlspecialchars($alamat_pengiriman); ?>">
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0">Metode Pembayaran</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input type="radio" id="prepaid" name="payment_method" value="Prepaid (Credit Card/PayPal)" class="form-check-input" checked required>
                                <label for="prepaid" class="form-check-label">Prepaid (Credit Card/PayPal)</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="postpaid" name="payment_method" value="Postpaid (Bayar di Tempat)" class="form-check-input">
                                <label for="postpaid" class="form-check-label">Postpaid (Bayar di Tempat / COD)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0">Ringkasan Pesanan</h2>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                        <small class="d-block text-muted">Jumlah: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <span class="text-nowrap">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="card-footer bg-white fs-5 fw-bold d-flex justify-content-between">
                            <span>Total:</span>
                            <span>Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span>
                            <input type="hidden" name="total_amount" value="<?php echo $total_belanja; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100 mt-4">
                        Bayar & Buat Pesanan
                    </button>
                </div>
                
            </div>
        </form>
    </div>

</body>
</html>