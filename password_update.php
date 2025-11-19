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


require_once 'koneksi.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        header("Location: profil.php?error=" . urlencode("Semua field password harus diisi."));
        exit();
    }
    
    if ($new_password !== $confirm_new_password) {
        header("Location: profil.php?error=" . urlencode("Password baru dan konfirmasi tidak cocok."));
        exit();
    }
    
    $sql_get_pass = "SELECT password FROM users WHERE user_id = ?";
    $stmt_get = $conn->prepare($sql_get_pass);
    $stmt_get->bind_param("i", $user_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row['password'];
        
        if (password_verify($current_password, $hashed_password_from_db)) {
            
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
    header("Location: profil.php");
    exit();
}
?>