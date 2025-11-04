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
    <style>
        body { font-family: sans-serif; background-color: #f9f9f9; padding: 20px; }
        .container { 
            max-width: 900px; 
            margin: auto; 
            background-color: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        h1 { 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 10px; 
        }
        .nav { 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .nav a { 
            margin-right: 15px; 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold;
        }
        .nav a:hover { text-decoration: underline; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f2f2f2; 
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status { 
            font-weight: bold; 
            padding: 5px 8px;
            border-radius: 4px;
            color: white;
            font-size: 0.9em;
        }
        /* ▼▼▼ PERBAIKAN DI SINI ▼▼▼ */
        .status-paid, .status-diproses, .status-menunggu-pembayaran { background-color: #ffc107; color: #333; }
        /* ▲▲▲ SELESAI PERBAIKAN ▲▲▲ */
        .status-dikirim { background-color: #007bff; }
        .status-selesai { background-color: #28a745; }
        .status-dibatalkan { background-color: #dc3545; }

        /* Notifikasi */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
        
    </style>
</head>
<body>
    <div class="container">
        
        <div class="nav">
            <a href="index.php">Beranda Toko</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="profil.php">Profil Saya</a>
            <a href="logout.php" style="color: red;">Logout</a>
        </div>
        
        <h1>Riwayat Pesanan Saya</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($username); ?>. Berikut adalah semua pesanan Anda.</p>

        <?php 
        if (!empty($pesan_sukses)) echo "<div class='alert alert-sukses'>".htmlspecialchars(urldecode($pesan_sukses))."</div>";
        if (!empty($pesan_error)) echo "<div class='alert alert-gagal'>".htmlspecialchars(urldecode($pesan_error))."</div>";
        ?>

        <table>
            <thead>
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
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <?php 
                                $status_text = htmlspecialchars($order['status']);
                                // Mengubah "Paid" -> "paid"
                                $status_class = strtolower(str_replace(' ', '-', $status_text));
                                echo "<span class='status status-{$status_class}'>{$status_text}</span>";
                                ?>
                            </td>
                            <td>
                                <a href="detail_pesanan.php?order_id=<?php echo $order['order_id']; ?>">Lihat Detail</a>
                                
                                <?php if ($order['status'] == 'Selesai' && is_null($order['feedback_id'])): ?>
                                    <br> | <a href="beri_umpan_balik.php?order_id=<?php echo $order['order_id']; ?>" style="color:green;">Beri Ulasan</a>
                                <?php elseif (!is_null($order['feedback_id'])): ?>
                                    <br> <small style="color:#777;">(Sudah diulas)</small>
                                <?php endif; ?>

                                <?php if ($order['status'] == 'Menunggu Pembayaran' || $order['status'] == 'Diproses' || $order['status'] == 'Paid'): ?>
                                    <br> | <a href="batal_pesanan.php?order_id=<?php echo $order['order_id']; ?>" 
                                            style="color:red;" 
                                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.');">
                                            Batalkan Pesanan
                                           </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Anda belum memiliki riwayat pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>