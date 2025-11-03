<?php
// 1. Mulai Session di baris paling atas
session_start();

// Jika pengguna sudah login, arahkan berdasarkan role MEREKA
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// 2. Memanggil file koneksi
require_once 'koneksi.php';

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
                
                // *** INI ADALAH PERUBAHANNYA ***
                // 8. Arahkan (Redirect) berdasarkan ROLE
                if ($_SESSION['role'] == 'admin') {
                    // Jika dia admin, kirim ke dashboard admin
                    header("Location: admin/index.php");
                } else {
                    // Jika dia customer, kirim ke halaman produk
                    header("Location: index.php");
                }
                exit();
                // *** AKHIR PERUBAHAN ***

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
    <title>Login - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .login-container { border: 1px solid #ccc; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; }
        div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 8px; box-sizing: border-box;
        }
        .message-error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Selamat datang di Toko Alat Kesehatan</h2>

        <?php if (!empty($pesan_error)): ?>
            <div class="message-error"><?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div>
                <label for="username">User ID:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">LOGIN</button>
            </div>
        </form>
        
        <p>Belum punya akun? <a href="registrasi.php">Registrasi di sini</a></p>
    </div>

</body>
</html>