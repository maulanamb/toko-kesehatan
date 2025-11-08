<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Toko (Vendor) - Admin Panel</title>
    
    <style>
        /* [CSS Anda yang sama] */
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        .btn-detail { color: #007bff; text-decoration: none; font-weight: bold; }
        
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
            <li><a href="manage_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Toko (Vendor)</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <?php
        if(isset($_GET['status'])) {
            if($_GET['status'] == 'approve_sukses') {
                echo "<div class='alert alert-sukses'>Toko berhasil disetujui.</div>";
            } else if ($_GET['status'] == 'reject_sukses') {
                echo "<div class='alert alert-sukses'>Toko berhasil ditolak.</div>";
            } else if ($_GET['status'] == 'gagal') {
                echo "<div class='alert alert-gagal'>Proses gagal: " . htmlspecialchars($_GET['error'] ?? '') . "</div>";
            }
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID Toko</th>
                    <th>Nama Toko</th>
                    <th>Pemilik (User)</th>
                    <th>Tanggal Daftar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT t.toko_id, t.nama_toko, t.status, t.tanggal_daftar, u.username 
                        FROM toko t
                        JOIN users u ON t.user_id = u.user_id
                        ORDER BY t.tanggal_daftar DESC";
                
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $row['toko_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_toko']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_daftar'])); ?></td>
                            <td>
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_toko.php?id=<?php echo $row['toko_id']; ?>" class="btn-detail">
                                    Lihat Detail & Tindaki
                                </a>
                                </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Belum ada toko yang mendaftar.</td></tr>";
                }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>