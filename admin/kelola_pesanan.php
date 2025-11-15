<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan</title>
    <link rel="icon" type="image/png" href="../images/minilogo.png"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* [CSS Admin Panel Anda yang sama] */
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #12b05f; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        
        /* CSS STATUS VENDOR */
        .status-vendor {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
        }
        .status-pending { background-color: #ffc107; color: #333; }
        .status-approved { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
        
        /* CSS TOMBOL LOGOUT */
        .btn-logout {
            background-color: #dc3545; 
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-logout:hover {
            background-color: #bb2d3b; 
            color: white;
        }

        /* ▼▼▼ 2. TAMBAHKAN CSS BANTUAN ▼▼▼ */
        .table .btn-sm {
            margin: 2px;
        }
        /* ▲▲▲ SELESAI ▲▲▲ */
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
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
            <li><a href="laporan.php">Laporan Bulanan</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Pesanan</h1>
            <a href="../logout.php" class="btn-logout">LOGOUT</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status Pesanan</th>
                    <th>Status Vendor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // QUERY SQL ANDA
                $sql = "SELECT 
                            o.order_id, 
                            o.order_date, 
                            o.total_amount, 
                            o.status, 
                            u.username,
                            SUM(CASE WHEN od.status_vendor = 'Pending' THEN 1 ELSE 0 END) AS count_pending,
                            SUM(CASE WHEN od.status_vendor = 'Rejected' THEN 1 ELSE 0 END) AS count_rejected
                        FROM 
                            orders o
                        LEFT JOIN 
                            users u ON o.user_id = u.user_id
                        LEFT JOIN 
                            order_details od ON o.order_id = od.order_id
                        WHERE
                            o.status NOT IN ('Dibatalkan')
                        GROUP BY
                            o.order_id, o.order_date, o.total_amount, o.status, u.username
                        ORDER BY 
                            o.order_date DESC";
                        
                $result = $conn->query($sql);
                
                if ($result) {
                    $orders = $result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $orders = [];
                    echo "<tr><td colspan='7'>Error: " . $conn->error . "</td></tr>";
                }
                $conn->close();
                
                if (count($orders) > 0):
                    foreach ($orders as $order):
                        
                        // LOGIKA STATUS VENDOR
                        if ($order['count_rejected'] > 0) {
                            $vendor_status = "Ditolak Vendor";
                            $vendor_class = "status-rejected";
                        } elseif ($order['count_pending'] > 0) {
                            $vendor_status = "Menunggu Vendor";
                            $vendor_class = "status-pending";
                        } else {
                            $vendor_status = "Disetujui Vendor";
                            $vendor_class = "status-approved";
                        }
                ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'User Dihapus'); ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            
                            <td>
                                <span class="status-vendor <?php echo $vendor_class; ?>">
                                    <?php echo $vendor_status; ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_pesanan_admin.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-sm">
                                    Detail & Update
                                </a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Belum ada pesanan yang masuk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
    </div>

</body>
</html>