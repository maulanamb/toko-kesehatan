<?php
require_once 'cek_admin.php';
require_once '../koneksi.php'; 

$page_title = "Kelola Toko (Vendor)";
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
        .sidebar { width: 250px; background: #0F4A86; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
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
        
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-rejected { color: #dc3545; font-weight: bold; }
        
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
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }
                
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
                            <td class="text-center">
                                <a href="detail_toko.php?id=<?php echo $row['toko_id']; ?>" class="btn btn-primary btn-sm m-1">
                                    Detail & Tindaki
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