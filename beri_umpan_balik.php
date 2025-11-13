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
$username = $_SESSION['username']; // Ambil username untuk navbar
$role = $_SESSION['role']; // Ambil role untuk navbar

// 2. Ambil ID Pesanan dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: riwayat_pesanan.php?error=ID pesanan tidak valid');
    exit();
}

// 3. Cek Keamanan: Pastikan pesanan ini milik user yang login DAN statusnya "Selesai"
$sql_cek = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ? AND status = 'Selesai'";
$stmt_cek = $conn->prepare($sql_cek);
$stmt_cek->bind_param("ii", $order_id, $user_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
if ($result_cek->num_rows == 0) {
    header('location: riwayat_pesanan.php?error=Pesanan tidak valid atau belum selesai.');
    exit();
}
$stmt_cek->close();

// 4. Cek Keamanan: Pastikan belum pernah memberi ulasan
$sql_cek_fb = "SELECT id FROM feedback WHERE order_id = ?";
$stmt_cek_fb = $conn->prepare($sql_cek_fb);
$stmt_cek_fb->bind_param("i", $order_id);
$stmt_cek_fb->execute();
$result_cek_fb = $stmt_cek_fb->get_result();
if ($result_cek_fb->num_rows > 0) {
    header('location: riwayat_pesanan.php?error=Anda sudah memberi ulasan untuk pesanan ini.');
    exit();
}
$stmt_cek_fb->close();


// 5. Logika saat form DISIMPAN (POST)
$pesan_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = (int)$_POST['rating'];
    $komentar = $conn->real_escape_string($_POST['komentar']);
    
    if ($rating < 1 || $rating > 5) {
        $pesan_error = "Silakan pilih rating bintang 1 sampai 5.";
    } else {
        $sql_insert = "INSERT INTO feedback (order_id, user_id, rating, komentar) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiis", $order_id, $user_id, $rating, $komentar);
        
        if ($stmt_insert->execute()) {
            header("Location: riwayat_pesanan.php?sukses=" . urlencode("Terima kasih atas ulasan Anda!"));
            exit();
        } else {
            $pesan_error = "Terjadi kesalahan: " . $conn->error;
        }
        $stmt_insert->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umpan Balik<?php echo $order_id; ?></title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Rating Bintang */
        .rating-stars {
            display: inline-block;
            direction: rtl; /* Balik urutan bintang */
        }
        .rating-stars input[type=radio] {
            display: none; /* Sembunyikan radio button asli */
        }
        .rating-stars label {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            padding: 0 2px;
            display: inline-block; /* Penting */
        }
        /* Saat di-hover atau di-check */
        .rating-stars:hover label,
        .rating-stars:hover label ~ label, /* Hover bintang di kirinya */
        .rating-stars input[type=radio]:checked ~ label {
            color: #f0ad4e; /* Warna bintang terisi */
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Toko Kesehatan</a>
            
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
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>

                        <?php else: // JIKA CUSTOMER BIASA ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
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

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <a href="riwayat_pesanan.php" class="text-decoration-none mb-3 d-inline-block">&laquo; Kembali ke Riwayat Pesanan</a>
                        <h1 class="h3 mb-3">Beri Umpan Balik</h1>
                        <p class="text-muted">Silakan beri ulasan untuk pesanan Anda <strong>#<?php echo $order_id; ?></strong>.</p>

                        <?php 
                        if (!empty($pesan_error)) echo "<div class='alert alert-danger'>$pesan_error</div>";
                        ?>

                        <form action="beri_umpan_balik.php?order_id=<?php echo $order_id; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label d-block">Rating Anda:</label>
                                <div class="rating-stars">
                                    <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="komentar" class="form-label">Komentar Anda (Opsional):</label>
                                <textarea id="komentar" name="komentar" class="form-control" rows="5" placeholder="Bagaimana pengalaman Anda dengan produk dan layanan kami?"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Kirim Umpan Balik</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>