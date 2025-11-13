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
    <title>Buku Tamu</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .navbar-brand {
            padding-top: 0; /* Hapus padding-top default */
            padding-bottom: 0; /* Hapus padding-bottom default */
            margin-right: 0.5rem; /* Beri sedikit jarak dengan menu */
        }
        .navbar-brand img {
            height: 80px; /* Ukuran logo yang lebih terlihat */
            width: auto;
            vertical-align: middle; /* Pastikan sejajar dengan teks jika ada */
        }
    </style>
    </head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="buku_tamu.php">
                <img src="images/logo.png" alt="Toko Kesehatan Purnama Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="keranjang.php">Keranjang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="buku_tamu.php">Buku Tamu</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Buka Toko</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="registrasi.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="mb-4">Buku Tamu</h1>
                <p class="lead">Silakan tinggalkan pesan atau kesan Anda tentang toko kami.</p>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Tulis Pesan Baru</h2>
                    </div>
                    <div class="card-body">
                        <?php 
                        if (!empty($pesan_sukses)) echo "<div class='alert alert-success'>$pesan_sukses</div>";
                        if (!empty($pesan_error)) echo "<div class='alert alert-danger'>$pesan_error</div>";
                        ?>

                        <form action="buku_tamu.php" method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Anda:</label>
                                <input type="text" id="nama" name="nama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Anda (Opsional):</label>
                                <input type="email" id="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="pesan" class="form-label">Pesan Anda:</label>
                                <textarea id="pesan" name="pesan" class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                        </form>
                    </div>
                </div>

                <h2 class="h4 mb-3">Pesan Terbaru</h2>
                <ul class="list-group">
                    <?php if (count($pesan_list) > 0): ?>
                        <?php foreach ($pesan_list as $pesan): ?>
                            <li class="list-group-item mb-2 border-0 shadow-sm">
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($pesan['pesan'])); ?></p>
                                <small class="text-muted">
                                    Oleh: <strong><?php echo htmlspecialchars($pesan['nama']); ?></strong>
                                    pada <?php echo date('d M Y', strtotime($pesan['tanggal_kirim'])); ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">Belum ada pesan. Jadilah yang pertama!</li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
    </div>

</body>
</html>