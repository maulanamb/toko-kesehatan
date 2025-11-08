<?php
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$toko_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($toko_id === 0) {
    header('location: kelola_toko.php');
    exit();
}

// Ambil data toko DAN data pemiliknya
// ▼▼▼ QUERY DIPERBARUI ▼▼▼
$sql = "SELECT t.*, u.username, u.email, u.contact_no as kontak_pemilik 
        FROM toko t 
        JOIN users u ON t.user_id = u.user_id 
        WHERE t.toko_id = ?";
// ▲▲▲ SELESAI ▲▲▲
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $toko_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('location: kelola_toko.php?status=gagal&error=Toko tidak ditemukan');
    exit();
}
$toko = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pendaftaran Toko - Admin Panel</title>
    <style>
        /* [CSS Anda yang sama] */
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }

        .detail-container { max-width: 700px; background: white; padding: 20px; border-radius: 5px; }
        .detail-container h2, .detail-container h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .detail-container p { line-height: 1.7; }
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        
        .action-buttons { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .btn-approve { display: inline-block; padding: 10px 15px; background: green; color: white; text-decoration: none; border-radius: 5px; }
        .btn-reject { display: inline-block; padding: 10px 15px; background: red; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="manage_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
            <li><a href="kelola_buku_tamu.php">Kelola Buku Tamu</a></li>
            <li><a href="kelola_umpan_balik.php">Kelola Umpan Balik</a></li>
            <li><a href="kelola_toko.php">Kelola Toko</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Detail Pendaftaran Toko</h1>
            <a href="kelola_toko.php">&laquo; Kembali ke Daftar Toko</a>
        </div>
        
        <div class="detail-container mt-4">
            <h2><?php echo htmlspecialchars($toko['nama_toko']); ?></h2>
            <p><strong>Status:</strong> <span class="status-<?php echo $toko['status']; ?>"><?php echo ucfirst($toko['status']); ?></span></p>
            <p><strong>Deskripsi Toko:</strong><br>
               <?php echo nl2br(htmlspecialchars($toko['deskripsi_toko'] ?? 'Tidak ada deskripsi.')); ?></p>
            
            <hr>
            <h3>Detail Alamat & Kontak Toko</h3>
            <p><strong>No. Telepon Toko:</strong> <?php echo htmlspecialchars($toko['no_telepon_toko'] ?? 'Tidak ada'); ?></p>
            <p><strong>Kota:</strong> <?php echo htmlspecialchars($toko['kota_toko'] ?? 'Tidak ada'); ?></p>
            <p><strong>Alamat Lengkap:</strong><br>
               <?php echo nl2br(htmlspecialchars($toko['alamat_toko'] ?? 'Tidak ada')); ?></p>
            <hr>
            <h3>Detail Pemilik Toko</h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($toko['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($toko['email']); ?></p>
            <p><strong>No. Kontak Pemilik:</strong> <?php echo htmlspecialchars($toko['kontak_pemilik'] ?? 'Tidak ada'); ?></p>
            
            <?php if ($toko['status'] == 'pending'): ?>
            <div class="action-buttons">
                <p>Tindakan:</p>
                <a href="proses_toko.php?id=<?php echo $toko['toko_id']; ?>&action=approve" class="btn-approve" onclick="return confirm('Anda yakin ingin MENYETUJUI toko ini?');">Setujui Toko</a>
                <a href="proses_toko.php?id=<?php echo $toko['toko_id']; ?>&action=reject" class="btn-reject" onclick="return confirm('Anda yakin ingin MENOLAK toko ini?');">Tolak Toko</a>
            </div>
            <?php endif; ?>
        </div>
        
    </div>

</body>
</html>