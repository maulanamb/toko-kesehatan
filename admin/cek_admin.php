<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ( !isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin' ) {
    
    header('location: ../login.php?error=Akses ditolak');
    exit();
}

$admin_id_logged_in = $_SESSION['user_id'];
$admin_username_logged_in = $_SESSION['username'];

?>