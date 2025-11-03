<?php
// Selalu mulai session di baris paling atas
session_start();

// 1. Cek apakah pengguna sudah login
// 2. Cek apakah rolenya adalah 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    
    // 3. Jika bukan admin, tendang dia kembali ke halaman login utama
    header("Location: ../login.php?error=Akses ditolak. Area khusus admin.");
    exit();
}

// 4. Jika dia adalah admin, ambil datanya
$admin_username = $_SESSION['username'];
$admin_id = $_SESSION['user_id'];

// File ini tidak menampilkan HTML, 
// hanya melakukan pengecekan dan menyediakan data admin.
?>