<?php
session_start();
require_once 'koneksi.php'; // Pastikan $conn

$pesan_error = "";
$pesan_sukses = "";

// 1. Logika saat form DISIMPAN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $pesan = $conn->real_escape_string($_POST['pesan']);

    // Validasi sederhana
    if (!empty($nama) && !empty($pesan)) {
        $sql = "INSERT INTO buku_tamu (nama, email, pesan) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama, $email, $pesan);
        
        if ($stmt->execute()) {
            $pesan_sukses = "Terima kasih! Pesan Anda telah terkirim.";
        } else {
            $pesan_error = "Maaf, terjadi kesalahan saat mengirim pesan: " . $conn->error;
        }
        $stmt->close();
    } else {
        $pesan_error = "Nama dan Pesan tidak boleh kosong.";
    }
}

// 2. Logika untuk MENAMPILKAN pesan yang ada
$sql_get = "SELECT nama, pesan, tanggal_kirim FROM buku_tamu ORDER BY tanggal_kirim DESC LIMIT 20"; // Ambil 20 terbaru
$result_pesan = $conn->query($sql_get);
$pesan_list = $result_pesan->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - Toko Kesehatan</title>
    
    <style>
        body { 
            font-family: sans-serif; 
            margin: 0; 
            background-color: #f4f4f4; 
        }
        .header {
            background-color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }
        .header .nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        h1, h2 {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        /* Style Form */
        .form-container { padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="email"], .form-group textarea { 
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; 
        }
        .btn-submit { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background-color: #0056b3; }

        /* Style Notifikasi */
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-sukses { background-color: #d4edda; color: #155724; }
        .alert-gagal { background-color: #f8d7da; color: #721c24; }

        /* Style Daftar Pesan */
        .pesan-list { list-style: none; padding: 0; }
        .pesan-item {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        .pesan-item p { margin: 0; line-height: 1.6; }
        .pesan-item .meta {
            font-size: 0.9em;
            color: #555;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="index.php" class="logo">Toko Kesehatan</a>
        
        <nav class="nav">
            <a href="keranjang.php">Keranjang</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin/index.php">Dashboard Admin</a>
                <?php else: ?>
                    <a href="profil.php">Profil Saya</a>
                    <a href="riwayat_pesanan.php">Riwayat</a>
                <?php endif; ?>
                <a href="logout.php" style="color: red;">Logout</a>
            <?php else: ?>
                <a href="buku_tamu.php">Buku Tamu</a> <a href="login.php">Login</a>
                <a href="registrasi.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h1>Buku Tamu</h1>
        <p>Silakan tinggalkan pesan atau kesan Anda tentang toko kami.</p>

        <div class="form-container">
            <h2>Tulis Pesan Baru</h2>
            
            <?php 
            if (!empty($pesan_sukses)) echo "<div class='alert alert-sukses'>$pesan_sukses</div>";
            if (!empty($pesan_error)) echo "<div class='alert alert-gagal'>$pesan_error</div>";
            ?>

            <form action="buku_tamu.php" method="POST">
                <div class="form-group">
                    <label for="nama">Nama Anda:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Anda (Opsional):</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="pesan">Pesan Anda:</label>
                    <textarea id="pesan" name="pesan" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Kirim Pesan</button>
            </form>
        </div>

        <h2>Pesan Terbaru</h2>
        <ul class="pesan-list">
            <?php if (count($pesan_list) > 0): ?>
                <?php foreach ($pesan_list as $pesan): ?>
                    <li class="pesan-item">
                        <p><?php echo nl2br(htmlspecialchars($pesan['pesan'])); ?></p>
                        <div class="meta">
                            Oleh: <strong><?php echo htmlspecialchars($pesan['nama']); ?></strong>
                            pada <?php echo date('d M Y', strtotime($pesan['tanggal_kirim'])); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="text-align: center; color: #777;">Belum ada pesan. Jadilah yang pertama!</li>
            <?php endif; ?>
        </ul>

    </div>

</body>
</html>