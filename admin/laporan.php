<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn

$bulan_dipilih = $_POST['bulan'] ?? date('m');
$tahun_dipilih = $_POST['tahun'] ?? date('Y');

$tanggal_awal = "$tahun_dipilih-$bulan_dipilih-01 00:00:00";
$tanggal_akhir = date("Y-m-t 23:59:59", strtotime($tanggal_awal));

// --- 1. Query untuk KPI ---
$sql_kpi = "SELECT 
                SUM(total_amount) as total_pendapatan, 
                COUNT(order_id) as jumlah_pesanan
            FROM orders
            WHERE order_date BETWEEN ? AND ?
            AND status != 'Dibatalkan'";
            
$stmt_kpi = $conn->prepare($sql_kpi);
$stmt_kpi->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$stmt_kpi->execute();
$result_kpi = $stmt_kpi->get_result()->fetch_assoc();
$stmt_kpi->close();

$total_pendapatan = $result_kpi['total_pendapatan'] ?? 0;
$jumlah_pesanan = $result_kpi['jumlah_pesanan'] ?? 0;

// --- 2. Query untuk Produk Terjual (BUKAN HANYA 10) ---
// ▼▼▼ PERUBAHAN DI SINI: Menghapus LIMIT 10 ▼▼▼
$sql_produk = "SELECT 
                   p.product_name, 
                   SUM(od.quantity) as total_terjual
               FROM order_details od
               JOIN products p ON od.product_id = p.product_id
               JOIN orders o ON od.order_id = o.order_id
               WHERE o.order_date BETWEEN ? AND ?
               AND o.status != 'Dibatalkan'
               GROUP BY p.product_id, p.product_name
               ORDER BY total_terjual DESC"; // Tetap diurutkan
// ▲▲▲ SELESAI ▲▲▲

$stmt_produk = $conn->prepare($sql_produk);
$stmt_produk->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$stmt_produk->execute();
$produk_terlaris = $stmt_produk->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_produk->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan - Admin Panel</title>
    
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
        
        .filter-form { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .filter-form select, .filter-form input { padding: 8px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .filter-form button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-download { padding: 8px 15px; background: #198754; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; margin-left: 10px; }

        .kpi-container { display: flex; gap: 20px; margin-bottom: 20px; }
        .kpi-card { flex: 1; background: white; border: 1px solid #ccc; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .kpi-card h3 { margin-top: 0; font-size: 1.2em; color: #555; }
        .kpi-card .nilai { font-size: 2em; font-weight: bold; color: #007bff; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        
        /* ▼▼▼ CSS BARU UNTUK TOTAL DI BAWAH ▼▼▼ */
        .total-container {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .total-container h3 {
            margin-top: 0;
            font-size: 1.5em;
            color: #555;
        }
        .total-container .nilai-total {
            font-size: 2.2em; /* Font sedikit lebih besar */
            font-weight: bold;
            color: #28a745; /* Warna hijau */
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
            <h1>Laporan Bulanan</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <form action="laporan.php" method="POST" class="filter-form">
            <label for="bulan">Pilih Bulan:</label>
            <select name="bulan" id="bulan">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $nama_bulan = date('F', mktime(0, 0, 0, $m, 1));
                    $selected = ($m == $bulan_dipilih) ? 'selected' : '';
                    echo "<option value=\"$m\" $selected>$nama_bulan</option>";
                }
                ?>
            </select>
            
            <label for="tahun">Pilih Tahun:</label>
            <input type="number" name="tahun" id="tahun" value="<?php echo $tahun_dipilih; ?>" min="2020" max="<?php echo date('Y'); ?>">
            
            <button type="submit">Tampilkan Laporan</button>
            <a href="laporan_download.php?bulan=<?php echo $bulan_dipilih; ?>&tahun=<?php echo $tahun_dipilih; ?>" class="btn-download" target="_blank">Download PDF</a>
        </form>

        <h2>Ringkasan untuk <?php echo date('F Y', strtotime($tanggal_awal)); ?></h2>

        <div class="kpi-container">
            <div class="kpi-card">
                <h3>Jumlah Pesanan Berhasil</h3>
                <div class="nilai"><?php echo $jumlah_pesanan; ?></div>
            </div>
        </div>
        <h3>Produk yang Terjual di Bulan Ini</h3>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk</th>
                    <th>Total Terjual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($produk_terlaris) > 0): ?>
                    <?php $nomor = 1; ?>
                    <?php foreach ($produk_terlaris as $produk): ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td><?php echo htmlspecialchars($produk['product_name']); ?></td>
                            <td><?php echo $produk['total_terjual']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">Tidak ada penjualan produk di bulan ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="total-container">
            <h3>Total Pendapatan</h3>
            <div class="nilai-total">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
        </div>
        </div>

</body>
</html>