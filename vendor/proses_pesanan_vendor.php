<?php

require_once 'cek_vendor.php'; 


$order_detail_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($order_detail_id > 0 && ($action == 'approve' || $action == 'reject')) {

    $conn->begin_transaction();
    
    try {
        $sql_cek = "SELECT od.product_id, od.quantity 
                    FROM order_details od
                    JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_detail_id = ? AND p.toko_id = ? AND od.status_vendor = 'Pending'";
        
        $stmt_cek = $conn->prepare($sql_cek);
        $stmt_cek->bind_param("ii", $order_detail_id, $toko_id_vendor);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();

        if ($result_cek->num_rows == 0) {
            throw new Exception("Item pesanan tidak valid atau sudah diproses.");
        }
        $item = $result_cek->fetch_assoc();
        $stmt_cek->close();

        if ($action == 'approve') {
            $sql_update = "UPDATE order_details SET status_vendor = 'Approved' WHERE order_detail_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $order_detail_id);
            $stmt_update->execute();
            $stmt_update->close();
            
        } elseif ($action == 'reject') {
            $sql_update = "UPDATE order_details SET status_vendor = 'Rejected' WHERE order_detail_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $order_detail_id);
            $stmt_update->execute();
            $stmt_update->close();
            
            $sql_stok = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
            $stmt_stok = $conn->prepare($sql_stok);
            $stmt_stok->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt_stok->execute();
            $stmt_stok->close();
        }
        
        $conn->commit();
        header("Location: kelola_pesanan.php?status={$action}_sukses");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: kelola_pesanan.php?status=gagal&error=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    header('location: kelola_pesanan.php');
    exit();
}
?>