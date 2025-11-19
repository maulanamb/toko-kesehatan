<?php
require_once 'cek_admin.php'; 
require_once '../koneksi.php'; 

$toko_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($toko_id > 0 && ($action == 'approve' || $action == 'reject')) {

    $conn->begin_transaction();
    
    try {
        $new_status = ($action == 'approve') ? 'approved' : 'rejected';

        $sql_toko = "UPDATE toko SET status = ? WHERE toko_id = ?";
        $stmt_toko = $conn->prepare($sql_toko);
        $stmt_toko->bind_param("si", $new_status, $toko_id);
        $stmt_toko->execute();
        
        if ($stmt_toko->affected_rows == 0) {
            throw new Exception("Update status toko gagal.");
        }
        $stmt_toko->close();

        if ($action == 'approve') {
            $sql_get_user = "SELECT user_id FROM toko WHERE toko_id = ?";
            $stmt_get_user = $conn->prepare($sql_get_user);
            $stmt_get_user->bind_param("i", $toko_id);
            $stmt_get_user->execute();
            $result_user = $stmt_get_user->get_result();
            if ($result_user->num_rows == 0) {
                throw new Exception("User pemilik toko tidak ditemukan.");
            }
            $user_id = $result_user->fetch_assoc()['user_id'];
            $stmt_get_user->close();
            
            $sql_user = "UPDATE users SET role = 'vendor' WHERE user_id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            
            if ($stmt_user->affected_rows == 0) {
                throw new Exception("Update role user gagal.");
            }
            $stmt_user->close();
        }
        
        $conn->commit();
        header("Location: kelola_toko.php?status={$action}_sukses");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: kelola_toko.php?status=gagal&error=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    header('location: kelola_toko.php');
    exit();
}
?>