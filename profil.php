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


if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$sql = "SELECT username, email, date_of_birth, gender, address, city, contact_no, paypal_id 
        FROM users 
        WHERE user_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    die("Error: Data pengguna tidak ditemukan.");
}

$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
                                    <li><a class="dropdown-item active" href="profil.php">Profil Saya</a></li>
                                    <li><a class="dropdown-item" href="riwayat_pesanan.php">Riwayat Pesanan</a></li>
                                    <li><a class="dropdown-item" href="buka_toko.php">Toko Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                                </ul>
                            </li>

                        <?php else:  ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    Halo, <?php echo htmlspecialchars($username); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item active" href="profil.php">Profil Saya</a></li>
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
            <div class="col-lg-8">
                
                <h1 class="mb-4 text-center">Profil Saya</h1>

                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars(urldecode($pesan_sukses)); ?></div>
                <?php endif; ?>
                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars(urldecode($pesan_error)); ?></div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Data Profil</h2>
                    </div>
                    <div class="card-body">
                        <form action="profil_update.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <div class="form-text"></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail:</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Tanggal Lahir:</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin:</label>
                                <div class="form-check">
                                    <input type="radio" id="male" name="gender" value="Male" class="form-check-input" <?php echo ($user['gender'] == 'Male') ? 'checked' : ''; ?>>
                                    <label for="male" class="form-check-label">Laki-laki</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" id="female" name="gender" value="Female" class="form-check-input" <?php echo ($user['gender'] == 'Female') ? 'checked' : ''; ?>>
                                    <label for="female" class="form-check-label">Perempuan</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat:</label>
                                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">Kota:</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contact_no" class="form-label">No. Kontak:</label>
                                <input type="text" id="contact_no" name="contact_no" class="form-control" value="<?php echo htmlspecialchars($user['contact_no'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="paypal_id" class="form-label">Pay-pal ID:</label>
                                <input type="text" id="paypal_id" name="paypal_id" class="form-control" value="<?php echo htmlspecialchars($user['paypal_id'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan Profil</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Ganti Password</h2>
                    </div>
                    <div class="card-body">
                        <form action="password_update.php" method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini:</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru:</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_new_password" class="form-label">Konfirmasi Password Baru:</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Ganti Password</button>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>