<?php
session_start();

// Pastikan hanya dokter yang dapat mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'dokter') {
    header("Location: login.php");
    exit();
}

include '../../koneksi.php'; // Koneksi ke database

// Ambil tanggal hari ini
$today = date('Y-m-d'); // Format tanggal YYYY-MM-DD

// Ambil parameter pencarian jika ada
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Ambil parameter pencarian dari URL

// Query untuk mengambil data reservasi berdasarkan pencarian
$sql = "SELECT r.id_reservasi, p.nik, p.nama, p.tanggal_lahir, p.jenis_kelamin, d.nama AS dokter 
        FROM reservasi r
        JOIN pasien p ON r.nik = p.nik
        JOIN dokter d ON r.id_dokter = d.id_dokter
        WHERE d.id_dokter = :id_dokter 
        AND r.status = 'Dikonfirmasi' 
        AND r.tanggal = :today
        AND p.nama LIKE :searchTerm"; // Menambahkan filter pencarian nama pasien
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_dokter', $_SESSION['id_dokter']);
$stmt->bindParam(':today', $today);
$stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR); // Pencarian berdasarkan nama
$stmt->execute();
$reservasiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses logout jika parameter logout ada di URL
if (isset($_GET['logout'])) {
    session_unset(); // Hapus semua sesi
    session_destroy(); // Hancurkan sesi
    header("Location: ../../login.php"); // Redirect ke halaman login
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Dokter Dashboard - E-Reservasi Rumah Sehat</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css" />
    <link rel="stylesheet" href="../../assets/vendor/animate/animate.css" />
    <link rel="stylesheet" href="../../assets/css/theme.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../../assets/vendor/wow/wow.min.js"></script>
    <script>new WOW().init();</script>
    <style>
        /* Gunakan CSS yang sudah ada */
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
                
                <li class="nav-item">
                    <a class="nav-link btn btn-light text-primary px-3" href="dokter_dashboard.php?logout=true">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="content wow fadeInUp">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">Reservasi Anda Hari Ini</div>
            <div class="card-body">
                <!-- Form Pencarian dan Tombol Tambah Reservasi Sejajar -->
                <form method="get" action="dokter_dashboard.php" class="mb-3">
                    <div class="row d-flex justify-content-between">
                        <!-- Kolom Pencarian -->
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="text" name="search" id="searchName" class="form-control" placeholder="Cari Nama Pasien" value="<?php echo htmlspecialchars($searchTerm); ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text" id="searchIcon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Tabel Reservasi -->
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal Lahir</th>
                            <th>Jenis Kelamin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservasiList)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada reservasi yang dikonfirmasi untuk hari ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservasiList as $index => $reservasi): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $reservasi['nik']; ?></td>
                                <td><?php echo $reservasi['nama']; ?></td>
                                <td><?php echo $reservasi['tanggal_lahir']; ?></td>
                                <td><?php echo $reservasi['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    <script>
        new WOW().init();
    </script>
    <script src="../../assets/js/theme.js"></script>
</body>
</html>
