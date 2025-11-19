<?php
session_start();

$batas_waktu = 1800; // 30 menit
if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset(); session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir.'));
        exit();
    }
}
if (isset($_SESSION['user_id'])) {
    $_SESSION['waktu_terakhir_aktif'] = time(); 
}

require_once 'koneksi.php';


if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

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

$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" type="image/png" href="images/minilogo.png"> 
    <style>
        .navbar-brand img {
             height: 40px; 
             width: auto; 
             vertical-align: middle; 
        }
        .card-img-top { 
            width: 100%; 
            height: 200px; 
            object-fit: cover; 
        }
        .sidebar-kategori .list-group-item.active { 
            background-color: #007bff; 
            border-color: #007bff; 
        }
        .status { 
            font-weight: bold; 
            padding: 5px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        .status-paid, .status-diproses, .status-menunggu-pembayaran { 
            background-color: #ffc107; 
            color: #333;
        }
        /* ▼▼▼ TAMBAHAN: Warna untuk Pending ▼▼▼ */
        .status-pending {
            background-color: #6c757d; /* Abu-abu */
        }
        /* ▲▲▲ SELESAI TAMBAHAN ▲▲▲ */
        .status-dikirim { 
            background-color: #007bff; 
        }
        .status-selesai { 
            background-color: #28a745; 
        }
        .status-dibatalkan { 
            background-color: #dc3545; 
        }

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
                    <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
                    <li class="nav-item"><a class="nav-link" href="buku_tamu.php">Buku Tamu</a></li>
                    <?php if (isset($_SESSION['role'])): ?>
                        <?php if ($role == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        <?php elseif ($role == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($username); ?></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item active" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">Halo, <?php echo htmlspecialchars($username); ?></a>
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
        <h2 class="mb-4">Riwayat Pesanan Saya</h2>

        <?php 
        if (!empty($pesan_sukses)) echo "<div class='alert alert-success'>".htmlspecialchars(urldecode($pesan_sukses))."</div>";
        if (!empty($pesan_error)) echo "<div class='alert alert-danger'>".htmlspecialchars(urldecode($pesan_error))."</div>";
        ?>

        <div class="card shadow-sm border-0">
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
                                            
                                            <?php if ($order['status'] == 'Dikirim'): ?>
                                                <a href="selesaikan_pesanan.php?order_id=<?php echo $order['order_id']; ?>" 
                                                   class="btn btn-sm btn-success ms-1" 
                                                   onclick="return confirm('Apakah pesanan sudah Anda terima dengan baik?');">
                                                   Pesanan Diterima
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($order['status'] == 'Selesai' && is_null($order['feedback_id'])): ?>
                                                <a href="beri_umpan_balik.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-success ms-1">Beri Ulasan</a>
                                            <?php elseif (!is_null($order['feedback_id'])): ?>
                                                <span class="badge bg-light text-dark border ms-1">Sudah Diulas</span>
                                            <?php endif; ?>

                                            <?php if ($order['status'] == 'Pending' || $order['status'] == 'Paid' || $order['status'] == 'Menunggu Pembayaran'): ?>
                                                <a href="batal_pesanan.php?order_id=<?php echo $order['order_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger ms-1" 
                                                   onclick="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.');">
                                                   Batalkan
                                                </a>
                                            <?php endif; ?>
                                            </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">Belum ada riwayat pesanan.</td>
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