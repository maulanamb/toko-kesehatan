<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Ambil data profil lengkap pengguna
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

// 3. Ambil pesan sukses/error dari URL
$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; background-color: #f9f9f9; padding: 20px; }
        .container { 
            max-width: 700px; 
            margin: auto; 
            background-color: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        h1, h2 { 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 10px; 
        }
        .nav { 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .nav a { 
            margin-right: 15px; 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold;
        }
        .nav a:hover { text-decoration: underline; }

        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-top: 20px; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="date"], input[type="password"] {
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="text"][disabled] { background-color: #eee; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .message-sukses { color: green; background-color: #d4edda; border: 1px solid green; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .message-error { color: red; background-color: #f8d7da; border: 1px solid red; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="nav">
            <a href="index.php">Beranda Toko</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="riwayat_pesanan.php">Riwayat Pesanan</a>
            <a href="profil.php">Profil Saya</a>
            <a href="logout.php" style="color: red;">Logout</a>
        </div>

        <h1>Profil Saya</h1>
        <p>Selamat datang, <?php echo htmlspecialchars($username); ?>.</p>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="message-sukses"><?php echo htmlspecialchars(urldecode($pesan_sukses)); ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="message-error"><?php echo htmlspecialchars(urldecode($pesan_error)); ?></div>
        <?php endif; ?>

        <form action="profil_update.php" method="POST">
            <h2>Data Profil</h2>
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                <small>Username tidak bisa diubah.</small>
            </div>
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div>
                <label for="date_of_birth">Tanggal Lahir:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
            </div>
            <div>
                <label>Jenis Kelamin:</label>
                <input type="radio" id="male" name="gender" value="Male" <?php echo ($user['gender'] == 'Male') ? 'checked' : ''; ?>>
                <label for="male" style="display:inline; font-weight:normal;">Laki-laki</label>
                <input type="radio" id="female" name="gender" value="Female" <?php echo ($user['gender'] == 'Female') ? 'checked' : ''; ?>>
                <label for="female" style="display:inline; font-weight:normal;">Perempuan</label>
            </div>
            <div>
                <label for="address">Alamat:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
            </div>
            <div>
                <label for="city">Kota:</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
            </div>
            <div>
                <label for="contact_no">No. Kontak:</label>
                <input type="text" id="contact_no" name="contact_no" value="<?php echo htmlspecialchars($user['contact_no']); ?>">
            </div>
            <div>
                <label for="paypal_id">Pay-pal ID:</label>
                <input type="text" id="paypal_id" name="paypal_id" value="<?php echo htmlspecialchars($user['paypal_id']); ?>">
            </div>
            <div>
                <button type="submit">Simpan Perubahan Profil</button>
            </div>
        </form>

        <form action="password_update.php" method="POST">
            <h2>Ganti Password</h2>
            <div>
                <label for="current_password">Password Saat Ini:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div>
                <label for="new_password">Password Baru:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div>
                <label for="confirm_new_password">Konfirmasi Password Baru:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required>
            </div>
            <div>
                <button type="submit">Ganti Password</button>
            </div>
        </form>
    </div>
</body>
</html>