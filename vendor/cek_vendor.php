<?php
// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek apakah user sudah login DAN rolenya 'vendor'
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'vendor') {
    // Jika tidak, tendang ke halaman login
    header('location: ../login.php?error=Akses ditolak. Silakan login sebagai vendor.');
    exit();
}

// 2. User adalah vendor, sekarang cek status tokonya
require_once '../koneksi.php'; // Panggil koneksi

$user_id_vendor = $_SESSION['user_id'];

// Cek di tabel 'toko'
$sql_toko = "SELECT toko_id, nama_toko, status FROM toko WHERE user_id = ?";
$stmt_toko = $conn->prepare($sql_toko);
$stmt_toko->bind_param("i", $user_id_vendor);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();

if ($result_toko->num_rows == 0) {
    // Aneh, rolenya 'vendor' tapi tidak punya data toko
    header('location: ../buka_toko.php?error=Data toko tidak ditemukan.');
    exit();
}

$toko = $result_toko->fetch_assoc();

// 3. Cek Status Toko
if ($toko['status'] == 'pending') {
    // Jika masih 'pending', tendang balik ke halaman 'buka_toko'
    header('location: ../buka_toko.php?status=pending');
    exit();
} elseif ($toko['status'] == 'rejected') {
    // Jika 'rejected', tendang balik
    header('location: ../buka_toko.php?status=rejected');
    exit();
}

// 4. Lolos! Tokonya 'approved'
// Simpan data penting toko ke session
$_SESSION['toko_id'] = $toko['toko_id'];
$_SESSION['nama_toko'] = $toko['nama_toko'];

// Kita juga siapkan variabel untuk dipakai di halaman
$toko_id_vendor = $toko['toko_id'];
$nama_toko_vendor = $toko['nama_toko'];

$stmt_toko->close();
// Biarkan $conn terbuka untuk digunakan oleh halaman yang memanggil file ini
?>