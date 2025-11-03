<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Ambil semua data pesanan untuk pengguna ini
// Urutkan dari yang paling baru (DESC)
$sql = "SELECT order_id, order_date, total_amount, payment_method, status 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY order_date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            Selamat Datang, <strong><?php echo htmlspecialchars($username); ?></strong>!
        </div>
        <div>
            <a href="index.php" style="margin-right: 15px;">Kembali ke Produk</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1>Riwayat Pesanan Saya</h1>

    <table>
        <thead>
            <tr>
                <th>No. Order</th>
                <th>Tanggal</th>
                <th>Total Belanja</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <a href="detail_pesanan.php?order_id=<?php echo $order['order_id']; ?>">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">Anda belum memiliki riwayat pesanan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>