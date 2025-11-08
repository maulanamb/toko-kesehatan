<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn
$pesan_error = "";

// 2. Ambil ID Produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header('location: kelola_produk.php?status=id_tidak_valid');
    exit();
}

// 3. Logika saat form DISIMPAN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data form
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    $category_id = (int) $_POST['category_id'];
    $gambar_lama = $conn->real_escape_string($_POST['gambar_lama']);
    
    $image_url_baru = $gambar_lama; 

    // PROSES UPLOAD GAMBAR BARU (JIKA ADA)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = basename($_FILES['gambar']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = "prod_toko" . $toko_id_vendor . "_" . uniqid() . '.' . $file_ext;
        $upload_dir = '../images/products/'; 
        
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $image_url_baru = 'images/products/' . $new_file_name;
            if (!empty($gambar_lama) && file_exists('../' . $gambar_lama)) {
                unlink('../' . $gambar_lama);
            }
        } else {
            $pesan_error = "Gagal mengupload gambar baru.";
        }
    }
    
    if (empty($pesan_error)) {
        // Query UPDATE
        // PENTING: Tambahkan "AND toko_id = ?" di WHERE
        $sql_update = "UPDATE products SET 
                        product_name = ?,
                        description = ?,
                        price = ?,
                        stock = ?,
                        category_id = ?,
                        image_url = ?
                    WHERE 
                        product_id = ? AND toko_id = ?"; // <-- Keamanan
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssdiisii", 
            $nama_produk, 
            $deskripsi, 
            $harga, 
            $stok, 
            $category_id, 
            $image_url_baru, 
            $product_id, 
            $toko_id_vendor // Pastikan hanya bisa update produk sendiri
        );

        if ($stmt->execute()) {
            header("location: kelola_produk.php?status=edit_sukses");
            exit();
        } else {
            $pesan_error = "Gagal memperbarui produk: " . $stmt->error;
        }
        $stmt->close();
    }
}

// 4. Logika saat halaman DIBUKA (GET)
// Ambil data produk HANYA JIKA ID produk DAN toko_id cocok
$sql_get = "SELECT * FROM products WHERE product_id = ? AND toko_id = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("ii", $product_id, $toko_id_vendor);
$stmt_get->execute();
$result = $stmt_get->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    // Jika produk tidak ditemukan ATAU bukan milik vendor ini
    header('location: kelola_produk.php?status=gagal&error=Produk tidak ditemukan');
    exit();
}
$stmt_get->close();

// Ambil data kategori untuk dropdown
$category_query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$category_result = $conn->query($category_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Vendor Panel</title>
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
        
        .form-container { max-width: 700px; padding: 20px; border-radius: 5px; background-color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; 
        }
        .btn-submit { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-kembali { display: inline-block; margin-top: 15px; color: #555; text-decoration: none; }
        .error { color: red; background-color: #fdd; padding: 10px; border: 1px solid red; margin-bottom: 15px; }
        .current-img-preview { max-width: 100px; max-height: 100px; border-radius: 4px; border: 1px solid #ddd; padding: 5px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Vendor Panel</h2>
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
            <h1>Edit Produk: <?php echo htmlspecialchars($product['product_name']); ?></h1>
        </div>

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='error'>$pesan_error</div>";
            }
            ?>

            <form action="edit_produk.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="nama_produk">Nama Produk:</label>
                    <input type="text" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategori:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php 
                        if ($category_result && $category_result->num_rows > 0) {
                            while($category = $category_result->fetch_assoc()) {
                                $selected = ($category['category_id'] == $product['category_id']) ? 'selected' : '';
                                echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga (Rp):</label>
                    <input type="number" id="harga" name="harga" step="1000" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Gambar Saat Ini:</label>
                    <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Gambar Saat Ini" class="current-img-preview">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="gambar">Upload Gambar Baru (Opsional):</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <small>Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
                
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </form>

            <a href="kelola_produk.php" class="btn-kembali">&laquo; Kembali ke Kelola Produk</a>
        </div>
    </div>

</body>
</html>