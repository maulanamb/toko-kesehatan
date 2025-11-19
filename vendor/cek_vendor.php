<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'vendor') {
    header('location: ../login.php?error=Akses ditolak. Silakan login sebagai vendor.');
    exit();
}

require_once '../koneksi.php'; 

$user_id_vendor = $_SESSION['user_id'];

$sql_toko = "SELECT toko_id, nama_toko, status FROM toko WHERE user_id = ?";
$stmt_toko = $conn->prepare($sql_toko);
$stmt_toko->bind_param("i", $user_id_vendor);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();

if ($result_toko->num_rows == 0) {
    header('location: ../buka_toko.php?error=Data toko tidak ditemukan.');
    exit();
}

$toko = $result_toko->fetch_assoc();

if ($toko['status'] == 'pending') {
    header('location: ../buka_toko.php?status=pending');
    exit();
} elseif ($toko['status'] == 'rejected') {
    header('location: ../buka_toko.php?status=rejected');
    exit();
}


$_SESSION['toko_id'] = $toko['toko_id'];
$_SESSION['nama_toko'] = $toko['nama_toko'];

$toko_id_vendor = $toko['toko_id'];
$nama_toko_vendor = $toko['nama_toko'];

$stmt_toko->close();
?>