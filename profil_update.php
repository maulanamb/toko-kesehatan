<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];

// 2. Cek apakah metode adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Ambil semua data dari form (kecuali username)
    $email = $_POST['email'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $paypal_id = $_POST['paypal_id'] ?? '';

    // 4. Validasi sederhana
    if (empty($email)) {
        header("Location: profil.php?error=E-mail tidak boleh kosong");
        exit();
    }
    
    // 5. Cek apakah email baru sudah dipakai orang lain
    $sql_cek = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
    $stmt_cek = $conn->prepare($sql_cek);
    $stmt_cek->bind_param("si", $email, $user_id);
    $stmt_cek->execute();
    $result_cek = $stmt_cek->get_result();

    if ($result_cek->num_rows > 0) {
        header("Location: profil.php?error=E-mail tersebut sudah terdaftar");
        exit();
    }
    $stmt_cek->close();

    // 6. Siapkan query UPDATE
    $sql_update = "UPDATE users SET 
                    email = ?, 
                    date_of_birth = ?, 
                    gender = ?, 
                    address = ?, 
                    city = ?, 
                    contact_no = ?, 
                    paypal_id = ?
                   WHERE 
                    user_id = ?";
                    
    $stmt_update = $conn->prepare($sql_update);
    // 's' = string, 'i' = integer
    $stmt_update->bind_param("sssssssi", 
        $email, 
        $date_of_birth, 
        $gender, 
        $address, 
        $city, 
        $contact_no, 
        $paypal_id, 
        $user_id
    );

    // 7. Eksekusi query
    if ($stmt_update->execute()) {
        header("Location: profil.php?sukses=Profil berhasil diperbarui");
    } else {
        header("Location: profil.php?error=Gagal memperbarui profil: " . $conn->error);
    }
    
    $stmt_update->close();
    $conn->close();

} else {
    // Jika bukan POST, tendang balik
    header("Location: profil.php");
    exit();
}
?>