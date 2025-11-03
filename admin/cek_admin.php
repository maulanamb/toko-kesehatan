<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login DAN apakah rolenya 'admin'
if ( !isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin' ) {
    
    // Jika tidak, tendang user ke halaman login (di luar folder admin)
    header('location: ../login.php?error=Akses ditolak');
    exit();
}

// Jika lolos, simpan ID admin untuk digunakan di halaman
$admin_id_logged_in = $_SESSION['user_id'];
$admin_username_logged_in = $_SESSION['username'];

?>