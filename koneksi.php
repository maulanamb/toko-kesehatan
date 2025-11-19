<?php
// Atur zona waktu ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "pass"; // Sesuaikan dengan password database Anda (biasanya kosong di XAMPP/Laragon default)
$DB_NAME = "db_tokokesehatan"; 

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>