<?php
// 1. Memanggil file koneksi.php
require_once 'koneksi.php'; // $conn akan tersedia dari sini

$pesan_sukses = "";
$pesan_error = "";

// 2. Cek apakah form sudah di-submit (ditekan tombol "Submit")
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Ambil semua data dari form (Validasi sederhana)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $retype_password = $_POST['retype_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $paypal_id = $_POST['paypal_id'] ?? '';

    // 4. Validasi Sederhana
    if (empty($username) || empty($password) || empty($email)) {
        $pesan_error = "Username, Password, dan E-mail wajib diisi.";
    } elseif ($password !== $retype_password) {
        $pesan_error = "Password dan Retype-Password tidak cocok.";
    } else {
        
        // 5. Cek apakah username atau email sudah ada
        $sql_cek = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt_cek = $conn->prepare($sql_cek);
        $stmt_cek->bind_param("ss", $username, $email);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();

        if ($result_cek->num_rows > 0) {
            $pesan_error = "Username atau E-mail sudah terdaftar.";
        } else {
            
            // 6. HASH PASSWORD (KRITIS!)
            // Ini adalah bagian penting untuk keamanan.
            // Kita menyimpan hash-nya, bukan password aslinya.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 7. Siapkan query INSERT (Gunakan Prepared Statement)
            $sql = "INSERT INTO users (username, password, email, date_of_birth, gender, address, city, contact_no, paypal_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            // 's' = string, 's' = date (sebagai string)
            $stmt->bind_param("sssssssss", 
                $username, 
                $hashed_password, 
                $email, 
                $date_of_birth, 
                $gender, 
                $address, 
                $city, 
                $contact_no, 
                $paypal_id
            );

            // 8. Eksekusi query
            if ($stmt->execute()) {
                $pesan_sukses = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $pesan_error = "Registrasi gagal. Error: " . $stmt->error;
            }
            
            $stmt->close();
        }
        $stmt_cek->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Registrasi - Toko Alat Kesehatan</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; max-width: 500px; }
        div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"], input[type="email"], input[type="date"] {
            width: 100%; padding: 8px; box-sizing: border-box;
        }
        .message-sukses { color: green; border: 1px solid green; padding: 10px; margin-bottom: 10px; }
        .message-error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>

    <h1>FORM REGISTRASI</h1>
    
    <?php if (!empty($pesan_sukses)): ?>
        <div class="message-sukses"><?php echo $pesan_sukses; ?></div>
    <?php endif; ?>
    <?php if (!empty($pesan_error)): ?>
        <div class="message-error"><?php echo $pesan_error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div>
            <label for="username">Username :</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password :</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="retype_password">Retype-Password :</label>
            <input type="password" id="retype_password" name="retype_password" required>
        </div>
        <div>
            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="date_of_birth">Date of birth :</label>
            <input type="date" id="date_of_birth" name="date_of_birth">
        </div>
        <div>
            <label>Gender :</label>
            <input type="radio" id="male" name="gender" value="Male">
            <label for="male" style="display:inline;">Male</label>
            <input type="radio" id="female" name="gender" value="Female">
            <label for="female" style="display:inline;">Female</label>
        </div>
        <div>
            <label for="address">Address :</label>
            <input type="text" id="address" name="address">
        </div>
        <div>
            <label for="city">City :</label>
            <input type="text" id="city" name="city">
        </div>
        <div>
            <label for="contact_no">Contact no :</label>
            <input type="text" id="contact_no" name="contact_no">
        </div>
        <div>
            <label for="paypal_id">Pay-pal id :</label>
            <input type="text" id="paypal_id" name="paypal_id">
        </div>
        <div>
            <button type="submit">Submit</button>
            <button type="reset">Clear</button>
        </div>
    </form>

</body>
</html>