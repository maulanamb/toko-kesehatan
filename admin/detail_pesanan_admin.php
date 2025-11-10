<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn

$pesan_error = "";
$pesan_sukses = "";

// 1. Ambil ID dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: kelola_pesanan.php?status=id_tidak_valid');
    exit();
}

// 2. Logika untuk UPDATE STATUS (Hanya jika di-POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status_baru = $conn->real_escape_string($_POST['status_pesanan']);
    
    // Nanti kita akan tambahkan validasi di sini
    
    $sql_update = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status_baru, $order_id);
    
    if ($stmt_update->execute()) {
        $pesan_sukses = "Status pesanan utama berhasil diperbarui.";
    } else {
        $pesan_error = "Gagal memperbarui status: " . $stmt_update->error;
    }
    $stmt_update->close();
}


// 3. Ambil data pesanan utama (JOIN dengan user)
$sql_order = "SELECT o.*, u.username, u.email, u.contact_no 
            FROM orders o
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.order_id = ?";
                
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows > 0) {
    $order = $result_order->fetch_assoc();
} else {
    header('location: kelola_pesanan.php?status=id_tidak_ditemukan');
    exit();
}
$stmt_order->close();

// 4. Ambil data item-item pesanan (JOIN dengan produk DAN toko)
$sql_items = "SELECT 
                od.quantity, 
                od.status_vendor,
                p.product_name, 
                p.image_url,
                p.price,
                t.nama_toko
            FROM 
                order_details od 
            JOIN 
                products p ON od.product_id = p.product_id 
            LEFT JOIN 
                toko t ON p.toko_id = t.toko_id -- LEFT JOIN jika ada produk admin (toko_id = NULL)
            WHERE 
                od.order_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
$conn->close();

// 5. Logika Pengecekan Status Vendor
$semua_item_disetujui = true;
$ada_item_ditolak = false;
foreach ($items as $item) {
    if ($item['status_vendor'] == 'Pending') {
        $semua_item_disetujui = false; // Masih ada yang nunggu
    }
    if ($item['status_vendor'] == 'Rejected') {
        $ada_item_ditolak = true; // Ada yang ditolak
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Admin Panel</title>
    
    <style>
        /* [CSS Admin Panel Anda yang sama] */
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
        .alert-info { background-color: #fff3cd; color: #856404; }

        /* Status Vendor */
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="manage_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
            <li><a href="laporan.php">Laporan Bulanan</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Detail Pesanan #<?php echo htmlspecialchars($order['order_id']); ?></h1>
            <a href="kelola_pesanan.php" class="btn-kembali">&laquo; Kembali ke Daftar Pesanan</a>
        </div>

        <?php
        if (!empty($pesan_sukses)) echo "<div class='alert alert-sukses'>$pesan_sukses</div>";
        if (!empty($pesan_error)) echo "<div class='alert alert-gagal'>$pesan_error</div>";
        ?>

        <div class="update-status">
            <h3>Update Status Pesanan Utama</h3>
            
            <?php if (!$semua_item_disetujui): // JIKA ADA ITEM PENDING/REJECTED ?>
                <div class="alert alert-info">
                    <strong>Tindakan Dibutuhkan:</strong> Anda baru bisa mengubah status pesanan ini menjadi "Dikirim" atau "Selesai" setelah semua item disetujui oleh vendor terkait.
                    <?php if ($ada_item_ditolak) echo "<br><strong>PERINGATAN:</strong> Ada item yang DITOLAK oleh vendor. Anda mungkin perlu menghubungi pelanggan."; ?>
                </div>
            <?php endif; ?>

            <form action="detail_pesanan_admin.php?order_id=<?php echo $order_id; ?>" method="POST">
                <div class="form-group">
                    <label for="status_pesanan">Status Saat Ini:</label>
                    <select name="status_pesanan" id="status_pesanan">
                        <option value="Paid" <?php echo ($order['status'] == 'Paid') ? 'selected' : ''; ?>>Paid (Menunggu Persetujuan Vendor)</option>
                        <option value="Diproses" <?php echo ($order['status'] == 'Diproses') ? 'selected' : ''; ?>>Diproses (Semua Vendor Setuju)</option>
                        <option value="Dikirim" <?php echo ($order['status'] == 'Dikirim') ? 'selected' : ''; ?> 
                            <?php if (!$semua_item_disetujui) echo 'disabled'; // <-- Kunci Logika ?>
                        >Dikirim</option>
                        <option value="Selesai" <?php echo ($order['status'] == 'Selesai') ? 'selected' : ''; ?>
                            <?php if (!$semua_item_disetujui) echo 'disabled'; // <-- Kunci Logika ?>
                        >Selesai</option>
                        <option value="Dibatalkan" <?php echo ($order['status'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn-submit">Update Status</button>
            </form>
        </div>

        <div class="order-details">
            <h3>Detail Pelanggan & Pengiriman</h3>
            <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>No. Kontak:</strong> <?php echo htmlspecialchars($order['contact_no'] ?? '-'); ?></p>
            <p><strong>Alamat Kirim:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'Alamat tidak tersedia')); ?></p>
        </div>

        <div class="order-items">
            <h3>Item Produk dalam Pesanan Ini</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penjual (Vendor)</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Status Vendor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="" class="item-img">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['nama_toko'] ?? 'Toko Utama (Admin)'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                <td>
                                    <strong class="status-<?php echo strtolower($item['status_vendor']); ?>">
                                        <?php echo htmlspecialchars($item['status_vendor']); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Tidak ada item dalam pesanan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>