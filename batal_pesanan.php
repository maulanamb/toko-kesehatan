<?php
session_start();

// --- LOGIKA LOGOUT OTOMATIS ---
$batas_waktu = 1800; // 30 menit
if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset(); session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir.'));
        exit();
    }
}
$_SESSION['waktu_terakhir_aktif'] = time();
// --- SELESAI LOGIKA LOGOUT ---

require_once 'koneksi.php'; // Pastikan $conn

// 1. Cek Login
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Ambil ID Pesanan
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: riwayat_pesanan.php?error=ID pesanan tidak valid');
    exit();
}

// 3. Mulai Transaksi Database
$conn->begin_transaction();

try {
    // 4. Cek Keamanan: Pastikan pesanan ini milik user DAN statusnya boleh dibatalkan
    // ▼▼▼ PERBAIKAN LOGIKA DI SINI ▼▼▼
    // Hapus 'Diproses' dari daftar IN()
    $sql_cek = "SELECT order_id FROM orders 
                WHERE order_id = ? AND user_id = ? 
                AND status IN ('Menunggu Pembayaran', 'Paid')"; // Status yang boleh dibatalkan
    // ▲▲▲ SELESAI PERBAIKAN ▲▲▲
                
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("ii", $order_id, $user_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();
    
    if ($result_cek->num_rows == 0) {
        // Jika pesanan tidak ditemukan, bukan milik user, atau statusnya sudah "Diproses"
        throw new Exception("Pesanan tidak dapat dibatalkan (mungkin sudah diproses).");
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