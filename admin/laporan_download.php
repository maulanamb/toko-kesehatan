<?php
require_once 'cek_admin.php'; 
require_once '../koneksi.php';
require_once '../fpdf/fpdf.php'; 


$bulan_dipilih = $_GET['bulan'] ?? date('m');
$tahun_dipilih = $_GET['tahun'] ?? date('Y');


$tanggal_awal = "$tahun_dipilih-$bulan_dipilih-01 00:00:00";
$tanggal_akhir = date("Y-m-t 23:59:59", strtotime($tanggal_awal));
$nama_bulan_tahun = date('F Y', strtotime($tanggal_awal));


$sql_kpi = "SELECT 
                SUM(total_amount) as total_pendapatan, 
                COUNT(order_id) as jumlah_pesanan
            FROM orders
            WHERE order_date BETWEEN ? AND ?
            AND status != 'Dibatalkan'";
            
$stmt_kpi = $conn->prepare($sql_kpi);
$stmt_kpi->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$stmt_kpi->execute();
$result_kpi = $stmt_kpi->get_result()->fetch_assoc();
$stmt_kpi->close();

$total_pendapatan = $result_kpi['total_pendapatan'] ?? 0;
$jumlah_pesanan = $result_kpi['jumlah_pesanan'] ?? 0;


$sql_produk = "SELECT 
                   p.product_name, 
                   SUM(od.quantity) as total_terjual
               FROM order_details od
               JOIN products p ON od.product_id = p.product_id
               JOIN orders o ON od.order_id = o.order_id
               WHERE o.order_date BETWEEN ? AND ?
               AND o.status != 'Dibatalkan'
               GROUP BY p.product_id, p.product_name
               ORDER BY total_terjual DESC";


$stmt_produk = $conn->prepare($sql_produk);
$stmt_produk->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$stmt_produk->execute();
$produk_terlaris = $stmt_produk->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_produk->close();

$conn->close();

// PDF


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Judul
$pdf->Cell(190, 10, 'Laporan Bulanan - Toko Kesehatan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(190, 10, $nama_bulan_tahun, 0, 1, 'C');
$pdf->Ln(10); // Jarak


$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Jumlah Pesanan Berhasil:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, $jumlah_pesanan, 0, 1);
$pdf->Ln(10); // Jarak

$pdf->SetFont('Arial', 'B', 12);

$pdf->Cell(190, 10, 'Produk yang Terjual di Bulan Ini', 0, 1, 'L');



$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(20, 10, 'No.', 1, 0, 'C');
$pdf->Cell(130, 10, 'Nama Produk', 1, 0, 'L');
$pdf->Cell(40, 10, 'Total Terjual', 1, 1, 'C');

$pdf->SetFont('Arial', '', 11);
if (count($produk_terlaris) > 0) {
    $nomor = 1;
    foreach ($produk_terlaris as $produk) {
        $pdf->Cell(20, 10, $nomor++, 1, 0, 'C');
        $pdf->Cell(130, 10, utf8_decode($produk['product_name']), 1, 0, 'L');
        $pdf->Cell(40, 10, $produk['total_terjual'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(190, 10, 'Tidak ada data penjualan produk.', 1, 1, 'C');
}


$pdf->Ln(10); // Jarak
$pdf->SetFont('Arial', 'B', 14); 
$pdf->Cell(130, 10, 'Total Pendapatan:', 0, 0, 'R'); 
$pdf->Cell(60, 10, 'Rp ' . number_format($total_pendapatan, 0, ',', '.'), 0, 1, 'R'); 


$nama_file = "laporan_" . $tahun_dipilih . "_" . $bulan_dipilih . ".pdf";
$pdf->Output('D', $nama_file);
exit;
?>