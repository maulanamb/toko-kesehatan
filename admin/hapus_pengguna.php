<?php
session_start();
require_once 'cek_admin.php'; 

require_once '../koneksi.php'; 

$user_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;


$admin_id_logged_in = $_SESSION['admin_user_id'] ?? 0; 


if ($user_id_to_delete === $admin_id_logged_in) {
    header('location: kelola_pengguna.php?status=hapus_gagal&error=self');
    exit();
}


if ($user_id_to_delete === 1) { 
    header('location: kelola_pengguna.php?status=hapus_gagal&error=superadmin');
    exit();
}


if ($user_id_to_delete > 0) {
    
    $sql = "DELETE FROM users WHERE user_id = $user_id_to_delete";
    
    if ($conn->query($sql) === TRUE) {
        header('location: kelola_pengguna.php?status=hapus_sukses');
        exit();
    } else {
        $error = urlencode($conn->error);
        header("location: kelola_pengguna.php?status=hapus_gagal&error={$error}");
        exit();
    }
} else {
    header('location: kelola_pengguna.php?status=id_tidak_valid');
    exit();
}
?>