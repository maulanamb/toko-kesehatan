<?php
require_once 'cek_admin.php'; // Pastikan satpam aktif
require_once '../koneksi.php'; // Pastikan $conn

$id_pesan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesan > 0) {
    $sql = "DELETE FROM buku_tamu WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pesan);
    
    if ($stmt->execute()) {
        header('location: kelola_buku_tamu.php?status=hapus_sukses');
    } else {
        header('location: kelola_buku_tamu.php?status=hapus_gagal');
    }
    $stmt->close();
} else {
    header('location: kelola_buku_tamu.php');
}
$conn->close();
exit();
?>