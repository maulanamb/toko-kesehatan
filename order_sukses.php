<?php
session_start();

$batas_waktu = 1800; // 30 menit (1800 detik)

if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset();
        session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir, silakan login kembali.'));
        exit();
    }
}
$_SESSION['waktu_terakhir_aktif'] = time();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Pelanggan';
$role = $_SESSION['role'] ?? 'customer'; 

$order_id = $_GET['order_id'] ?? 'N/A';
$pdf_file_path = $_GET['pdf'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
         .navbar-brand {
            padding-top: 0; 
            padding-bottom: 0;
            margin-right: 0.5rem;
        }
        .navbar-brand img {
            height: 80px;
            width: auto;
            vertical-align: middle; 
        }   
    </style>
    </head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="order_sukses.php">
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

                        <?php else:  ?>
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
            <div class="col-md-7">
                <div class="card shadow-sm border-0 text-center p-4">
                    <div class="card-body">
                        
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        
                        <h1 class="h2 text-success mt-3">Pesanan Berhasil!</h1>
                        <p class="lead">Terima kasih atas pembelian Anda.</p>
                        <p>Nomor Order Anda adalah: <strong>#<?php echo htmlspecialchars($order_id); ?></strong></p>
                        <p>Laporan pembelian Anda dalam bentuk PDF telah berhasil dibuat. Silakan cek email anda!</p>
                        
                        <?php
                        $path_fisik_file = __DIR__ . '/' . $pdf_file_path;
                        
                        if (!empty($pdf_file_path) && file_exists($path_fisik_file)):
                        ?>
                            <a href="download.php?file=<?php echo urlencode($pdf_file_path); ?>" class="btn btn-primary btn-lg mt-3">
                                <i class="bi bi-download"></i> Download Laporan PDF
                            </a>
                        <?php else: ?>
                            <p class="alert alert-danger">Error: File PDF tidak dapat ditemukan.</p>
                        <?php endif; ?>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                            <a href="riwayat_pesanan.php" class="btn btn-outline-primary ms-2">Lihat Riwayat Pesanan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>