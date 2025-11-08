<?php
session_start();
require_once 'koneksi.php'; // Pastikan file ini menyediakan variabel $conn

// 1. "Satpam" untuk Customer
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}

// 2. Ambil ID Customer
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Ambil role

// 3. Ambil data pesanan HANYA untuk user ini
$sql = "SELECT 
            o.order_id, 
            o.order_date, 
            o.total_amount, 
            o.status,
            f.id as feedback_id 
        FROM orders o
        LEFT JOIN feedback f ON o.order_id = f.order_id
        WHERE o.user_id = ? 
        ORDER BY o.order_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Ambil pesan sukses/error
$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko Kesehatan</title>
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
                    
                    <?php if (isset($_SESSION['user_id'])): // Pasti true di sini ?>
                        
                        <?php if ($role == 'admin'): // Cek variabel $role ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        
                        <?php elseif ($role == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item active" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>

                        <?php else: // JIKA CUSTOMER BIASA ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item active" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
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
        <h1 class="mb-4">Riwayat Pesanan Saya</h1>
        <p class="lead">Selamat datang, <?php echo htmlspecialchars($username); ?>. Berikut adalah semua pesanan Anda.</p>

        <?php 
        if (!empty($pesan_sukses)) echo "<div class='alert alert-success'>".htmlspecialchars(urldecode($pesan_sukses))."</div>";
        if (!empty($pesan_error)) echo "<div class='alert alert-danger'>".htmlspecialchars(urldecode($pesan_error))."</div>";
        ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No. Order</th>
                                <th>Tanggal</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                            $status_text = htmlspecialchars($order['status']);
                                            $status_class = strtolower(str_replace(' ', '-', $status_text));
                                            echo "<span class='status status-{$status_class}'>{$status_text}</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <a href="detail_pesanan.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                            
                                            <?php if ($order['status'] == 'Selesai' && is_null($order['feedback_id'])): ?>
                                                <a href="beri_umpan_balik.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-success mt-1">Beri Ulasan</a>
                                            <?php elseif (!is_null($order['feedback_id'])): ?>
                                                <small class="d-block text-muted mt-1">(Sudah diulas)</small>
                                            <?php endif; ?>

                                            <?php if ($order['status'] == 'Menunggu Pembayaran' || $order['status'] == 'Diproses' || $order['status'] == 'Paid'): ?>
                                                <a href="batal_pesanan.php?order_id=<?php echo $order['order_id']; ?>" 
                                                        class="btn btn-sm btn-outline-danger mt-1" 
                                                        onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.');">
                                                        Batalkan
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Anda belum memiliki riwayat pesanan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>