<?php
session_start();
require_once 'koneksi.php'; // Pastikan $conn

// 1. "Satpam" untuk Customer
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
    header("Location: login.php?error=Silakan login sebagai pelanggan.");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Ambil ID Pesanan dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id === 0) {
    header('location: riwayat_pesanan.php?error=ID pesanan tidak valid');
    exit();
}

// 3. Cek Keamanan: Pastikan pesanan ini milik user yang login DAN statusnya "Selesai"
// ▼▼▼ PERBAIKAN DI SINI (Baris 21) ▼▼▼
$sql_cek = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ? AND status = 'Selesai'";
// ▲▲▲ SELESAI PERBAIKAN ▲▲▲
$stmt_cek = $conn->prepare($sql_cek);
$stmt_cek->bind_param("ii", $order_id, $user_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();
if ($result_cek->num_rows == 0) {
    // Jika pesanan tidak ditemukan, bukan milik user, atau belum selesai
    header('location: riwayat_pesanan.php?error=Pesanan tidak valid atau belum selesai.');
    exit();
}
$stmt_cek->close();

// 4. Cek Keamanan: Pastikan belum pernah memberi ulasan
$sql_cek_fb = "SELECT id FROM feedback WHERE order_id = ?";
$stmt_cek_fb = $conn->prepare($sql_cek_fb);
$stmt_cek_fb->bind_param("i", $order_id);
$stmt_cek_fb->execute();
$result_cek_fb = $stmt_cek_fb->get_result();
if ($result_cek_fb->num_rows > 0) {
    header('location: riwayat_pesanan.php?error=Anda sudah memberi ulasan untuk pesanan ini.');
    exit();
}
$stmt_cek_fb->close();


// 5. Logika saat form DISIMPAN (POST)
$pesan_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = (int)$_POST['rating'];
    $komentar = $conn->real_escape_string($_POST['komentar']);
    
    // Validasi rating
    if ($rating < 1 || $rating > 5) {
        $pesan_error = "Silakan pilih rating bintang 1 sampai 5.";
    } else {
        // Simpan ke database
        $sql_insert = "INSERT INTO feedback (order_id, user_id, rating, komentar) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiis", $order_id, $user_id, $rating, $komentar);
        
        if ($stmt_insert->execute()) {
            header("Location: riwayat_pesanan.php?sukses=" . urlencode("Terima kasih atas ulasan Anda!"));
            exit();
        } else {
            $pesan_error = "Terjadi kesalahan: " . $conn->error;
        }
        $stmt_insert->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Umpan Balik - Pesanan #<?php echo $order_id; ?></title>
    <style>
        body { font-family: sans-serif; background-color: #f9f9f9; padding: 20px; }
        .container { 
            max-width: 600px; 
            margin: auto; 
            background-color: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        h1 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .nav { 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .nav a { 
            margin-right: 15px; 
            text-decoration: none; 
            color: #007bff; 
            font-weight: bold;
        }
        
        /* Form */
        form { margin-top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group textarea { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            height: 120px;
        }
        .btn-submit { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .alert-gagal { color: red; background-color: #f8d7da; border: 1px solid red; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        
        /* Rating Bintang */
        .rating-stars {
            display: inline-block;
            direction: rtl; /* Balik urutan bintang */
        }
        .rating-stars input[type=radio] {
            display: none; /* Sembunyikan radio button asli */
        }
        .rating-stars label {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            padding: 0 2px;
            display: inline-block; /* Penting */
        }
        /* Saat di-hover atau di-check */
        .rating-stars:hover label,
        .rating-stars:hover label ~ label, /* Hover bintang di kirinya */
        .rating-stars input[type=radio]:checked ~ label {
            color: #f0ad4e; /* Warna bintang terisi */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="riwayat_pesanan.php">&laquo; Kembali ke Riwayat Pesanan</a>
        </div>
        
        <h1>Beri Umpan Balik</h1>
        <p>Silakan beri ulasan untuk pesanan Anda <strong>#<?php echo $order_id; ?></strong>.</p>

        <?php 
        if (!empty($pesan_error)) echo "<div class='alert-gagal'>$pesan_error</div>";
        ?>

        <form action="beri_umpan_balik.php?order_id=<?php echo $order_id; ?>" method="POST">
            <div class="form-group">
                <label>Rating Anda:</label>
                <div class="rating-stars">
                    <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="komentar">Komentar Anda (Opsional):</label>
                <textarea id="komentar" name="komentar" placeholder="Bagaimana pengalaman Anda dengan produk dan layanan kami?"></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Kirim Umpan Balik</button>
        </form>
    </div>
</body>
</html>