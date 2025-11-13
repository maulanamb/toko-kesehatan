<?php
// 1. Mulai Session di baris paling atas
session_start();

// --- LOGIKA LOGOUT OTOMATIS ---
$batas_waktu = 1800; // 30 menit
if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset(); session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir.'));
        exit();
    }
}
if (isset($_SESSION['user_id'])) {
    $_SESSION['waktu_terakhir_aktif'] = time(); 
}
// --- SELESAI LOGIKA LOGOUT ---

// Jika pengguna sudah login, arahkan berdasarkan role MEREKA
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } elseif ($_SESSION['role'] == 'vendor') { 
        header("Location: vendor/index.php");
    } else {
        header("Location: index.php"); // Arahkan ke beranda
    }
    exit();
}

// 2. Memanggil file koneksi
require_once 'koneksi.php'; // Pastikan $conn ada di sini

$pesan_error = "";

// 3. Cek apakah form login sudah di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ... (Logika PHP Anda untuk login tetap sama) ...
    // 4. Ambil data dari form
    $username_input = $_POST['username'] ?? '';
    $password_input = $_POST['password'] ?? '';

    if (empty($username_input) || empty($password_input)) {
        $pesan_error = "Username dan Password wajib diisi.";
    } else {
        
        // 5. Cari User di database
        $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $hashed_password_from_db = $user['password'];

            // 6. VERIFIKASI PASSWORD
            if (password_verify($password_input, $hashed_password_from_db)) {
                
                // 7. LOGIN BERHASIL: Simpan data ke session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['waktu_terakhir_aktif'] = time(); // <-- Set timer logout
                
                // 8. Arahkan (Redirect) berdasarkan ROLE
                if ($_SESSION['role'] == 'admin') {
                    header("Location: admin/index.php");
                } elseif ($_SESSION['role'] == 'vendor') {
                    header("Location: vendor/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();

            } else {
                $pesan_error = "Password yang Anda masukkan salah.";
            }
        } else {
            $pesan_error = "Username tidak ditemukan.";
        }
        
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Kesehatan Purnama</title> 
    <link rel="icon" type="image/png" href="images/minilogo.png"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }
        /* Panel Kiri: Visual */
        .login-visual {
            flex: 1;
            background-color: #f8f8ff; /* Latar belakang putih */
            color: #333; /* Teks gelap */
            display: flex;
            flex-direction: column;
            /* ▼▼▼ PERBAIKAN: Kembalikan ke tengah ▼▼▼ */
            justify-content: center;
            align-items: center;
            /* ▲▲▲ SELESAI ▲▲▲ */
            padding: 3rem;
            text-align: center;
        }
        /* Panel Kanan: Form */
        .login-form-wrapper {
            flex: 1;
            background-image: linear-gradient(135deg, #007bff, #198754);
            display: flex;
            justify-content: center;
            /* ▼▼▼ PERBAIKAN: Kembalikan ke tengah ▼▼▼ */
            align-items: center;
            /* ▲▲▲ SELESAI ▲▲▲ */
            padding: 2rem;
        }
        .login-box {
            width: 100%;
            max-width: 400px; /* Lebar maksimum form */
            background-color: #f8f8ff;
            padding: 2.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .logo-visual {
            width: 250px; /* Ukuran logo Anda */
            height: auto;
            margin-bottom: 0; /* Jarak bawah logo di-nol-kan */
        }
        .logo-form {
            width: 120px; 
            height: auto;
            margin-bottom: 1rem;
        }
        
        .visual-title {
            /* ▼▼▼ PERBAIKAN: Kembalikan jarak atas ▼▼▼ */
            margin-top: 1.5rem; /* Jarak atas h1 (dari logo) */
            /* ▲▲▲ SELESAI ▲▲▲ */
            margin-bottom: 1rem; 
        }

        .btn-login {
            background-color: #007bff; /* Warna biru normal */
            color: white;
            border: none;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s; /* Ini membuat perubahan warna jadi mulus */
        }
        .btn-login:hover {
            background-color: #0056b3; /* Warna biru saat hover */
        }

        /* Responsive: Sembunyikan panel visual di layar kecil (HP) */
        @media (max-width: 991.98px) {
            .login-visual {
                display: none;
            }
            .login-form-wrapper {
                align-items: center; 
            }
            .login-box {
                box-shadow: none; 
                background-color: transparent; 
                padding: 1rem;
            }
        }
    </style>
    </head>
<body>

    <div class="login-wrapper">
        
        <div class="login-visual d-none d-lg-flex">
            <div>
                <img src="images/minilogo.png" alt="Toko Kesehatan Purnama Logo" class="logo-visual">
                
                <h1 class="display-5 fw-bold visual-title">Toko Kesehatan Purnama</h1>
                
                <p class="lead">Healthy Living, Every Day</p>
                <a href="index.php" class="btn btn-outline-primary mt-3">&laquo; Kembali ke Beranda</a>
            </div>
        </div>

        <div class="login-form-wrapper">
            <div class="login-box">
                
                <div class="text-center mb-4 d-lg-none"> <img src="images/logo.png" alt="Toko Kesehatan Purnama Logo" class="logo-form">
                </div>

                <h2 class="h3 fw-bold text-center">Login</h2>
                <p class="text-muted text-center mb-4">Selamat datang kembali!</p>

                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($pesan_error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                       <button type="submit" class="btn-login">LOGIN</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">Belum punya akun? <a href="registrasi.php">Registrasi di sini</a></p>
                    <p class="mt-2 d-lg-none"><a href="index.php" class="text-decoration-none">&laquo; Kembali ke Beranda</a></p>
                </div>

            </div>
        </div>
    </div>

</body>
</html>