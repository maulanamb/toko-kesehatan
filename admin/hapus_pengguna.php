<?php
session_start();
// Pastikan Anda mengaktifkan 'cek_admin.php' saat sudah production
require_once 'cek_admin.php'; 

require_once '../koneksi.php'; 

// 1. Ambil ID dari URL
$user_id_to_delete = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Ambil ID admin yang sedang login dari session
//    GANTI 'admin_id' dengan nama session Anda yang benar
$admin_id_logged_in = $_SESSION['admin_user_id'] ?? 0; // Asumsi 'admin_user_id'

// 3. PENGAMAN: Cek agar admin tidak menghapus diri sendiri
if ($user_id_to_delete === $admin_id_logged_in) {
    header('location: kelola_pengguna.php?status=hapus_gagal&error=self');
    exit();
}

// 4. PENGAMAN: Cek agar admin utama (misal ID 1) tidak terhapus
if ($user_id_to_delete === 1) { // Asumsi admin utama punya ID 1
    header('location: kelola_pengguna.php?status=hapus_gagal&error=superadmin');
    exit();
}


if ($user_id_to_delete > 0) {
    // 5. Siapkan query DELETE
    // Peringatan: Ini bisa gagal jika user punya 'orders' (Foreign Key)
    $sql = "DELETE FROM users WHERE user_id = $user_id_to_delete";
    
    // 6. Eksekusi query
    if ($conn->query($sql) === TRUE) {
        header('location: kelola_pengguna.php?status=hapus_sukses');
        exit();
    } else {
        // 7. Jika gagal (kemungkinan karena user sudah punya order)
        $error = urlencode($conn->error);
        header("location: kelola_pengguna.php?status=hapus_gagal&error={$error}");
        exit();
    }
} else {
    header('location: kelola_pengguna.php?status=id_tidak_valid');
    exit();
}
?>