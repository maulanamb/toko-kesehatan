<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

// 1. Ambil ID dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: kelola_pesanan.php?status=id_tidak_valid');
    exit();
}

// 2. Logika untuk UPDATE STATUS jika form disubmit (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status_baru = $conn->real_escape_string($_POST['status_pesanan']);
    
    $sql_update = "UPDATE orders SET status = '$status_baru' WHERE order_id = $order_id";
    if ($conn->query($sql_update)) {
        $pesan_sukses = "Status pesanan berhasil diperbarui.";
    } else {
        $pesan_error = "Gagal memperbarui status: " . $conn->error;
    }
}


// 3. Ambil data pesanan utama (JOIN dengan user)
$sql_order = "SELECT 
                o.order_id, 
                o.order_date, 
                o.total_amount, 
                o.status, 
                u.username, 
                u.email,
                o.shipping_address -- Asumsi Anda punya kolom ini
            FROM 
                orders o
            JOIN 
                users u ON o.user_id = u.user_id 
            WHERE 
                o.order_id = $order_id";
                
$result_order = $conn->query($sql_order);

if ($result_order->num_rows > 0) {
    $order = $result_order->fetch_assoc();
} else {
    // Jika ID tidak ditemukan
    header('location: kelola_pesanan.php?status=id_tidak_ditemukan');
    exit();
}

// 4. Ambil data item-item pesanan (JOIN dengan produk)
// ▼▼▼ PERBAIKAN DI SINI ▼▼▼
$sql_items = "SELECT 
                od.quantity, 
                p.price,  -- Mengambil harga dari tabel 'products' (p), BUKAN 'order_details' (od)
                p.product_name, 
                p.image_url 
            FROM 
                order_details od 
            JOIN 
                products p ON od.product_id = p.product_id 
            WHERE 
                od.order_id = $order_id";
// ▲▲▲ SELESAI PERBAIKAN ▲▲▲

$result_items = $conn->query($sql_items);
$items = $result_items->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Admin Panel</title>
    
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        
        /* Style untuk detail pesanan */
        .order-details, .order-items, .update-status {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .order-details h3, .order-items h3, .update-status h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .order-details p { margin: 5px 0; line-height: 1.6; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .item-img { max-width: 50px; max-height: 50px; object-fit: cover; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select { width: 300px; padding: 8px; }
        .btn-submit { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }

        .btn-kembali { display: inline-block; margin-top: 15px; color: #555; text-decoration: none; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="kelola_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>
            <a href="../logout.php">Logout</a>
        </div>

        <a href="kelola_pesanan.php" class="btn-kembali">&laquo; Kembali ke Daftar Pesanan</a>

        <?php
        if (!empty($pesan_sukses)) echo "<div class='alert alert-sukses'>$pesan_sukses</div>";
        if (!empty($pesan_error)) echo "<div class='alert alert-gagal'>$pesan_error</div>";
        ?>

        <div class="update-status">
            <h3>Update Status Pesanan</h3>
            <form action="detail_pesanan_admin.php?order_id=<?php echo $order_id; ?>" method="POST">
                <div class="form-group">
                    <label for="status_pesanan">Status Saat Ini:</label>
                    <select name="status_pesanan" id="status_pesanan">
                        <option value="Menunggu Pembayaran" <?php echo ($order['status'] == 'Menunggu Pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                        <option value="Diproses" <?php echo ($order['status'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Dikirim" <?php echo ($order['status'] == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="Selesai" <?php echo ($order['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?php echo ($order['status'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn-submit">Update Status</button>
            </form>
        </div>

        <div class="order-details">
            <h3>Detail Pesanan</h3>
            <p><strong>Tanggal Pesan:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Status:</strong> <strong><?php echo htmlspecialchars($order['status']); ?></strong></p>
            <p><strong>Total Bayar:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
            <p><strong>Alamat Kirim:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'Alamat tidak tersedia')); ?></p>
            <hr>
            <h3>Detail Pelanggan</h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        </div>

        <div class="order-items">
            <h3>Item Produk</h3>
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-img"></td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Tidak ada item dalam pesanan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>