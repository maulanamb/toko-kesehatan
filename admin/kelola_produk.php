<?php
// 1. Set variabel khusus halaman
$page_title = "Kelola Produk";

// 2. Panggil Satpam & Header
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; // Panggil koneksi
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/minilogo.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
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
        
        .btn-logout {
            background-color: #dc3545; color: white; padding: 8px 12px;
            text-decoration: none; border-radius: 5px; font-weight: bold;
        }
        .btn-logout:hover { background-color: #bb2d3b; color: white; }

        .table .btn-sm {
            margin: 2px;
        }
        .text-center {
            text-align: center !important;
        }
        
        /* Status untuk Produk Diarsip */
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

        /* Tombol Aksi */
        .btn-aksi {
            min-width: 75px; 
        }

        /* Notifikasi */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
        .alert-info { background-color: #fff3cd; color: #856404; }
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
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <a href="../logout.php" class="btn-logout">LOGOUT</a>
        </div>

        <?php
        if(isset($_GET['status'])) {
            $status = $_GET['status'];
            if($status == 'sukses_tambah') {
                echo "<div class='alert alert-sukses'>Produk baru berhasil ditambahkan.</div>";
            } else if($status == 'edit_sukses') {
                echo "<div class='alert alert-sukses'>Produk berhasil diperbarui.</div>";
            } else if($status == 'hapus_sukses') {
                echo "<div class='alert alert-sukses'>Produk berhasil diarsipkan.</div>";
            } else if($status == 'aktif_sukses') { // <-- TAMBAHAN BARU
                echo "<div class='alert alert-sukses'>Produk berhasil diaktifkan kembali.</div>";
            } else if($status == 'id_tidak_valid') {
                echo "<div class='alert alert-info'>ID Produk tidak valid.</div>";
            } else if($status == 'gagal') {
                echo "<div class='alert alert-gagal'>Proses gagal.</div>";
            } else if($status == 'hapus_gagal') {
                echo "<div class='alert alert-gagal'>Gagal menghapus/mengarsipkan produk.</div>";
            }
        }
        ?>
        <a href="tambah_produk.php" class="btn btn-primary">Tambah Produk Baru (Admin)</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Toko (Vendor)</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }
                
                $sql = "SELECT 
                            p.product_id, p.product_name, p.price, p.stock, p.image_url, p.status_produk,
                            c.category_name, 
                            t.nama_toko
                        FROM 
                            products p 
                        LEFT JOIN 
                            categories c ON p.category_id = c.category_id 
                        LEFT JOIN
                            toko t ON p.toko_id = t.toko_id
                        ORDER BY 
                            p.product_id DESC";
                
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($row['image_url'] ?? 'images/default.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-img">
                            </td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_toko'] ?? 'Toko Purnama (Admin)'); ?></td>
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
                    echo "<tr><td colspan='7' style='text-align: center;'>Belum ada data produk.</td></tr>";
                }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>