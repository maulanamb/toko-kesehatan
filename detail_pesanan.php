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


// 1. Cek Login
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Ambil role

// 2. Ambil ID pesanan dari URL
$order_id = $_GET['order_id'] ?? 0;
if ($order_id == 0) {
    die("Error: ID Pesanan tidak valid.");
}

// 3. Keamanan: Cek apakah pesanan ini milik pengguna yang sedang login
$sql_order = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows == 0) {
    // Jika tidak, tolak akses!
    die("Error: Pesanan tidak ditemukan atau bukan milik Anda.");
}
$order = $result_order->fetch_assoc();
$stmt_order->close();


// 4. Ambil item-item detail untuk pesanan ini (JOIN)
$sql_items = "SELECT p.product_name, p.product_code, od.quantity, od.price_at_purchase
              FROM order_details od
              JOIN products p ON od.product_id = p.product_id
              WHERE od.order_id = ?";
              
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Toko Alat Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* CSS tambahan untuk status */
        .status { 
            font-weight: bold; 
            padding: 5px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        .status-paid, .status-diproses, .status-menunggu-pembayaran { background-color: #ffc107; color: #333; }
        .status-dikirim { background-color: #007bff; }
        .status-selesai { background-color: #28a745; }
        .status-dibatalkan { background-color: #dc3545; }
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
            <h1 class="mb-0">Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>
            <a href="riwayat_pesanan.php" class="btn btn-outline-primary">&laquo; Kembali ke Riwayat</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Ringkasan Pesanan</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tanggal:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                        <p><strong>Alamat Pengiriman:</strong><br>
                           <?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'Tidak ada alamat')); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php 
                            $status_text = htmlspecialchars($order['status']);
                            $status_class = strtolower(str_replace(' ', '-', $status_text));
                            echo "<span class='status status-{$status_class}'>{$status_text}</span>";
                            ?>
                        </p>
                        <p class="h4"><strong>Total: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0">Item yang Dibeli</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nama Produk (ID)</th>
                                <th>Jumlah</th>
                                <th>Harga Saat Beli</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php $subtotal = $item['quantity'] * $item['price_at_purchase']; ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                        <small class="text-muted d-block">(<?php echo htmlspecialchars($item['product_code']); ?>)</small>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?></td>
                                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">Total Belanja:</td>
                                <td class="fw-bold"><strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
    </div>

</body>
</html>