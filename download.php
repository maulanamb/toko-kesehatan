<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$file_path_relatif = $_GET['file'] ?? '';


if (strpos($file_path_relatif, 'invoices/') !== 0 || strpos($file_path_relatif, '..') !== false) {
    die("Error: Lokasi file tidak valid.");
}

$path_fisik_file = __DIR__ . '/' . $file_path_relatif;

if (file_exists($path_fisik_file)) {
    
    // Tentukan tipe konten
    header('Content-Type: application/pdf');
    
    header('Content-Disposition: attachment; filename="' . basename($path_fisik_file) . '"');
    
    // Nonaktifkan caching
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Beri tahu browser ukuran file
    header('Content-Length: ' . filesize($path_fisik_file));
    
    // ob_clean() dan flush() membersihkan output buffer jika ada
    ob_clean();
    flush();
    readfile($path_fisik_file);
    exit; 
    
} else {
    die("Error: File yang Anda minta tidak dapat ditemukan di server.");
}
?>