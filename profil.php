<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. Ambil data profil lengkap pengguna dari database
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

// 3. Ambil pesan sukses/error dari URL (jika ada)
$pesan_sukses = $_GET['sukses'] ?? '';
$pesan_error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 600px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ccc; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-top: 20px; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="date"] {
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;
        }
        input[type="text"][disabled] { background-color: #eee; }
        .message-sukses { color: green; border: 1px solid green; padding: 10px; margin-bottom: 10px; }
        .message-error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Profil Saya</h1>
        <div><a href="index.php">Kembali ke Produk</a></div>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="message-sukses"><?php echo htmlspecialchars($pesan_sukses); ?></div>
    <?php endif; ?>
    <?php if (!empty($pesan_error)): ?>
        <div class="message-error"><?php echo htmlspecialchars($pesan_error); ?></div>
    <?php endif; ?>

    <form action="profil_update.php" method="POST">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div>
            <label for="date_of_birth">Date of birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
        </div>
        <div>
            <label>Gender:</label>
            <input type="radio" id="male" name="gender" value="Male" <?php echo ($user['gender'] == 'Male') ? 'checked' : ''; ?>>
            <label for="male" style="display:inline; font-weight:normal;">Male</label>
            <input type="radio" id="female" name="gender" value="Female" <?php echo ($user['gender'] == 'Female') ? 'checked' : ''; ?>>
            <label for="female" style="display:inline; font-weight:normal;">Female</label>
        </div>
        <div>
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
        </div>
        <div>
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
        </div>
        <div>
            <label for="contact_no">Contact no:</label>
            <input type="text" id="contact_no" name="contact_no" value="<?php echo htmlspecialchars($user['contact_no']); ?>">
        </div>
        <div>
            <label for="paypal_id">Pay-pal id:</label>
            <input type="text" id="paypal_id" name="paypal_id" value="<?php echo htmlspecialchars($user['paypal_id']); ?>">
        </div>
        <div>
            <button type="submit">Simpan Perubahan</button>
        </div>
    </form>

</body>
</html>