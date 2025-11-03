<?php
session_start();
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

// 1. Logika saat form DISIMPAN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = (int)$_POST['category_id'];
    $category_name = $conn->real_escape_string($_POST['category_name']);

    if (!empty($category_name) && $category_id > 0) {
        $sql = "UPDATE categories SET category_name = '$category_name' WHERE category_id = $category_id";
        
        if ($conn->query($sql) === TRUE) {
            header("location: kelola_kategori.php?status=edit_sukses");
            exit();
        } else {
            $pesan_error = "Gagal memperbarui kategori: " . $conn->error;
        }
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong.";
    }
}

// 2. Logika saat halaman DIBUKA (GET)
// Ambil ID dari URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($category_id === 0) {
    header('location: manage_kategori.php?status=id_tidak_valid');
    exit();
}

// Ambil data lama dari database untuk ditampilkan di form
$sql = "SELECT category_name FROM categories WHERE category_id = $category_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nama_kategori_sekarang = $row['category_name'];
} else {
    // Jika ID tidak ditemukan
    header('location: manage_kategori.php?status=id_tidak_ditemukan');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - Admin Panel</title>
    
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        
        /* Style untuk form */
        .form-container { max-width: 500px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background-color: #1e7e34; }
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
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Edit Kategori</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='error'>$pesan_error</div>";
            }
            ?>

            <form action="edit_kategori.php" method="POST">
                <div class="form-group">
                    <label for="category_name">Nama Kategori:</label>
                    <input type="text" id="category_name" name="category_name" value="<?php echo htmlspecialchars($nama_kategori_sekarang); ?>" required>
                </div>
                
                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </form>

            <a href="kelola_kategori.php" class="btn-kembali">&laquo; Kembali ke Manajemen Kategori</a>
        </div>
    </div>

</body>
</html>