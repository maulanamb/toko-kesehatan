<?php
session_start(); 

require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori_baru = $conn->real_escape_string($_POST['nama_kategori']);

    if (!empty($nama_kategori_baru)) {
        $query = "INSERT INTO categories (category_name) VALUES ('$nama_kategori_baru')";
        
        if ($conn->query($query)) {
            header("location: kelola_kategori.php?status=sukses_tambah");
            exit(); 
        } else {
            $pesan_error = "Gagal menambahkan kategori: " . $conn->error;
        }
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong.";
    }
}
// Tidak perlu $conn->close() di sini karena akan terjadi header redirect
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori - Admin Panel</title>
    
    <style>
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        
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
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Tambah Kategori Baru</h1>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='error'>$pesan_error</div>";
            }
            ?>

            <form action="tambah_kategori.php" method="POST">
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori:</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" required>
                </div>
                <button type="submit" class="btn-submit">Simpan Kategori</button>
            </form>

            <a href="kelola_kategori.php" class="btn-kembali">&laquo; Kembali ke Manajemen Kategori</a>
        </div>
    </div>

</body>
</html>