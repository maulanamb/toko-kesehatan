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

$conn->begin_transaction();

try {
    $sql_cek = "SELECT order_id FROM orders 
                WHERE order_id = ? AND user_id = ? 
                AND status IN ('Menunggu Pembayaran', 'Paid')"; // Status yang boleh dibatalkan
                
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ii", $order_id, $user_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows == 0) {
        throw new Exception("Pesanan tidak dapat dibatalkan (mungkin sudah diproses).");
    }
    $stmt_cek->close();

    $sql_items = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    $sql_update_stok = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
    $stmt_stok = $conn->prepare($sql_update_stok);
    
    foreach ($items as $item) {
        $stmt_stok->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt_stok->execute();
    }
    $stmt_stok->close();

    $sql_cancel = "UPDATE orders SET status = 'Dibatalkan' WHERE order_id = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param("i", $order_id);
    $stmt_cancel->execute();
    $stmt_cancel->close();

    $conn->commit();
    header("Location: riwayat_pesanan.php?sukses=" . urlencode("Pesanan #$order_id telah berhasil dibatalkan."));
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: riwayat_pesanan.php?error=" . urlencode("Gagal membatalkan pesanan: " . $e->getMessage()));
    exit();
}
?>