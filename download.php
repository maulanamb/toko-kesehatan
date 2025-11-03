<?php
session_start();

// 1. Keamanan: Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// 2. Ambil nama file dari URL
$file_path_relatif = $_GET['file'] ?? '';

// 3. Keamanan: Validasi nama file (SANGAT PENTING!)
// Ini untuk mencegah seseorang mencoba men-download file lain (misal: download.php?file=../koneksi.php)
// Kita pastikan path-nya HARUS dimulai dengan 'invoices/' dan tidak mengandung '..' (path traversal)
if (strpos($file_path_relatif, 'invoices/') !== 0 || strpos($file_path_relatif, '..') !== false) {
    die("Error: Lokasi file tidak valid.");
}

// 4. Tentukan path fisik lengkap di server
$path_fisik_file = __DIR__ . '/' . $file_path_relatif;

// 5. Cek apakah file benar-benar ada
if (file_exists($path_fisik_file)) {
    
    // 6. Siapkan Header untuk "Download Paksa"
    
    // Tentukan tipe konten
    header('Content-Type: application/pdf');
    
    // Tampilkan dialog "Save As..." dan beri nama file
    header('Content-Disposition: attachment; filename="' . basename($path_fisik_file) . '"');
    
    // Nonaktifkan caching
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Beri tahu browser ukuran file
    header('Content-Length: ' . filesize($path_fisik_file));
    
    // 7. Baca file dan kirim ke browser
    // ob_clean() dan flush() membersihkan output buffer jika ada
    ob_clean();
    flush();
    readfile($path_fisik_file);
    exit; // Hentikan script
    
} else {
    die("Error: File yang Anda minta tidak dapat ditemukan di server.");
}
?>