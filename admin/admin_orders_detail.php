<?php
// 1. Panggil "Satpam"
require_once 'admin_check.php';

// 2. Panggil koneksi (naik satu level)
require_once '../koneksi.php';

$pesan_sukses = "";

// 3. PROSES UPDATE STATUS (JIKA FORM DI-SUBMIT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id_to_update = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_status, $order_id_to_update);
    
    if ($stmt_update->execute()) {
        $pesan_sukses = "Status pesanan berhasil diperbarui!";
    }
    $stmt_update->close();
}

// 4. Ambil ID pesanan dari URL
$order_id = $_GET['order_id'] ?? 0;
if ($order_id == 0) {
    die("Error: ID Pesanan tidak valid.");
}

// 5. Ambil data pesanan (tanpa cek user_id, karena admin boleh lihat semua)
$sql_order = "SELECT o.*, u.username, u.email 
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id 
              WHERE o.order_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows == 0) {
    die("Error: Pesanan tidak ditemukan.");
}
$order = $result_order->fetch_assoc();
$stmt_order->close();


// 6. Ambil item-item detail untuk pesanan ini
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
    <title>Detail Pesanan #<?php echo $order_id; ?> - Admin</title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        .order-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-form { margin-top: 20px; border: 1px solid #007bff; padding: 15px; border-radius: 8px; }
        .message-sukses { color: green; border: 1px solid green; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_orders.php">Kelola Pesanan</a></li>
            <li><a href="manage_products.php">Kelola Produk</a></li>
            <li><a href="manage_users.php">Kelola Pengguna</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>
            <a href="manage_orders.php">Kembali ke Daftar Pesanan</a>
        </div>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="message-sukses"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>

        <div class="status-form">
            <form action="" method="POST">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <label for="status" style="font-size: 1.2em; font-weight: bold;">Ubah Status Pesanan:</label>
                <select name="status" id="status" style="padding: 5px; font-size: 1.1em; margin: 0 10px;">
                    <?php 
                        $all_status = ['Pending', 'Paid', 'Shipped', 'Cancelled'];
                        foreach ($all_status as $status):
                            $selected = ($order['status'] == $status) ? 'selected' : '';
                            echo "<option value='$status' $selected>$status</option>";
                        endforeach;
                    ?>
                </select>
                <button type="submit" name="update_status" style="padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer;">Update</button>
            </form>
        </div>

        <div class="order-info">
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username'] ?? 'User Dihapus'); ?> (<?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?>)</p>
            <p><strong>Tanggal:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Total:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
            <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
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
            </tbody>
        </table>
        
    </div>

</body>
</html>