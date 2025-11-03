<?php
// Selalu mulai session
session_start();

// 1. Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Sertakan file koneksi
require_once 'koneksi.php';

// 3. Ambil data pengguna dan ID-nya
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 4. Ambil data isi keranjang (JOIN dengan tabel products)
$sql = "SELECT 
            p.product_id, 
            p.product_code, 
            p.product_name, 
            p.price, 
            c.quantity
        FROM 
            cart_items c
        JOIN 
            products p ON c.product_id = p.product_id
        WHERE 
            c.user_id = ?";
            
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_belanja = 0; // Variabel untuk menghitung total

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Hitung subtotal per item
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $cart_items[] = $row;
        // Tambahkan subtotal ke total belanja
        $total_belanja += $row['subtotal'];
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: left;
        }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; }
        .text-right { text-align: right; }
        .input-qty { width: 60px; text-align: center; }
        .cart-actions { margin-top: 20px; display: flex; justify-content: space-between; }
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

    <h1>Keranjang Belanja</h1>

    <form action="keranjang_update.php" method="POST">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk dengan IDnya</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cart_items) > 0): ?>
                    <?php $nomor = 1; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td>
                                <?php echo htmlspecialchars($item['product_name']); ?> 
                                (<?php echo htmlspecialchars($item['product_code']); ?>)
                            </td>
                            
                            <td>
                                <input type="number" 
                                       class="input-qty" 
                                       name="quantity[<?php echo $item['product_id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="0">
                            </td>

                            <td class="text-right">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="text-right">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="keranjang_hapus.php?id=<?php echo $item['product_id']; ?>" onclick="return confirm('Hapus item ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr class="total-row">
                        <td colspan="4">Total belanja (termasuk pajak):</td>
                        <td class="text-right">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Keranjang belanja Anda kosong.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="cart-actions">
            <button type="submit" name="update_cart" style="padding: 10px 20px;">
                🔄 Update Keranjang
            </button>
            
            <a href="checkout.php" style="text-decoration: none;">
                <button type="button" style="padding: 10px 20px; font-size: 16px; background-color: #28a745; color: white; border: none; cursor: pointer;">
                    Lanjut ke Checkout
                </button>
            </a>
        </div>
    </form>
    </body>
</html>