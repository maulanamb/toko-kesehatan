<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// $pdf_file_path akan berisi "invoices/invoice_order_X.pdf"
$order_id = $_GET['order_id'] ?? 'N/A';
$pdf_file_path = $_GET['pdf'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; }
        .container { border: 2px solid #28a745; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; }
        h1 { color: #28a745; }
        .pdf-download {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>✅ Pesanan Berhasil!</h1>
        <p>Terima kasih atas pembelian Anda.</p>
        <p>Nomor Order Anda adalah: <strong><?php echo htmlspecialchars($order_id); ?></strong></p>
        <p>Laporan pembelian Anda dalam bentuk PDF telah berhasil dibuat.</p>
        
        <?php
        // Cek file menggunakan path fisik di server
        $path_fisik_file = __DIR__ . '/' . $pdf_file_path;
        
        if (!empty($pdf_file_path) && file_exists($path_fisik_file)):
        ?>
            <a href="download.php?file=<?php echo urlencode($pdf_file_path); ?>" class="pdf-download">
                Download Laporan PDF
            </a>
        <?php else: ?>
            <p style="color: red;">Error: File PDF tidak dapat ditemukan.</p>
        <?php endif; ?>

        <br><br>
        <a href="index.php">Kembali ke Halaman Produk</a>
    </div>

</body>
</html>