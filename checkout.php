<?php
session_start();
require_once 'koneksi.php';

// --- SATPAM (Login & Logout Otomatis) ---
if (isset($_SESSION['user_id'])) {
    $batas_waktu = 1800; // 30 menit
    if (isset($_SESSION['waktu_terakhir_aktif'])) {
        if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
            session_unset(); session_destroy();
            header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir.'));
            exit();
        }
    }
    $_SESSION['waktu_terakhir_aktif'] = time(); 
} else {
    // Jika belum login, paksa ke login
    header('Location: login.php?error=' . urlencode('Anda harus login untuk checkout.'));
    exit();
}
// --- SELESAI SATPAM ---

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; 

// --- 1. LOGIKA CEK STOK OTOMATIS & POP-UP ALERT ▼▼▼ ---
$stok_aman = true;
$pesan_alert = "";

// Kita cek stok berdasarkan data di tabel cart_items
$sql_cek_stok = "SELECT c.product_id, c.quantity, p.product_name, p.stock, p.status_produk 
                 FROM cart_items c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
$stmt_cek = $conn->prepare($sql_cek_stok);
$stmt_cek->bind_param("i", $user_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

if ($result_cek->num_rows == 0) {
    // Keranjang kosong di database
    header("Location: keranjang.php?error=Keranjang Anda kosong");
    exit();
}

while ($row = $result_cek->fetch_assoc()) {
    // Cek 1: Apakah produk diarsipkan?
    if ($row['status_produk'] != 'Aktif') {
        $stok_aman = false;
        $pesan_alert = "Maaf, produk '" . $row['product_name'] . "' sedang tidak aktif/diarsipkan.";
        break;
    }
    
    // Cek 2: Apakah jumlah beli > stok tersedia?
    if ($row['quantity'] > $row['stock']) {
        $stok_aman = false;
        $pesan_alert = "Maaf, stok tidak mencukupi untuk '" . $row['product_name'] . "'. Sisa stok: " . $row['stock'];
        break; 
    }
}
$stmt_cek->close();

// JIKA ADA MASALAH -> TAMPILKAN POP-UP JS LALU KEMBALI
if (!$stok_aman) {
    $conn->close();
    echo "<script>
            alert('" . addslashes($pesan_alert) . "');
            window.location.href = 'keranjang.php';
          </script>";
    exit();
}
// --- ▲▲▲ SELESAI LOGIKA POP-UP ▲▲▲ ---


// 2. Ambil Data Keranjang untuk Tampilan HTML
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
while($row = $result_cart->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $cart_items[] = $row; 
    $total_belanja += $row['subtotal'];
}
$stmt_cart->close();

// 3. Ambil Data User untuk Form Default
$sql_user = "SELECT address, city, contact_no, email FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
$conn->close();

// Gabungkan alamat default
$alamat_pengiriman_default = ($user['address'] ?? '') . ", " . ($user['city'] ?? '');
$alamat_pengiriman_default = trim($alamat_pengiriman_default, ", ");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Kesehatan</title>
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
            <a class="navbar-brand fw-bold" href="index.php">
                 <img src="images/logo.png" alt="Toko Kesehatan Purnama Logo">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="buku_tamu.php">Buku Tamu</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($role == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        <?php elseif ($role == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($username); ?></a>
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
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($username); ?></a>
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
                            
                            <div class="mb-3">
                                <label for="recipient_name" class="form-label">Nama Penerima:</label>
                                <input type="text" id="recipient_name" name="recipient_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Alamat Lengkap:</label>
                                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" required><?php echo htmlspecialchars($alamat_pengiriman_default); ?></textarea>
                                <small class="text-muted">Anda dapat mengubah alamat ini jika ingin mengirim ke lokasi lain.</small>
                            </div>
                            
                            <div class="mt-3">
                                <p class="mb-1"><strong>Kontak:</strong> <?php echo htmlspecialchars($user['contact_no'] ?? '-'); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <a href="profil.php" class="text-decoration-none small">Ubah Data Kontak di Profil</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h2 class="h5 mb-0">Metode Pembayaran</h2>
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input type="radio" id="emoney" name="payment_method" value="Bayar dengan E-Money" class="form-check-input" required>
                                <label for="emoney" class="form-check-label">Bayar dengan E-Wallet</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="cod" name="payment_method" value="Bayar di Tempat (COD)" class="form-check-input" checked required>
                                <label for="cod" class="form-check-label">Bayar di Tempat (COD)</label>
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