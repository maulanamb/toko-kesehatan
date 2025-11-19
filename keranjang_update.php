<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];


if (isset($_POST['update_cart']) && isset($_POST['quantity']) && is_array($_POST['quantity'])) {

    $sql_update = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    $sql_delete = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    foreach ($_POST['quantity'] as $product_id => $quantity) {
        
        $product_id = (int)$product_id;
        $quantity = (int)$quantity;
        
        if ($product_id > 0) {
            if ($quantity <= 0) {
                $stmt_delete->bind_param("ii", $user_id, $product_id);
                $stmt_delete->execute();
            } else {
                $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
                $stmt_update->execute();
            }
        }
    }
    
    $stmt_update->close();
    $stmt_delete->close();
}

$conn->close();

header("Location: keranjang.php?message=Keranjang berhasil diperbarui");
exit();
?>