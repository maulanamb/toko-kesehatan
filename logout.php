<?php
// 1. Mulai session
session_start();

// 2. Hapus semua variabel session
$_SESSION = array();

// 3. Hancurkan session
session_destroy();

// 4. Arahkan (redirect) kembali ke halaman login
header("Location: login.php?message=Anda telah logout.");
exit();
?>