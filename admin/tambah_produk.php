<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";

// Cek jika form sudah di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data form
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    $category_id = (int) $_POST['category_id'];
    
    // ▼▼▼ PERUBAHAN 1: Buat Kode Produk Otomatis ▼▼▼
    $product_code = "SKU-" . strtoupper(uniqid()); 
    // ▲▲▲ SELESAI PERUBAHAN 1 ▲▲▲
    
    $image_url = ""; 
    
    // ===================================
    // PROSES UPLOAD GAMBAR
    // ===================================
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = basename($_FILES['gambar']['name']); 
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $new_file_name = "prod_" . uniqid() . '.' . $file_ext;
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
    // ===================================
    
    if (empty($pesan_error)) {
        
        // ▼▼▼ PERUBAHAN 2: Tambahkan 'product_code' ke Query INSERT ▼▼▼
        $query = "INSERT INTO products (product_code, product_name, description, price, stock, category_id, image_url) 
                  VALUES ('$product_code', '$nama_produk', '$deskripsi', $harga, $stok, $category_id, '$image_url')";
        // ▲▲▲ SELESAI PERUBAHAN 2 ▲▲▲
        
        if ($conn->query($query)) {
            header("location: kelola_produk.php?status=sukses_tambah");
            exit();
        } else {
            // Tampilkan error SQL jika gagal
            $pesan_error = "Gagal menambahkan produk ke database: " . $conn->error;
        }
    }
}

// Ambil data kategori untuk dropdown (SELECT box)
$category_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$category_result = $conn->query($category_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Admin Panel</title>
    
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
        .form-container { max-width: 700px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select { 
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; 
        }
        .btn-submit { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-kembali { display: inline-block; margin-top: 15px; color: #555; text-decoration: none; }
        .error { color: red; background-color: #fdd; padding: 10px; border: 1px solid red; margin-bottom: 15px; }
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
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Tambah Produk Baru</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='error'>$pesan_error</div>";
            }
            ?>

            <form action="tambah_produk.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_produk">Nama Produk:</label>
                    <input type="text" id="nama_produk" name="nama_produk" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategori:</label>
                    <select id="category_id" name="category_id" required>
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

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga (Rp):</label>
                    <input type="number" id="harga" name="harga" step="1000" min="0" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Produk:</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                </div>
                
                <button type="submit" class="btn-submit">Simpan Produk</button>
            </form>

            <a href="kelola_produk.php" class="btn-kembali">&laquo; Kembali ke Manajemen Produk</a>
        </div>
    </div>

</body>
</html>