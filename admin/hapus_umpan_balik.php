<?php
require_once 'cek_admin.php'; 
require_once '../koneksi.php';

$id_feedback = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_feedback > 0) {
    $sql = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_feedback);
    
    if ($stmt->execute()) {
        header('location: kelola_umpan_balik.php?status=hapus_sukses');
    } else {
        header('location: kelola_umpan_balik.php?status=hapus_gagal');
    }
    $stmt->close();
} else {
    header('location: kelola_umpan_balik.php');
}
$conn->close();
exit();
?>