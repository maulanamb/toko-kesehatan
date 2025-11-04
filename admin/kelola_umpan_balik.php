<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Umpan Balik - Admin Panel</title>
    
    <style>
        /* [CSS yang sama dengan file admin lainnya] */
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
        td.komentar { max-width: 400px; word-wrap: break-word; line-height: 1.5; }
        .rating { color: #f0ad4e; font-size: 1.2em; }
        
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
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li> </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Umpan Balik (Review)</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <?php
        if(isset($_GET['status'])) {
            if($_GET['status'] == 'hapus_sukses') {
                echo "<div class='alert alert-sukses'>Umpan balik berhasil dihapus.</div>";
            } else if ($_GET['status'] == 'hapus_gagal') {
                echo "<div class='alert alert-gagal'>Gagal menghapus umpan balik.</div>";
            }
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Order ID</th>
                    <th>Pelanggan</th>
                    <th>Rating</th>
                    <th>Komentar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Kita JOIN dengan tabel 'users' untuk mendapatkan nama pelanggan
                $sql = "SELECT f.id, f.order_id, f.rating, f.komentar, f.tanggal_kirim, u.username 
                        FROM feedback f
                        JOIN users u ON f.user_id = u.user_id
                        ORDER BY f.tanggal_kirim DESC";
                
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_kirim'])); ?></td>
                            <td><a href="detail_pesanan_admin.php?order_id=<?php echo $row['order_id']; ?>">#<?php echo $row['order_id']; ?></a></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="rating">
                                <?php 
                                // Loop untuk menampilkan bintang
                                echo str_repeat('★', $row['rating']); // Bintang penuh
                                echo str_repeat('☆', 5 - $row['rating']); // Bintang kosong
                                ?>
                            </td>
                            <td class="komentar"><?php echo nl2br(htmlspecialchars($row['komentar'] ?? 'Tidak ada komentar.')); ?></td>
                            <td>
                                <a href="hapus_umpan_balik.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus umpan balik ini?');" style="color: red;">Hapus</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>Belum ada umpan balik yang masuk.</td></tr>";
                }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>