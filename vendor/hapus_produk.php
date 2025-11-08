<?php
// 1. Panggil "Satpam" Vendor
require_once 'cek_vendor.php'; 
// Jika lolos, kita akan punya $toko_id_vendor dan $conn

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    // 2. Ambil path gambar HANYA JIKA produk ini milik vendor
    $sql_get_img = "SELECT image_url FROM products WHERE product_id = ? AND toko_id = ?";
    $stmt_get_img = $conn->prepare($sql_get_img);
    $stmt_get_img->bind_param("ii", $product_id, $toko_id_vendor);
    $stmt_get_img->execute();
    $result_img = $stmt_get_img->get_result();

    if ($result_img->num_rows > 0) {
        $row = $result_img->fetch_assoc();
        $gambar_lama = $row['image_url'];
        
        // 3. Hapus produk dari DB HANYA JIKA milik vendor ini
        $sql_delete = "DELETE FROM products WHERE product_id = ? AND toko_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $product_id, $toko_id_vendor);

        if ($stmt_delete->execute()) {
            // 4. Jika hapus DB berhasil, Hapus file gambar
            if (!empty($gambar_lama) && file_exists('../' . $gambar_lama)) {
                unlink('../' . $gambar_lama);
            }
            header('location: kelola_produk.php?status=hapus_sukses');
            exit();
        } else {
            // Gagal (kemungkinan karena Foreign Key dari order_details)
            header("location: kelola_produk.php?status=hapus_gagal&error=" . urlencode($conn->error));
            exit();
        }
        $stmt_delete->close();
    } else {
        // Jika produk bukan milik vendor ini
        header('location: kelola_produk.php?status=gagal&error=Akses ditolak');
        exit();
    }
    $stmt_get_img->close();
} else {
    header('location: kelola_produk.php?status=id_tidak_valid');
    exit();
}
?>