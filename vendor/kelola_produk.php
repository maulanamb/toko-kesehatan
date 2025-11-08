<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - <?php echo htmlspecialchars($nama_toko_vendor); ?></title>
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #34495e; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .sidebar ul li a:hover { color: #1abc9c; }
        .content { flex: 1; padding: 20px; background-color: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; background: white; padding: 15px; margin: -20px -20px 20px -20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn-tambah { display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .product-img { max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px; }
        
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
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
            <li><hr style="border-color: #34495e;"></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Kelola Produk Anda</h1>
            <a href="../index.php" target="_blank">Lihat Toko Publik</a>
        </div>

        <?php
        if(isset($_GET['status'])) {
            $status = $_GET['status'];
            if($status == 'sukses_tambah') echo "<div class='alert alert-sukses'>Produk baru berhasil ditambahkan.</div>";
            if($status == 'edit_sukses') echo "<div class='alert alert-sukses'>Produk berhasil diperbarui.</div>";
            if($status == 'hapus_sukses') echo "<div class='alert alert-sukses'>Produk berhasil dihapus.</div>";
            if($status == 'hapus_gagal') echo "<div class='alert alert-gagal'>Gagal menghapus produk.</div>";
        }
        ?>

        <a href="tambah_produk.php" class="btn-tambah">Tambah Produk Baru</a>

        <table>
            <thead>
                <tr>
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
                // Query JOIN untuk ambil nama kategori
                // PENTING: "WHERE p.toko_id = ?"
                $sql = "SELECT 
                            p.product_id, p.product_name, p.price, p.stock, p.image_url, 
                            c.category_name 
                        FROM 
                            products p 
                        LEFT JOIN 
                            categories c ON p.category_id = c.category_id 
                        WHERE 
                            p.toko_id = ?  -- Ini adalah filter utama!
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
                            <td><img src="../<?php echo htmlspecialchars($row['image_url']); ?>" alt="" class="product-img"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Tanpa Kategori'); ?></td>
                            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['stock']); ?></td>
                            <td>
                                <a href="edit_produk.php?id=<?php echo $row['product_id']; ?>">Edit</a> | 
                                <a href="hapus_produk.php?id=<?php echo $row['product_id']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?');" style="color:red;">Hapus</a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Anda belum memiliki produk. Silakan 'Tambah Produk Baru'.</td></tr>";
                }
                $stmt->close();
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>