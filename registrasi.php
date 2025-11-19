<?php
require_once 'koneksi.php'; 

$pesan_sukses = "";
$pesan_error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    if (empty($username) || empty($password) || empty($email)) {
        $pesan_error = "Username, Password, dan E-mail wajib diisi.";
    } elseif ($password !== $retype_password) {
        $pesan_error = "Password dan Retype-Password tidak cocok.";
    } else {
        
        $sql_cek = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt_cek = $conn->prepare($sql_cek);
        $stmt_cek->bind_param("ss", $username, $email);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();

        if ($result_cek->num_rows > 0) {
            $pesan_error = "Username atau E-mail sudah terdaftar.";
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password, email, date_of_birth, gender, address, city, contact_no, paypal_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
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

            if ($stmt->execute()) {
                $pesan_sukses = "Registrasi berhasil! Silakan <a href='login.php' class='alert-link'>login</a>.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Registrasi</title>
    <link rel="icon" type="image/png" href="images/minilogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
<body style="background-image: linear-gradient( 135deg, #007bff, #198754);">

    <div class="container">
        <div class="row justify-content-center" style="padding-top: 50px; padding-bottom: 50px;">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="h3 fw-bold">Buat Akun Baru</h2>
                            <p class="text-muted">Silakan isi data diri Anda.</p>
                        </div>

                        <?php if (!empty($pesan_sukses)): ?>
                            <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($pesan_error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($pesan_error); ?></div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="retype_password" class="form-label">Ulangi Password:</label>
                                <input type="password" id="retype_password" name="retype_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Tanggal Lahir:</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin:</label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="male" name="gender" value="Male" class="form-check-input">
                                    <label for="male" class="form-check-label">Laki-laki</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" id="female" name="gender" value="Female" class="form-check-input">
                                    <label for="female" class="form-check-label">Perempuan</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat:</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">Kota:</label>
                                <input type="text" id="city" name="city" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="contact_no" class="form-label">No. Kontak:</label>
                                <input type="text" id="contact_no" name="contact_no" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="paypal_id" class="form-label">Pay-pal id:</label>
                                <input type="text" id="paypal_id" name="paypal_id" class="form-control">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">DAFTAR</button>
                                <button type="reset" class="btn btn-outline-secondary">Clear</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                            <p class="mt-2"><a href="index.php" class="text-decoration-none">&laquo; Kembali ke Beranda</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>