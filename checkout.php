<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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
$alamat_pengiriman = $user['address'] . ", " . $user['city'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        .checkout-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .summary, .shipping, .payment { border: 1px solid #ccc; padding: 15px; border-radius: 8px; }
        h2 { border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 0; }
        .item { display: flex; justify-content: space-between; border-bottom: 1px solid #f0f0f0; padding: 5px 0; }
        .total { font-weight: bold; font-size: 1.2em; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Konfirmasi Pesanan</h1>
        <div><a href="keranjang.php">Kembali ke Keranjang</a></div>
    </div>

    <form action="proses_order.php" method="POST">
        <div class="checkout-container">
            <div>
                <div class="summary">
                    <h2>Ringkasan Pesanan</h2>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="item">
                            <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="total">
                        Total: Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?>
                        <input type="hidden" name="total_amount" value="<?php echo $total_belanja; ?>">
                    </div>
                </div>

                <br>

                <div class="payment">
                    <h2>Metode Pembayaran</h2>
                    <p>(Sesuai dokumen: Prepaid/Postpaid)</p>
                    <div>
                        <input type="radio" id="prepaid" name="payment_method" value="Prepaid (Credit Card/PayPal)" checked required>
                        <label for="prepaid">Prepaid (Credit Card/PayPal)</label>
                    </div>
                    <div>
                        <input type="radio" id="postpaid" name="payment_method" value="Postpaid (Bayar di Tempat)">
                        <label for="postpaid">Postpaid (Bayar di Tempat / COD)</label>
                    </div>
                </div>
            </div>

            <div>
                <div class="shipping">
                    <h2>Alamat Pengiriman</h2>
                    <p>
                        <strong><?php echo htmlspecialchars($username); ?></strong><br>
                        <?php echo htmlspecialchars($user['contact_no']); ?><br>
                        <?php echo htmlspecialchars($user['address']); ?><br>
                        <?php echo htmlspecialchars($user['city']); ?>
                    </p>
                    <a href="#">Ubah Alamat</a> <input type="hidden" name="shipping_address" value="<?php echo htmlspecialchars($alamat_pengiriman); ?>">
                </div>
                
                <br>
                
                <button type="submit" style="width: 100%; padding: 15px; font-size: 1.2em; background-color: #28a745; color: white; border: none; cursor: pointer;">
                    Bayar & Buat Pesanan
                </button>
            </div>
        </div>
    </form>

</body>
</html>