<?php
session_start();
include '../../koneksi.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: ../../login.php");
  exit();
}
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
  session_destroy();
  unset($_SESSION);
  header("Location: ../../login.php");
  exit();
}

try {
  $query_dokter = $conn->query("SELECT COUNT(*) AS total FROM dokter");
  $data_dokter = $query_dokter->fetch(PDO::FETCH_ASSOC);
  $total_dokter = $data_dokter['total'];

  $query_pasien = $conn->query("SELECT COUNT(*) AS total FROM pasien");
  $data_pasien = $query_pasien->fetch(PDO::FETCH_ASSOC);
  $total_pasien = $data_pasien['total'];

  $query_reservasi = $conn->query("SELECT COUNT(*) AS total FROM reservasi");
  $data_reservasi = $query_reservasi->fetch(PDO::FETCH_ASSOC);
  $total_reservasi = $data_reservasi['total'];
} catch (PDOException $e) {
  die("Query gagal: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Reservasi Rumah Sehat</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/animate/animate.css" />
    <link rel="stylesheet" href="../../assets/css/theme.css" />
    <script src="../../assets/vendor/wow/wow.min.js"></script>
    <script>new WOW().init();</script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <a class="navbar-brand" href="#">Rumah Sehat</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="view_pasien.php">Kelola Pasien</a></li>
                <li class="nav-item"><a class="nav-link" href="view_dokter.php">Kelola Dokter</a></li>
                <li class="nav-item"><a class="nav-link" href="view_reservasi.php">Kelola Reservasi</a></li>
                <li class="nav-item">
                    <a class="nav-link btn btn-light text-primary px-3" href="admin_dashboard.php?logout=true">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="content wow fadeInRight">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">Dashboard Admin</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="card shadow-sm p-3 mb-3 wow fadeInUp">
                            <h3 class="text-primary"> <?php echo $total_pasien; ?> </h3>
                            <p class="font-weight-bold">Total Pasien</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm p-3 mb-3 wow fadeInUp">
                            <h3 class="text-primary"> <?php echo $total_dokter; ?> </h3>
                            <p class="font-weight-bold">Total Dokter</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm p-3 mb-3 wow fadeInUp">
                            <h3 class="text-primary"> <?php echo $total_reservasi; ?> </h3>
                            <p class="font-weight-bold">Total Reservasi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
    <p id="copyright">
          Copyright &copy; 2025 <a href="#" target="_blank">RumahSehat</a>
        </p>
    </footer>
    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
