<?php
require_once 'cek_admin.php'; // Aktifkan satpam!
require_once '../koneksi.php'; 

$pesan_error = "";
$pesan_sukses = "";

// Ambil ID pengguna dari URL
$user_id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id_to_edit === 0) {
    header('location: kelola_pengguna.php?status=id_tidak_valid');
    exit();
}

// 1. Logika saat form DISIMPAN (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password']; // Ambil password (jika diisi)

    // Validasi dasar
    if (!empty($username) && !empty($email) && !empty($role)) {
        
        // Cek apakah admin utama (ID 1) diedit dan rolenya diubah
        if ($user_id_to_edit == 1 && $role != 'admin') {
            $pesan_error = "Gagal! Role untuk admin utama (ID 1) tidak boleh diubah dari 'admin'.";
        } 
        // Cek apakah admin mencoba mengubah role-nya sendiri
        else if ($user_id_to_edit == $admin_id_logged_in && $role != 'admin') {
             $pesan_error = "Gagal! Anda tidak bisa mengubah role akun Anda sendiri.";
        }
        else {
            // Siapkan query UPDATE
            if (!empty($password)) {
                // Jika password baru diisi, HASH password tersebut
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET 
                            username = '$username',
                            email = '$email',
                            role = '$role',
                            password = '$hashed_password'
                        WHERE user_id = $user_id_to_edit";
            } else {
                // Jika password dikosongi, JANGAN update password
                $sql = "UPDATE users SET 
                            username = '$username',
                            email = '$email',
                            role = '$role'
                        WHERE user_id = $user_id_to_edit";
            }

            // Eksekusi query
            if ($conn->query($sql) === TRUE) {
                header("location: kelola_pengguna.php?status=edit_sukses");
                exit();
            } else {
                $pesan_error = "Gagal memperbarui pengguna: " . $conn->error;
            }
        }

    } else {
        $pesan_error = "Username, Email, dan Role tidak boleh kosong.";
    }
}

// 2. Logika saat halaman DIBUKA (GET)
// Ambil data lama dari database untuk ditampilkan di form
$sql_get = "SELECT username, email, role FROM users WHERE user_id = $user_id_to_edit";
$result = $conn->query($sql_get);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Jika ID tidak ditemukan
    header('location: kelola_pengguna.php?status=id_tidak_ditemukan');
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna - Admin Panel</title>
    
    <style>
        /* [CSS yang sama dengan file admin lainnya] */
        body { font-family: sans-serif; display: flex; margin: 0; }
        .sidebar { width: 250px; background: #333; color: white; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .sidebar h2 { border-bottom: 1px solid #555; padding-bottom: 10px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { margin: 15px 0; }
        .sidebar ul li a { color: white; text-decoration: none; font-size: 1.1em; }
        .content { flex: 1; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; }
        
        /* Style untuk form */
        .form-container { max-width: 500px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: white; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-kembali { display: inline-block; margin-top: 15px; color: #555; text-decoration: none; }
        .error { color: red; background-color: #fdd; padding: 10px; border: 1px solid red; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_pesanan.php">Kelola Pesanan</a></li>
            <li><a href="manage_kategori.php">Kelola Kategori</a></li>
            <li><a href="kelola_produk.php">Kelola Produk</a></li>
            <li><a href="kelola_pengguna.php">Kelola Pengguna</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Edit Pengguna: <?php echo htmlspecialchars($user['username']); ?></h1>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="form-container">
            <?php 
            if (!empty($pesan_error)) {
                echo "<div class='error'>$pesan_error</div>";
            }
            ?>

            <form action="edit_pengguna.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="customer" <?php echo ($user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">Password Baru (Opsional):</label>
                    <input type="password" id="password" name="password">
                    <small>Biarkan kosong jika tidak ingin mengubah password.</small>
                </div>
                
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </form>

            <a href="kelola_pengguna.php" class="btn-kembali">&laquo; Kembali ke Manajemen Pengguna</a>
        </div>
    </div>

</body>
</html>