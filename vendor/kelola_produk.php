<?php
// 1. Set variabel khusus halaman
$page_title = "Kelola Produk";

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
            background: #12b05f; /* 12b05f<-- WARNA ADMIN */
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

        /* CSS Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .table .btn-sm {
            margin: 2px;
        }
        .text-center {
            text-align: center !important;
        }

        /* Notifikasi */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
        
        /* CSS Khusus Halaman Ini */
        .status-diarsip {
            font-weight: bold;
            color: #777;
        }
        .product-img {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
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

        <?php
        if(isset($_GET['status'])) {
            $status = $_GET['status'];
            if($status == 'sukses_tambah') echo "<div class='alert alert-sukses'>Produk baru berhasil ditambahkan.</div>";
            if($status == 'edit_sukses') echo "<div class='alert alert-sukses'>Produk berhasil diperbarui.</div>";
            if($status == 'hapus_sukses') echo "<div class='alert alert-sukses'>Produk berhasil diarsipkan.</div>";
            if($status == 'aktif_sukses') echo "<div class='alert alert-sukses'>Produk berhasil diaktifkan kembali.</div>"; // <-- TAMBAHAN BARU
            if($status == 'gagal') echo "<div class='alert alert-gagal'>Proses gagal: " . htmlspecialchars($_GET['error'] ?? '') . "</div>";
        }
        ?>
        <a href="tambah_produk.php" class="btn btn-primary">Tambah Produk Baru</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }
                
                $sql = "SELECT 
                            p.product_id, p.product_name, p.price, p.stock, p.image_url, p.status_produk,
                            c.category_name 
                        FROM 
                            products p 
                        LEFT JOIN 
                            categories c ON p.category_id = c.category_id 
                        WHERE 
                            p.toko_id = ?
                        ORDER BY 
                            p.product_id DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $toko_id_vendor);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><img src="../<?php echo htmlspecialchars($row['image_url'] ?? 'images/default.png'); ?>" alt="" class="product-img"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori'); ?></td>
                            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td class="text-center">
                                <?php if ($row['status_produk'] == 'Diarsip'): ?>
                                    <span class="status-diarsip">Diarsip</span>
                                <?php else: ?>
                                    <span>Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="edit_produk.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm m-1 btn-aksi">
                                    Edit
                                </a>
                                
                                <?php if ($row['status_produk'] == 'Aktif'): ?>
                                    <a href="hapus_produk.php?id=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm m-1 btn-aksi" onclick="return confirm('Anda yakin ingin meng-arsipkan produk ini?');">
                                        Arsipkan
                                    </a>
                                <?php else: ?>
                                    <a href="aktifkan_produk.php?id=<?php echo $row['product_id']; ?>" class="btn btn-success btn-sm m-1 btn-aksi" onclick="return confirm('Anda yakin ingin meng-aktifkan kembali produk ini?');">
                                        Aktifkan
                                    </a>
                                <?php endif; ?>
                            </td>
                            </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>Anda belum memiliki produk. Silakan 'Tambah Produk Baru'.</td></tr>";
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