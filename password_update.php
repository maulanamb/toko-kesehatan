<?php
session_start();
require_once 'koneksi.php'; // Pastikan file ini menyediakan variabel $conn

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Cek apakah ini request POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Ambil data password
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 4. Validasi input
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        header("Location: profil.php?error=" . urlencode("Semua field password harus diisi."));
        exit();
    }
    
    if ($new_password !== $confirm_new_password) {
        header("Location: profil.php?error=" . urlencode("Password baru dan konfirmasi tidak cocok."));
        exit();
    }
    
    // 5. Verifikasi password saat ini
    $sql_get_pass = "SELECT password FROM users WHERE user_id = ?";
    $stmt_get = $conn->prepare($sql_get_pass);
    $stmt_get->bind_param("i", $user_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row['password'];
        
        // Cek password saat ini
        if (password_verify($current_password, $hashed_password_from_db)) {
            
            // 6. Update ke password baru
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $sql_update_pass = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update_pass);
            $stmt_update->bind_param("si", $new_hashed_password, $user_id);
            
            if ($stmt_update->execute()) {
                header("Location: profil.php?sukses=" . urlencode("Password berhasil diganti."));
            } else {
                header("Location: profil.php?error=" . urlencode("Gagal mengganti password: " . $stmt_update->error));
            }
            $stmt_update->close();
            
        } else {
            header("Location: profil.php?error=" . urlencode("Password saat ini salah."));
        }
    }
    
    $stmt_get->close();
    $conn->close();
    
} else {
    // Jika bukan POST, tendang
    header("Location: profil.php");
    exit();
}
?>