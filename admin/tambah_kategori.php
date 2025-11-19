<?php
session_start();

$page_title = "Tambah Kategori Baru";

require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori = $conn->real_escape_string($_POST['nama_kategori']);
    
    if (!empty($nama_kategori)) {
        $sql_cek = "SELECT category_id FROM categories WHERE category_name = ?";
        $stmt_cek = $conn->prepare($sql_cek);
        $stmt_cek->bind_param("s", $nama_kategori);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        
        if ($result_cek->num_rows > 0) {
            $pesan_error = "Nama kategori tersebut sudah ada.";
        } else {
            $sql_insert = "INSERT INTO categories (category_name) VALUES (?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("s", $nama_kategori);
            
            if ($stmt_insert->execute()) {
                $pesan_sukses = "Kategori baru berhasil ditambahkan. <a href='kelola_kategori.php'>Kembali ke daftar</a>.";
            } else {
                $pesan_error = "Gagal menyimpan ke database: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt_cek->close();
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong.";
    }
    $conn->close();
}
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
        
        .btn-logout {
            background-color: #dc3545; color: white; padding: 8px 12px;
            text-decoration: none; border-radius: 5px; font-weight: bold;
        }
        .btn-logout:hover { background-color: #bb2d3b; color: white; }

        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }

        /* Style untuk Form */
        .form-container {
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
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

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='alert alert-gagal'>$pesan_error</div>";
            }
            if (!empty($pesan_sukses)) {
                echo "<div class='alert alert-sukses'>$pesan_sukses</div>";
            }
            ?>
            
            <form action="tambah_kategori.php" method="POST">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori Baru:</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </form>
            
            <a href="kelola_kategori.php" class="btn btn-secondary mt-3">Kembali ke Kelola Kategori</a>
        </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>