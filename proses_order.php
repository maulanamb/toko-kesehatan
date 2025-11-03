<?php
session_start();
require_once 'koneksi.php';
require_once 'fpdf/fpdf.php'; // Panggil library FPDF

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Ambil data dari Form Checkout (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total_amount = $_POST['total_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? 'Unknown';
    $shipping_address = $_POST['shipping_address'] ?? 'No address';

    // Ambil data keranjang dari DB (untuk disimpan ke order_details)
    $sql_cart = "SELECT p.product_id, p.price, c.quantity 
                 FROM cart_items c
                 JOIN products p ON c.product_id = p.product_id
                 WHERE c.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_items = $stmt_cart->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_cart->close();

    if (count($cart_items) == 0) {
        header("Location: keranjang.php?error=Keranjang kosong");
        exit();
    }

    // --- 3. MULAI TRANSAKSI DATABASE (PENTING!) ---
    $conn->begin_transaction();

    try {
        // --- Langkah A: Masukkan ke tabel 'orders' ---
        $sql_order = "INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, status) 
                      VALUES (?, ?, ?, ?, 'Paid')";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("isss", $user_id, $total_amount, $payment_method, $shipping_address);
        $stmt_order->execute();
        
        $order_id = $conn->insert_id;
        $stmt_order->close();

        // --- Langkah B: Masukkan setiap item ke 'order_details' ---
        $sql_details = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) 
                          VALUES (?, ?, ?, ?)";
        $stmt_details = $conn->prepare($sql_details);
        
        // Siapkan statement untuk update stok (akan digunakan di dalam loop)
        $sql_update_stok = "UPDATE products SET stock = stock - ? 
                            WHERE product_id = ? AND stock >= ?";
        $stmt_stok = $conn->prepare($sql_update_stok);

        foreach ($cart_items as $item) {
            // Masukkan ke order_details
            $stmt_details->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $stmt_details->execute();

            // --- ▼▼▼ INI KODE TAMBAHAN UNTUK MENGURANGI STOK ▼▼▼ ---
            
            // Kurangi stok
            // bind_param("iii"): (kurangi berapa, untuk id berapa, HANYA JIKA stok >= dari berapa)
            $stmt_stok->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
            $stmt_stok->execute();
            
            // Cek apakah stok berhasil dikurangi
            if ($stmt_stok->affected_rows === 0) {
                // Jika affected_rows = 0, berarti stok tidak cukup (WHERE stock >= ? gagal)
                // Kita paksa error agar transaksi di-rollback
                throw new Exception("Stok tidak mencukupi untuk produk ID: " . $item['product_id']);
            }
            // --- ▲▲▲ SELESAI KODE TAMBAHAN ▲▲▲ ---
        }
        $stmt_details->close();
        $stmt_stok->close(); // Jangan lupa tutup statement stok

        // --- Langkah C: Kosongkan keranjang 'cart_items' ---
        $sql_clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt_clear = $conn->prepare($sql_clear_cart);
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();
        $stmt_clear->close();

        // --- Langkah D: Commit transaksi ---
        $conn->commit();

        // --- 4. MEMBUAT LAPORAN PDF ---
        
        // Ambil data lengkap untuk PDF
        $sql_pdf = "SELECT p.product_name, od.quantity, od.price_at_purchase 
                    FROM order_details od
                    JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = ?";
        $stmt_pdf = $conn->prepare($sql_pdf);
        $stmt_pdf->bind_param("i", $order_id);
        $stmt_pdf->execute();
        $pdf_items = $stmt_pdf->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_pdf->close();

        // Buat objek PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Judul
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(190, 10, 'Laporan Pembelian - Toko Alat Kesehatan', 1, 1, 'C');
        $pdf->Ln(10); // Jarak

        // Info Order
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 7, 'Nomor Order:');
        $pdf->Cell(100, 7, $order_id);
        $pdf->Ln();
        $pdf->Cell(40, 7, 'Tanggal:');
        $pdf->Cell(100, 7, date('d-m-Y H:i:s'));
        $pdf->Ln();
        $pdf->Cell(40, 7, 'Alamat Kirim:');
        $pdf->Cell(100, 7, $shipping_address);
        $pdf->Ln();
        $pdf->Cell(40, 7, 'Metode Bayar:');
        $pdf->Cell(100, 7, $payment_method);
        $pdf->Ln(10);

        // Header Tabel Item
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 8, 'Nama Produk', 1);
        $pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Harga', 1, 0, 'R');
        $pdf->Cell(30, 8, 'Subtotal', 1, 0, 'R');
        $pdf->Ln();
        
        // Isi Tabel Item
        $pdf->SetFont('Arial', '', 12);
        foreach ($pdf_items as $item) {
            $subtotal = $item['price_at_purchase'] * $item['quantity'];
            $pdf->Cell(100, 7, $item['product_name'], 1);
            $pdf->Cell(30, 7, $item['quantity'], 1, 0, 'C');
            $pdf->Cell(30, 7, 'Rp ' . number_format($item['price_at_purchase']), 1, 0, 'R');
            $pdf->Cell(30, 7, 'Rp ' . number_format($subtotal), 1, 0, 'R');
            $pdf->Ln();
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(160, 8, 'Total Belanja (termasuk pajak):', 1, 0, 'R');
        $pdf->Cell(30, 8, 'Rp ' . number_format($total_amount), 1, 0, 'R');
        $pdf->Ln();
        
        // Simpan file PDF ke folder 'invoices'
        $folder_invoice = __DIR__ . '/invoices/'; 
        $nama_file_pdf = "invoice_order_" . $order_id . ".pdf";
        $path_lengkap_file = $folder_invoice . $nama_file_pdf;
        
        $pdf->Output('F', $path_lengkap_file); // 'F' = Simpan ke file

        // --- 5. Redirect ke Halaman Sukses ---
        
        header("Location: order_sukses.php?order_id=" . $order_id . "&pdf=invoices/" . $nama_file_pdf);
        exit();

    } catch (Exception $exception) { // Menangkap semua jenis Exception
        // --- Langkah E: Jika ada error (termasuk stok), rollback ---
        $conn->rollback();
        
        // Kirim user kembali ke keranjang dengan pesan error
        $error_message = urlencode($exception->getMessage());
        if (strpos($error_message, 'Stok tidak mencukupi') !== false) {
             header("Location: keranjang.php?error=Stok tidak mencukupi untuk salah satu barang.");
        } else {
             header("Location: keranjang.php?error=Gagal memproses pesanan: " . $error_message);
        }
        exit();
    }
    
    $conn->close();

} else {
    // Jika bukan POST, tendang balik
    header("Location: index.php");
    exit();
}
?>