<?php
session_start();
include 'koneksi.php'; // Menghubungkan dengan koneksi database PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form login
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Query untuk memeriksa apakah email ada di tabel pasien
        $sql = "SELECT * FROM pasien WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR); // Binding parameter untuk mencegah SQL Injection
        $stmt->execute();

        // Cek apakah pengguna ditemukan di tabel pasien
        if ($stmt->rowCount() > 0) {
            // Ambil data pengguna
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Periksa apakah password cocok
            if ($password == $user['password']) {
                $_SESSION['nik'] = $user['nik']; // Simpan NIK ke session
                $_SESSION['role'] = 'pasien';    // Tentukan role sebagai pasien
                header("Location: pages/pasien/pasien_dashboard.php"); // Arahkan ke dashboard pasien
                exit();
            } else {
                echo "Password salah!";
            }
        } else {
            // Cek apakah pengguna ada di tabel dokter
            $sql = "SELECT * FROM dokter WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // Cek apakah dokter ditemukan
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($password == $user['password']) {
                    $_SESSION['id_dokter'] = $user['id_dokter']; // Simpan ID Dokter ke session
                    $_SESSION['role'] = 'dokter';    // Tentukan role sebagai dokter
                    header("Location: pages/dokter/dokter_dashboard.php"); // Arahkan ke dashboard dokter
                    exit();
                } else {
                    echo "Password salah!";
                }
            } else {
                // Cek apakah pengguna ada di tabel admin
                $sql = "SELECT * FROM admin WHERE email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                // Cek apakah admin ditemukan
                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($password == $user['password']) {
                        $_SESSION['id_admin'] = $user['id_admin']; // Simpan ID Admin ke session
                        $_SESSION['role'] = 'admin';    // Tentukan role sebagai admin
                        header("Location: pages/admin/admin_dashboard.php"); // Arahkan ke dashboard admin
                        exit();
                    } else {
                        echo "Password salah!";
                    }
                } else {
                    echo "Email tidak ditemukan!";
                }
            }
        }
    } catch (PDOException $e) {
        echo "Kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    <title>Login - E-Reservasi Rumah Sehat</title>

    <link rel="stylesheet" href="assets/css/maicons.css" />
    <link rel="stylesheet" href="assets/css/bootstrap.css" />
    <link rel="stylesheet" href="assets/vendor/animate/animate.css" />
    <link rel="stylesheet" href="assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  </head>
  <body>



 <!-- Login Start -->
<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow-lg p-4 wow fadeInUp position-relative" style="width: 350px;">
    <div class="card-body">
      <!-- Tombol Kembali di pojok kiri -->
      <button class="btn btn-link position-absolute" style="top: 10px; left: 10px; text-decoration: none; font-size: 24px;">
      <a href="index.php"><i class="fa fa-arrow-left"></i></a>
      </button>

      <!-- Teks LOGIN tetap di tengah -->
      <h3 class="fw-bold text-center" style="font-size: 26px;">LOGIN</h3>

      <form action="login.php" method="POST" class="mt-4">
        <div class="form-group ">
          <label for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Masukan email anda" required />
        </div>
        <div class="form-group ">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Masukan password anda" required />
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-3">Masuk</button>
      </form>
    </div>
  </div>
</div>
<!-- Login End -->

    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/wow/wow.min.js"></script>
    <script src="assets/js/theme.js"></script>
  </body>
</html>
