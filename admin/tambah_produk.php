<?php
session_start();

$page_title = "Tambah Produk Baru";

require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    $category_id = (int) $_POST['category_id'];
    
    // Kode produk unik untuk Admin
    $product_code = "SKU-ADMIN-" . strtoupper(uniqid()); 
    
    $image_url = ""; 
    
    // PROSES UPLOAD GAMBAR
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = basename($_FILES['gambar']['name']); 
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $new_file_name = "prod_admin_" . uniqid() . '.' . $file_ext;
        $upload_dir = '../images/products/'; 
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $image_url = 'images/products/' . $new_file_name; 
        } else {
            $pesan_error = "Gagal mengupload gambar.";
        }
    } else if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $pesan_error = "Terjadi kesalahan saat upload file: " . $_FILES['gambar']['error'];
    }
    
    if (empty($pesan_error)) {
        
        // Produk yang dibuat Admin, 'toko_id' di-set ke NULL
        $query = "INSERT INTO products (product_code, product_name, description, price, stock, category_id, image_url, toko_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, NULL)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssdiis", $product_code, $nama_produk, $deskripsi, $harga, $stok, $category_id, $image_url);

        if ($stmt->execute()) {
            header("location: kelola_produk.php?status=sukses_tambah");
            exit();
        } else {
            $pesan_error = "Gagal menambahkan produk ke database: " . $stmt->error;
        }
        $stmt->close();
    }
}

$category_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$category_result = $conn->query($category_query);

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
            max-width: 700px;
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

            <form action="tambah_produk.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk:</label>
                    <input type="text" id="nama_produk" name="nama_produk" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Kategori:</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        if ($category_result && $category_result->num_rows > 0) {
                            while($category = $category_result->fetch_assoc()) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga (Rp):</label>
                    <input type="number" id="harga" name="harga" class="form-control" step="1000" min="0" required>
                </div>

                <div class="mb-3">
                    <label for="stok" class="form-label">Stok:</label>
                    <input type="number" id="stok" name="stok" class="form-control" min="0" required>
                </div>
                
                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Produk:</label>
                    <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
            </form>

            <a href="kelola_produk.php" class="btn btn-secondary mt-3">Kembali ke Kelola Produk</a>
        </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>