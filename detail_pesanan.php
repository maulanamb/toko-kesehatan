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
    <title>Detail Pesanan - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        .order-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>
        <div><a href="riwayat_pesanan.php">Kembali ke Riwayat</a></div>
    </div>

    <div class="order-info">
        <p><strong>Tanggal:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Alamat Pengiriman:</strong> <?B> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
    </div>

    <h2>Item yang Dibeli</h2>
    <table>
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
                        (<?php echo htmlspecialchars($item['product_code']); ?>)
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            
            <tr>
                <td colspan="3" class="total-row">Total Belanja:</td>
                <td><strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong></td>
            </tr>
        </tbody>
    </table>

</body>
</html>