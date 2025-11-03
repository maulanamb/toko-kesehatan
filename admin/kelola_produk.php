<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Panel</title>
    
    <style>
        /* [CSS Anda yang sudah ada di sini] */
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
        .btn {
            display: inline-block;
            padding: 8px 12px;
            margin: 20px 0;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover { background-color: #0056b3; }
        .product-img { max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px; }
        
        /* Style untuk Notifikasi */
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
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Produk</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <?php
        if(isset($_GET['status'])) {
            $status = $_GET['status'];
            if($status == 'sukses_tambah') {
                echo "<div class='alert alert-sukses'>Produk baru berhasil ditambahkan.</div>";
            } else if($status == 'edit_sukses') {
                echo "<div class='alert alert-sukses'>Produk berhasil diperbarui.</div>";
            } else if($status == 'hapus_sukses') {
                echo "<div class='alert alert-sukses'>Produk berhasil dihapus.</div>";
            } else if($status == 'id_tidak_valid' || $status == 'id_tidak_ditemukan') {
                echo "<div class='alert alert-info'>ID Produk tidak valid atau tidak ditemukan.</div>";
            } else if($status == 'hapus_gagal') {
                $error_msg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : 'Terjadi kesalahan.';
                if (strpos($error_msg, 'foreign key constraint') !== false) {
                    echo "<div class='alert alert-gagal'><strong>Gagal menghapus!</strong> Produk ini tidak bisa dihapus karena sudah ada dalam data pesanan (order_details).</div>";
                } else {
                    echo "<div class='alert alert-gagal'>Gagal menghapus produk: {$error_msg}</div>";
                }
            }
        }
        ?>
        <a href="tambah_produk.php" class="btn">Tambah Produk Baru</a>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Koneksi lagi jika diperlukan
                if (!isset($conn) || $conn->ping() === false) {
                    // Masukkan detail koneksi Anda di sini, atau panggil ulang file koneksi
                    // Contoh: require '../koneksi.php'; 
                    // Ini asumsi detail koneksi Anda, GANTI JIKA PERLU
                    $servername = "localhost";
                    $username = "root";
                    $password = ""; // Password Laragon biasanya kosong
                    $dbname = "db_tokokesehatan";
                    $conn = new mysqli($servername, $username, $password, $dbname);
                }

                $sql = "SELECT p.*, c.category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.category_id 
                        ORDER BY p.product_id DESC";
                
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    $nomor = 1;
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-img"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori'); ?></td>
                            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td>
                                <a href="edit_produk.php?id=<?php echo $row['product_id']; ?>">Edit</a> | 
                                <a href="hapus_produk.php?id=<?php echo $row['product_id']; ?>" onclick="return confirm('Peringatan: Menghapus produk mungkin gagal jika sudah ada di pesanan. Yakin ingin melanjutkan?');">Hapus</a>
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