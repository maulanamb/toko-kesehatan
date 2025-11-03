<?php
session_start();

// 1. Cek login dan koneksi
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'koneksi.php';

// 2. Ambil ID pengguna
$user_id = $_SESSION['user_id'];

// 3. Cek apakah tombol "update_cart" ditekan dan data "quantity" ada
if (isset($_POST['update_cart']) && isset($_POST['quantity']) && is_array($_POST['quantity'])) {

    // 4. Siapkan query SQL di luar loop (lebih efisien)
    $sql_update = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    $sql_delete = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    // 5. Looping untuk setiap produk di keranjang
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        
        // Sanitasi input (pastikan angkanya valid)
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id > 0) {
            if ($quantity <= 0) {
                // 6. Jika jumlah 0 atau kurang, HAPUS item
                $stmt_delete->bind_param("ii", $user_id, $product_id);
                $stmt_delete->execute();
            } else {
                // 7. Jika jumlah lebih dari 0, UPDATE item
                $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
                $stmt_update->execute();
            }
        }
    }
    
    $stmt_update->close();
    $stmt_delete->close();
}

$conn->close();

// 8. Kembalikan pengguna ke halaman keranjang
header("Location: keranjang.php?message=Keranjang berhasil diperbarui");
exit();
?>