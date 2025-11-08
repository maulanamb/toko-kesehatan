<?php
// 1. Mulai Session di baris paling atas
session_start();

// Jika pengguna sudah login, arahkan berdasarkan role MEREKA
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } elseif ($_SESSION['role'] == 'vendor') { // <-- Tambahan Pengecekan
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
                
                // ▼▼▼ TAMBAHAN UNTUK LOGOUT OTOMATIS (Poin 3) ▼▼▼
                $_SESSION['waktu_terakhir_aktif'] = time(); 
                // ▲▲▲ SELESAI TAMBAHAN ▲▲▲
                
                
                // ▼▼▼ PERBAIKAN LOGIKA REDIRECT (Poin 1) ▼▼▼
                if ($_SESSION['role'] == 'admin') {
                    header("Location: admin/index.php");
                } elseif ($_SESSION['role'] == 'vendor') {
                    header("Location: vendor/index.php"); // <-- Arahkan vendor ke sini
                } else {
                    header("Location: index.php"); // Arahkan customer ke sini
                }
                exit();
                // ▲▲▲ SELESAI PERBAIKAN ▲▲▲

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
    <title>Login - Toko Alat Kesehatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        
                        <div class="text-center mb-4">
                            <h2 class="h3 fw-bold">Login</h2>
                            <p class="text-muted">Selamat datang kembali!</p>
                        </div>

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
                                <button type="submit" class="btn btn-primary btn-lg">LOGIN</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Belum punya akun? <a href="registrasi.php">Daftar di sini</a></p>
                            <p class="mt-2"><a href="index.php" class="text-decoration-none">&laquo; Kembali ke Beranda</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>