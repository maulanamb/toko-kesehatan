<?php
session_start();

$batas_waktu = 1800; // 30 menit
if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset(); session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir.'));
        exit();
    }
}
$_SESSION['waktu_terakhir_aktif'] = time();

require_once 'koneksi.php'; 

if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}
$user_id = $_SESSION['user_id'];

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: riwayat_pesanan.php?error=ID pesanan tidak valid');
    exit();
}

$sql_cek = "SELECT order_id FROM orders 
            WHERE order_id = ? AND user_id = ? AND status = 'Dikirim'";
            
$stmt_cek = $conn->prepare($sql_cek);
$stmt_cek->bind_param("ii", $order_id, $user_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

if ($result_cek->num_rows == 1) {
    $sql_update = "UPDATE orders SET status = 'Selesai' WHERE order_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $order_id, $user_id);
    
    if ($stmt_update->execute()) {
        header("Location: riwayat_pesanan.php?sukses=" . urlencode("Pesanan #$order_id telah diselesaikan. Terima kasih!"));
    } else {
        header("Location: riwayat_pesanan.php?error=" . urlencode("Gagal memperbarui status pesanan."));
    }
    $stmt_update->close();
} else {
    header("Location: riwayat_pesanan.php?error=" . urlencode("Pesanan tidak valid atau statusnya bukan 'Dikirim'."));
}

$stmt_cek->close();
$conn->close();
exit();
?>