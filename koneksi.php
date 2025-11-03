<?php
/*
 * File: koneksi.php
 * Deskripsi: Menyambungkan aplikasi ke database MySQL
 */

// --- (Sesuaikan dengan setting Laragon Anda) ---
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "pass"; // Password yang Anda sebutkan
$DB_NAME = "db_tokokesehatan"; // Nama database Anda
// ------------------------------------

// Membuat koneksi menggunakan MySQLi
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Mengatur charset ke utf8mb4 (rekomendasi)
$conn->set_charset("utf8mb4");

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>