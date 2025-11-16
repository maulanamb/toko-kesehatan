<?php
// 1. Set variabel khusus halaman
$page_title = "Kelola Pesanan Toko";

// 2. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <link rel="icon" type="image/png" href="../images/minilogo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { 
            width: 250px; 
            background: #0F4A86; /* <-- WARNA ADMIN */
            color: white; 
            min-height: 100vh; 
            padding: 20px; 
            box-sizing: border-box; 
        }
        .sidebar h2 { 
            border-bottom: 1px solid #555; /* <-- WARNA ADMIN */
            padding-bottom: 10px; 
        }
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
        
        /* Tombol Header (Biru) */
        .btn-header {
            background-color: #0d6efd; 
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-header:hover {
            background-color: #0b5ed7; 
            color: white;
        }

        /* Tombol Logout Sidebar (Merah) */
        .sidebar ul li.logout-item {
            margin: 20px 0 0 0;
            padding-top: 15px;
            border-top: 1px solid #555;
        }
        .sidebar ul li a.sidebar-logout {
            background-color: #dc3545; 
            color: white !important;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            display: block; 
            transition: background-color 0.2s;
        }
        .sidebar ul li a.sidebar-logout:hover {
            background-color: #bb2d3b;
            color: white !important;
        }

        .table .btn-sm {
            margin: 2px;
        }
        .text-center {
            text-align: center !important;
        }
        
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }

        /* Status Item Vendor */
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        
        .btn-aksi {
            min-width: 75px; 
        }
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
            <li class="logout-item">
                <a href="../logout.php" class="sidebar-logout" onclick="return confirm('Anda yakin ingin logout?');">Logout</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <a href="../index.php" class="btn-header">Lihat Toko Publik</a>
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

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Detail Pesanan</th>
                    <th>Produk Dipesan</th>
                    <th class="text-center">Status Item</th>
                    <th class="text-center" style="width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }
                
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
                            <td class="text-center">
                                <strong class="status-<?php echo strtolower($row['status_vendor']); ?>">
                                    <?php echo htmlspecialchars($row['status_vendor']); ?>
                                </strong>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status_vendor'] == 'Pending'): ?>
                                    <a href="proses_pesanan_vendor.php?item_id=<?php echo $row['order_detail_id']; ?>&action=approve" class="btn btn-success btn-sm m-1 btn-aksi" onclick="return confirm('Anda yakin ingin MENERIMA item pesanan ini?');">
                                        Terima
                                    </a>
                                    <a href="proses_pesanan_vendor.php?item_id=<?php echo $row['order_detail_id']; ?>&action=reject" class="btn btn-danger btn-sm m-1 btn-aksi" onclick="return confirm('Anda yakin ingin MENOLAK item pesanan ini? Stok akan dikembalikan.');">
                                        Tolak
                                    </a>
                                <?php else: ?>
                                    (Sudah diproses)
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>