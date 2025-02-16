<?php
session_start();

// Periksa apakah pengguna sudah login dan memiliki role sebagai pasien
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: login.php"); // Arahkan kembali ke halaman login jika bukan pasien
    exit();
}

include '../../koneksi.php'; // Koneksi ke database

// Ambil data reservasi pasien berdasarkan NIK untuk tanggal yang sudah lewat
$nik = $_SESSION['nik']; // Menggunakan NIK yang disimpan di session untuk mencari data reservasi pasien
$today = date('Y-m-d'); // Tanggal hari ini

// Query untuk mengambil history reservasi (tanggal < hari ini)
$sql = "SELECT r.id_reservasi, r.id_dokter, r.id_poli, r.tanggal, r.status, d.nama AS dokter, p.nama_poli
        FROM reservasi r
        JOIN dokter d ON r.id_dokter = d.id_dokter
        JOIN poli p ON r.id_poli = p.id_poli
        WHERE r.nik = :nik AND r.tanggal < :today";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nik', $nik);
$stmt->bindParam(':today', $today);
$stmt->execute();
$reservasiList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>History Reservasi - E-Reservasi Rumah Sehat</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/animate/animate.css" />
    <link rel="stylesheet" href="../../assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <a class="navbar-brand" href="#">Rumah Sehat</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="pasien_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="history_reservasi.php">History Reservasi</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-primary px-3" href="pasien_dashboard.php?logout=true">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Tabel History Reservasi -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <span>History Reservasi</span>
            </div>
            <div class="card-body">
                <!-- Tabel History Reservasi -->
                <div class="table-container">
                    <?php if (empty($reservasiList)): ?>
                        <p>Tidak ada history reservasi.</p>
                    <?php else: ?>
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Dokter</th>
                                <th>Poli</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="reservasiTableBody">
                            <?php
                            $no = 1;
                            foreach ($reservasiList as $r) : 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($r['dokter']); ?></td>
                                <td><?php echo htmlspecialchars($r['nama_poli']); ?></td>
                                <td><?php echo htmlspecialchars($r['tanggal']); ?></td>
                                <td><?php echo htmlspecialchars($r['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
    <p id="copyright">
          Copyright &copy; 2025 <a href="#" target="_blank">RumahSehat</a>
        </p>
    </footer>

    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
