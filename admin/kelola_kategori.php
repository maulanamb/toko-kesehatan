<?php
session_start();

// 1. Set variabel khusus halaman
$page_title = "Kelola Kategori";

// 2. Panggil Satpam
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    <link rel="icon" type="image/png" href="../images/minilogo.png"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .btn-primary {
            display: inline-block;
            padding: 8px 12px;
            margin: 20px 0;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #0d6efd;
        }
        .btn-primary:hover { background-color: #0b5ed7; }

        .btn-logout {
            background-color: #dc3545; 
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-logout:hover {
            background-color: #bb2d3b; 
            color: white;
        }

        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }
        .alert-info { background-color: #fff3cd; color: #856404; }
        
        .table .btn-sm {
            margin: 2px;
        }
        .text-center {
            text-align: center !important; 
        }
        
        /* ▼▼▼ 1. TAMBAHKAN CSS INI ▼▼▼ */
        .btn-aksi {
            min-width: 75px; /* Atur lebar minimum tombol (sesuaikan 75px jika perlu) */
        }
        /* ▲▲▲ SELESAI ▲▲▲ */
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
        
        <?php
        // ... (Blok Notifikasi Anda) ...
        ?>

        <a href="tambah_kategori.php" class="btn btn-primary">Tambah Kategori Baru</a>

        <table class="table table-bordered"> 
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th class="text-center">Nama Kategori</th>
                    <th style="width: 180px;" class="text-center">Aksi</th> </tr>
            </thead>
            <tbody>
                <?php
                if (!isset($conn) || $conn->ping() === false) { require '../koneksi.php'; }
                
                $sql = "SELECT * FROM categories ORDER BY category_name ASC";
                $result = $conn->query($sql); 

                if ($result->num_rows > 0) {
                    $nomor = 1;
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($row['category_name']); ?></td> 
                            <td class="text-center">
                                <a href="edit_kategori.php?id=<?php echo $row['category_id']; ?>" class="btn btn-primary btn-sm m-1 btn-aksi">
                                    Edit
                                </a>
                                <a href="hapus_kategori.php?id=<?php echo $row['category_id']; ?>" class="btn btn-danger btn-sm m-1 btn-aksi" onclick="return confirm('Peringatan: Menghapus kategori mungkin gagal jika masih ada produk di dalamnya. Yakin ingin melanjutkan?');">
                                    Hapus
                                </a>
                                </td>
                        </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align: center;'>Belum ada data kategori.</td></tr>";
                }
                $conn->close(); 
                ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>