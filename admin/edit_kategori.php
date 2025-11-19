<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$page_title = "Edit Kategori";

$pesan_error = "";
$pesan_sukses = "";
$kategori_nama = "";

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($category_id === 0) {
    header('location: kelola_kategori.php?status=id_tidak_valid');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori_baru = $conn->real_escape_string($_POST['nama_kategori']);
    
    if (!empty($nama_kategori_baru)) {
        $sql_update = "UPDATE categories SET category_name = ? WHERE category_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $nama_kategori_baru, $category_id);
        
        if ($stmt_update->execute()) {
            header("location: kelola_kategori.php?status=edit_sukses");
            exit();
        } else {
            $pesan_error = "Gagal memperbarui kategori: " . $conn->error;
        }
        $stmt_update->close();
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong.";
    }
}

$sql_get = "SELECT category_name FROM categories WHERE category_id = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $category_id);
$stmt_get->execute();
$result_get = $stmt_get->get_result();

if ($result_get->num_rows > 0) {
    $kategori = $result_get->fetch_assoc();
    $kategori_nama = $kategori['category_name'];
} else {
    header('location: kelola_kategori.php?status=id_tidak_ditemukan');
    exit();
}
$stmt_get->close();
$conn->close();
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
            ?>
            
            <form action="edit_kategori.php?id=<?php echo $category_id; ?>" method="POST">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori:</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="<?php echo htmlspecialchars($kategori_nama); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
            
            <a href="kelola_kategori.php" class="btn btn-secondary mt-3">Kembali ke Kelola Kategori</a>
        </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>