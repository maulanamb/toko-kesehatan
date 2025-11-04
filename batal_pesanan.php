<?php
session_start();
require_once 'koneksi.php'; // Pastikan $conn

// 1. "Satpam" untuk Customer
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Ambil ID Pesanan dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: riwayat_pesanan.php?error=ID pesanan tidak valid');
    exit();
}

// 3. Mulai Transaksi Database
$conn->begin_transaction();

try {
    // 4. Cek Keamanan: Pastikan pesanan ini milik user DAN statusnya boleh dibatalkan
    $sql_cek = "SELECT order_id FROM orders 
                WHERE order_id = ? AND user_id = ? 
                AND status IN ('Menunggu Pembayaran', 'Diproses', 'Paid')"; // Status yang boleh dibatalkan
                
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ii", $order_id, $user_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows == 0) {
        // Jika pesanan tidak ditemukan, bukan milik user, atau statusnya "Dikirim" / "Selesai"
        throw new Exception("Pesanan tidak dapat dibatalkan.");
    }
    $stmt_cek->close();

    // 5. Ambil semua item di pesanan tersebut
    $sql_items = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    // 6. KEMBALIKAN STOK PRODUK (Loop)
    $sql_update_stok = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
    $stmt_stok = $conn->prepare($sql_update_stok);
    
    foreach ($items as $item) {
        $stmt_stok->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt_stok->execute();
    }
    $stmt_stok->close();

    // 7. Ubah Status Pesanan menjadi "Dibatalkan"
    $sql_cancel = "UPDATE orders SET status = 'Dibatalkan' WHERE order_id = ?";
    $stmt_cancel = $conn->prepare($sql_cancel);
    $stmt_cancel->bind_param("i", $order_id);
    $stmt_cancel->execute();
    $stmt_cancel->close();

    // 8. Jika semua berhasil, Commit transaksi
    $conn->commit();
    header("Location: riwayat_pesanan.php?sukses=" . urlencode("Pesanan #$order_id telah berhasil dibatalkan."));
    exit();

} catch (Exception $e) {
    // 9. Jika ada error, Rollback (batalkan semua)
    $conn->rollback();
    header("Location: riwayat_pesanan.php?error=" . urlencode("Gagal membatalkan pesanan: " . $e->getMessage()));
    exit();
}
?>