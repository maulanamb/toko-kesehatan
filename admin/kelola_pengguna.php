<?php
$page_title = "Kelola Pengguna";

require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 
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
            if($status == 'hapus_sukses') {
                echo "<div class='alert alert-sukses'>Pengguna berhasil dihapus.</div>";
            } else if($status == 'edit_sukses') {
                echo "<div class='alert alert-sukses'>Pengguna berhasil diperbarui.</div>";
            } else if ($status == 'hapus_gagal') {
                $error_msg = $_GET['error'] ?? '';
                if ($error_msg == 'self') {
                    echo "<div class='alert alert-gagal'><strong>Gagal!</strong> Anda tidak bisa menghapus akun Anda sendiri.</div>";
                } else if ($error_msg == 'superadmin') {
                    echo "<div class='alert alert-gagal'><strong>Gagal!</strong> Akun admin utama tidak boleh dihapus.</div>";
                } else if (strpos($error_msg, 'foreign key constraint') !== false) {
                    echo "<div class='alert alert-gagal'><strong>Gagal!</strong> Pengguna ini tidak bisa dihapus karena sudah memiliki data pesanan.</div>";
                } else {
                    echo "<div class='alert alert-gagal'>Gagal menghapus pengguna. Error: " . htmlspecialchars($error_msg) . "</div>";
                }
            } else if ($status == 'id_tidak_valid' || $status == 'id_tidak_ditemukan') {
                 echo "<div class='alert alert-info'>ID Pengguna tidak valid atau tidak ditemukan.</div>";
            }
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Role</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }

                $sql = "SELECT user_id, username, email, created_at, role FROM users ORDER BY created_at DESC";
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td class="text-center">
                                <a href="edit_pengguna.php?id=<?php echo $row['user_id']; ?>" class="btn btn-primary btn-sm m-1">
                                    Edit
                                </a>
                                <a href="hapus_pengguna.php?id=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm m-1" onclick="return confirm('Peringatan: Menghapus pengguna mungkin gagal jika sudah memiliki data pesanan. Yakin ingin melanjutkan?');">
                                    Hapus
                                </a>
                            </td>
                            </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Belum ada pengguna yang terdaftar.</td></tr>";
                }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>