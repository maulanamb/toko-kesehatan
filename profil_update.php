<?php
session_start();

$batas_waktu = 1800; // 30 menit (1800 detik)

if (isset($_SESSION['waktu_terakhir_aktif'])) {
    if (time() - $_SESSION['waktu_terakhir_aktif'] > $batas_waktu) {
        session_unset();
        session_destroy();
        header('location: login.php?error=' . urlencode('Sesi Anda telah berakhir, silakan login kembali.'));
        exit();
    }
}
$_SESSION['waktu_terakhir_aktif'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $paypal_id = $_POST['paypal_id'] ?? '';

    if (empty($email)) {
        header("Location: profil.php?error=E-mail tidak boleh kosong");
        exit();
    }
    
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

    if ($stmt_update->execute()) {
        header("Location: profil.php?sukses=Profil berhasil diperbarui");
    } else {
        header("Location: profil.php?error=Gagal memperbarui profil: " . $conn->error);
    }
    
    $stmt_update->close();
    $conn->close();

} else {
    header("Location: profil.php");
    exit();
}
?>