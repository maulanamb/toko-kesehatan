<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

require_once 'koneksi.php';
require_once 'fpdf/fpdf.php';
require_once 'config_email.php'; 
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username_akun = $_SESSION['username'] ?? 'Pelanggan'; // Nama asli akun (untuk backup)

// Ambil email user dari DB
$stmt_email = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$user_email_result = $stmt_email->get_result();
$user_email = ($user_email_result->num_rows > 0) ? $user_email_result->fetch_assoc()['email'] : '';
$stmt_email->close();

// 2. Cek data POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $total_amount = $_POST['total_amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? 'Unknown';
    $shipping_address = $_POST['shipping_address'] ?? 'No address';
    
    // ▼▼▼ AMBIL NAMA PENERIMA DARI FORM ▼▼▼
    // Jika tidak diisi, gunakan username akun sebagai default
    $recipient_name = !empty($_POST['recipient_name']) ? $_POST['recipient_name'] : $username_akun;
    // ▲▲▲ SELESAI ▲▲▲

    // 3. Ambil data keranjang (Dari Database)
    $sql_cart = "SELECT p.product_id, p.product_name, p.price, c.quantity, p.toko_id 
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

    // --- 4. MULAI TRANSAKSI DATABASE ---
    $conn->begin_transaction();
    $order_id = 0; 

    try {
        // --- Langkah A: Insert order ---
        // ▼▼▼ UPDATE: Tambah recipient_name & Ubah status jadi 'Pending' ▼▼▼
        $sql_order = "INSERT INTO orders (user_id, recipient_name, total_amount, payment_method, shipping_address, status) 
                      VALUES (?, ?, ?, ?, ?, 'Pending')";
        
        $stmt_order = $conn->prepare($sql_order);
        // "isdss" = integer, string, double, string, string
        $stmt_order->bind_param("isdss", $user_id, $recipient_name, $total_amount, $payment_method, $shipping_address);
        // ▲▲▲ SELESAI ▲▲▲
        
        $stmt_order->execute();
        $order_id = $conn->insert_id; 
        $stmt_order->close();

        // --- Langkah B: Insert order_details & Kurangi Stok ---
        $sql_details = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase, status_vendor) 
                          VALUES (?, ?, ?, ?, ?)";
        $stmt_details = $conn->prepare($sql_details);
        
        $sql_update_stok = "UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?";
        $stmt_stok = $conn->prepare($sql_update_stok);

        foreach ($cart_items as $item) {
            $status_vendor_item = (is_null($item['toko_id'])) ? 'Approved' : 'Pending';
            $stmt_details->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $item['price'], $status_vendor_item);
            $stmt_details->execute();
            
            $stmt_stok->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
            $stmt_stok->execute();
            
            // Cek apakah stok cukup (affected_rows akan 0 jika stok < quantity karena kondisi WHERE)
            if ($stmt_stok->affected_rows === 0) {
                throw new Exception("Stok tidak mencukupi untuk produk: " . $item['product_name']);
            }
        }
        $stmt_details->close();
        $stmt_stok->close(); 

        // --- Langkah C: Kosongkan keranjang ---
        // Hapus dari Database
        $sql_clear_cart = "DELETE FROM cart_items WHERE user_id = ?";
        $stmt_clear = $conn->prepare($sql_clear_cart);
        $stmt_clear->bind_param("i", $user_id);
        $stmt_clear->execute();
        $stmt_clear->close();
        
        // Hapus dari Session juga (untuk konsistensi)
        if (isset($_SESSION['keranjang'])) {
            unset($_SESSION['keranjang']);
        }

        // --- Langkah D: Commit transaksi ---
        $conn->commit();

    } catch (Exception $exception) { 
        $conn->rollback();
        header("Location: keranjang.php?error=Gagal memproses pesanan: " . urlencode($exception->getMessage()));
        exit();
    }
    
    // --- 6. MEMBUAT LAPORAN PDF ---
    $sql_pdf = "SELECT p.product_name, od.quantity, od.price_at_purchase 
                FROM order_details od
                JOIN products p ON od.product_id = p.product_id
                WHERE od.order_id = ?";
    $stmt_pdf = $conn->prepare($sql_pdf);
    $stmt_pdf->bind_param("i", $order_id);
    $stmt_pdf->execute();
    $pdf_items = $stmt_pdf->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_pdf->close();
    $conn->close(); 

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, 'Invoice - Toko Kesehatan Purnama', 1, 1, 'C');
    $pdf->Ln(10); 
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 7, 'Nomor Order:', 0); $pdf->Cell(100, 7, '#'.$order_id, 0); $pdf->Ln();
    
    // ▼▼▼ UPDATE PDF: Gunakan Nama Penerima Baru ▼▼▼
    $pdf->Cell(40, 7, 'Nama Penerima:', 0); $pdf->Cell(100, 7, $recipient_name, 0); $pdf->Ln();
    // ▲▲▲ SELESAI ▲▲▲
    
    $pdf->Cell(40, 7, 'Tanggal:', 0); $pdf->Cell(100, 7, date('d-m-Y H:i:s'), 0); $pdf->Ln();
    $pdf->Cell(40, 7, 'Alamat Kirim:', 0); $pdf->Cell(100, 7, $shipping_address, 0); $pdf->Ln();
    $pdf->Cell(40, 7, 'Metode Bayar:', 0); $pdf->Cell(100, 7, $payment_method, 0); $pdf->Ln(10);
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(100, 8, 'Nama Produk', 1);
    $pdf->Cell(30, 8, 'Jumlah', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Harga', 1, 0, 'R');
    $pdf->Cell(30, 8, 'Subtotal', 1, 0, 'R');
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 12);
    foreach ($pdf_items as $item) {
        $subtotal = $item['price_at_purchase'] * $item['quantity'];
        $pdf->Cell(100, 7, $item['product_name'], 1); 
        $pdf->Cell(30, 7, $item['quantity'], 1, 0, 'C');
        $pdf->Cell(30, 7, 'Rp ' . number_format($item['price_at_purchase']), 1, 0, 'R');
        $pdf->Cell(30, 7, 'Rp ' . number_format($subtotal), 1, 0, 'R');
        $pdf->Ln();
    }
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(160, 8, 'Total Belanja (termasuk pajak):', 1, 0, 'R');
    $pdf->Cell(30, 8, 'Rp ' . number_format($total_amount), 1, 0, 'R');
    $pdf->Ln();
    
    $folder_invoice = __DIR__ . '/invoices/'; 
    if (!is_dir($folder_invoice)) mkdir($folder_invoice, 0777, true); 
    $nama_file_pdf = "invoice_order_" . $order_id . ".pdf";
    $path_lengkap_file = $folder_invoice . $nama_file_pdf;
    $pdf->Output('F', $path_lengkap_file); 

    // --- 7. KIRIM INVOICE PDF KE EMAIL ---
    if (!empty($user_email) && MAIL_PASSWORD != 'KUNCI_SMTP_DARI_BREVO') {
        $mail = new PHPMailer(true);
        try {
            // $mail->SMTPDebug = 2; 
            
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(MAIL_SENDER_EMAIL, MAIL_SENDER_NAME); 
            $mail->addAddress($user_email, $username_akun); 

            $mail->addAttachment($path_lengkap_file, $nama_file_pdf);

            $mail->isHTML(true);
            $mail->Subject = 'Invoice Pesanan Anda #' . $order_id . ' dari Toko Kesehatan';
            
            // ▼▼▼ UPDATE EMAIL: Gunakan Nama Penerima Baru ▼▼▼
            $mail->Body    = "Halo " . htmlspecialchars($recipient_name) . ",<br><br>Terima kasih atas pesanan Anda! Pesanan Anda dengan nomor <b>#" . $order_id . "</b> sedang kami proses.<br><br>Terlampir adalah invoice untuk pesanan Anda.<br><br>Salam,<br>" . MAIL_SENDER_NAME;
            // ▲▲▲ SELESAI ▲▲▲
            
            $mail->send();
            
        } catch (Exception $e) {
            // error_log("Gagal kirim email: {$mail->ErrorInfo}");
        }
    }

    // --- 8. Redirect ke Halaman Sukses ---
    $pdf_url_path = "invoices/" . $nama_file_pdf;
    header("Location: order_sukses.php?order_id=" . $order_id . "&pdf=" . $pdf_url_path);
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>