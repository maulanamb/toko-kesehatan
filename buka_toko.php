<?php
session_start();

// --- ▼▼▼ LOGIKA LOGOUT OTOMATIS (Poin 3) ▼▼▼ ---
$batas_waktu = 1800; // 30 menit (1800 detik)

if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset();
        session_destroy();
        // Arahkan ke login dengan pesan
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir, silakan login kembali.'));
        exit();
    }
}
// Reset timer setiap kali halaman dimuat
$_SESSION['waktu_terakhir_aktif'] = time();
// --- ▲▲▲ SELESAI LOGIKA LOGOUT ▲▲▲ ---


require_once 'koneksi.php'; // Pastikan $conn

// 1. "Satpam" untuk Customer
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Ambil role

// 2. Cek apakah user ini sudah punya toko
$sql_cek_toko = "SELECT status FROM toko WHERE user_id = ?";
$stmt_cek = $conn->prepare($sql_cek_toko);
$stmt_cek->bind_param("i", $user_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

$status_toko = null;
if ($result_cek->num_rows > 0) {
    $status_toko = $result_cek->fetch_assoc()['status'];
}
$stmt_cek->close();


// 3. Logika saat form DISIMPAN (POST)
$pesan_error = "";
$pesan_sukses = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && is_null($status_toko)) {
    // Ambil data baru
    $nama_toko = $conn->real_escape_string($_POST['nama_toko']);
    $deskripsi_toko = $conn->real_escape_string($_POST['deskripsi_toko']);
    $no_telepon_toko = $conn->real_escape_string($_POST['no_telepon_toko']);
    $alamat_toko = $conn->real_escape_string($_POST['alamat_toko']);
    $kota_toko = $conn->real_escape_string($_POST['kota_toko']);

    if (!empty($nama_toko) && !empty($deskripsi_toko) && !empty($no_telepon_toko) && !empty($alamat_toko)) {
        // Query INSERT baru dengan kolom tambahan
        $sql_insert = "INSERT INTO toko (user_id, nama_toko, deskripsi_toko, no_telepon_toko, alamat_toko, kota_toko, status) 
                       VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $stmt_insert = $conn->prepare($sql_insert);
        
        // Perbaikan 'isssss' (6)
        $stmt_insert->bind_param("isssss", $user_id, $nama_toko, $deskripsi_toko, $no_telepon_toko, $alamat_toko, $kota_toko);
        
        if ($stmt_insert->execute()) {
            $pesan_sukses = "Pendaftaran toko Anda berhasil dikirim! Silakan tunggu persetujuan dari Admin.";
            $status_toko = 'pending'; // Update status di halaman ini
        } else {
            $pesan_error = "Gagal mendaftar toko: " . $conn->error;
        }
        $stmt_insert->close();
    } else {
        $pesan_error = "Semua field wajib diisi.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Toko</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .status-box { padding: 2rem; border-radius: 8px; text-align: center; }

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
            <a class="navbar-brand" href="profil.php">
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
                        <a class="nav-link" href="buku_tamu.php">Buku Tamu</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        
                        <?php if ($role == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/index.php">Dashboard Admin</a></li>
                            <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                        
                        <?php elseif ($role == 'vendor'): ?>
                            <li class="nav-item"><a class="nav-link" href="vendor/index.php">Dashboard Vendor</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item active" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>

                        <?php else: // JIKA CUSTOMER BIASA ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item active" href="buka_toko.php">Buka Toko</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h3 mb-4 border-bottom pb-3">Buka Toko Anda</h1>

                        <?php 
                        if (!empty($pesan_sukses)) echo "<div class='alert alert-success'>$pesan_sukses</div>";
                        if (!empty($pesan_error)) echo "<div class='alert alert-danger'>$pesan_error</div>";
                        ?>

                        <?php if (is_null($status_toko)): // 1. Tampilkan form jika BELUM mendaftar ?>
                            <p>Daftarkan toko Anda untuk mulai menjual produk alat kesehatan di platform kami.</p>
                            <form action="buka_toko.php" method="POST">
                                <div class="mb-3">
                                    <label for="nama_toko" class="form-label">Nama Toko:</label>
                                    <input type="text" id="nama_toko" name="nama_toko" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="no_telepon_toko" class="form-label">No. Telepon Toko (WhatsApp):</label>
                                    <input type="text" id="no_telepon_toko" name="no_telepon_toko" class="form-control" placeholder="Contoh: 08123456789" required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat_toko" class="form-label">Alamat Lengkap Toko:</label>
                                    <textarea id="alamat_toko" name="alamat_toko" class="form-control" rows="3" placeholder="Masukkan alamat lengkap toko / pickup" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="kota_toko" class="form-label">Kota:</label>
                                    <input type="text" id="kota_toko" name="kota_toko" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="deskripsi_toko" class="form-label">Deskripsi Toko:</label>
                                    <textarea id="deskripsi_toko" name="deskripsi_toko" class="form-control" rows="4" placeholder="Jelaskan tentang toko Anda..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2">Daftar Sekarang</button>
                            </form>
                        
                        <?php elseif ($status_toko == 'pending'): // 2. Tampilkan status PENDING ?>
                            <div class="status-box bg-warning-subtle text-dark-emphasis">
                                <h3 class="h4">Pendaftaran Anda Sedang Ditinjau</h3>
                                <p class="lead">Pendaftaran toko Anda telah kami terima dan sedang dalam proses peninjauan oleh Admin. Mohon tunggu 1-2 hari kerja.</p>
                            </div>

                        <?php elseif ($status_toko == 'approved'): // 3. Tampilkan status APPROVED ?>
                            <div class="status-box bg-success-subtle text-success-emphasis">
                                <h3 class="h4">Toko Anda Telah Disetujui!</h3>
                                <p class="lead">Selamat! Toko Anda telah disetujui. Anda sekarang adalah seorang Vendor.</p>
                                <a href="vendor/index.php" class="btn btn-success btn-lg mt-3">Masuk ke Dashboard Vendor</a>
                            </div>

                        <?php elseif ($status_toko == 'rejected'): // 4. Tampilkan status REJECTED ?>
                             <div class="status-box bg-danger-subtle text-danger-emphasis">
                                <h3 class="h4">Pendaftaran Ditolak</h3>
                                <p class="lead">Maaf, pendaftaran toko Anda ditolak oleh Admin. Silakan hubungi kami untuk informasi lebih lanjut.</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>