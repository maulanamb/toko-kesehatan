<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .sidebar ul li a:hover { color: #1abc9c; }
        .content { flex: 1; padding: 20px; background-color: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; background: white; padding: 15px; margin: -20px -20px 20px -20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        
        .btn-approve { color: green; text-decoration: none; font-weight: bold; }
        .btn-reject { color: red; text-decoration: none; margin-left: 10px; font-weight: bold; }

        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Vendor Panel</h2>
        <p>Toko: <strong><?php echo htmlspecialchars($nama_toko_vendor); ?></strong></p>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan Toko</a></li>
            <li><hr style="border-color: #34495e;"></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Pesanan Toko Anda</h1>
        </div>

        <p>Halaman ini menampilkan setiap item dari toko Anda yang dipesan oleh pelanggan. Anda harus menyetujui item untuk diproses oleh Admin.</p>
        
        <?php
        if(isset($_GET['status'])) {
            if($_GET['status'] == 'approve_sukses') {
                echo "<div class='alert alert-sukses'>Item berhasil disetujui.</div>";
            } else if ($_GET['status'] == 'reject_sukses') {
                echo "<div class='alert alert-sukses'>Item berhasil ditolak dan stok telah dikembalikan.</div>";
            } else if ($_GET['status'] == 'gagal') {
                echo "<div class='alert alert-gagal'>Proses gagal: " . htmlspecialchars($_GET['error'] ?? '') . "</div>";
            }
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>Detail Pesanan</th>
                    <th>Produk Dipesan</th>
                    <th>Status Item</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN yang mengambil SEMUA item milik vendor ini
                $sql = "SELECT 
                            o.order_id, 
                            o.order_date,
                            o.shipping_address,
                            p.product_name,
                            od.quantity,
                            od.order_detail_id,
                            od.status_vendor,
                            u.username
                        FROM 
                            order_details od
                        JOIN 
                            products p ON od.product_id = p.product_id
                        JOIN 
                            orders o ON od.order_id = o.order_id
                        JOIN
                            users u ON o.user_id = u.user_id
                        WHERE 
                            p.toko_id = ?
                            AND o.status NOT IN ('Dibatalkan')
                        ORDER BY 
                            CASE od.status_vendor
                                WHEN 'Pending' THEN 1
                                WHEN 'Approved' THEN 2
                                WHEN 'Rejected' THEN 3
                            END, o.order_date DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $toko_id_vendor);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td>
                                <strong>Order ID: #<?php echo $row['order_id']; ?></strong><br>
                                Tanggal: <?php echo date('d M Y', strtotime($row['order_date'])); ?><br>
                                Pelanggan: <?php echo htmlspecialchars($row['username']); ?><br>
                                Alamat: <?php echo nl2br(htmlspecialchars($row['shipping_address'])); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['product_name']); ?><br>
                                Jumlah: <strong><?php echo $row['quantity']; ?></strong>
                            </td>
                            <td>
                                <strong class="status-<?php echo strtolower($row['status_vendor']); ?>">
                                    <?php echo htmlspecialchars($row['status_vendor']); ?>
                                </strong>
                            </td>
                            <td>
                                <?php if ($row['status_vendor'] == 'Pending'): ?>
                                    <a href="proses_pesanan_vendor.php?item_id=<?php echo $row['order_detail_id']; ?>&action=approve" class="btn-approve" onclick="return confirm('Anda yakin ingin MENERIMA item pesanan ini?');">Terima</a>
                                    <br><br>
                                    <a href="proses_pesanan_vendor.php?item_id=<?php echo $row['order_detail_id']; ?>&action=reject" class="btn-reject" onclick="return confirm('Anda yakin ingin MENOLAK item pesanan ini? Stok akan dikembalikan.');">Tolak</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Belum ada produk Anda yang dipesan.</td></tr>";
                }
                $stmt->close();
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>