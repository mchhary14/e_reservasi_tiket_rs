<?php
session_start();

// Periksa apakah pengguna sudah login dan memiliki role sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php"); // Arahkan kembali ke halaman login jika bukan admin
    exit();
}

include '../../koneksi.php'; // Koneksi ke database

// Ambil data pasien berdasarkan pencarian atau tampilkan semua data pasien
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Ambil parameter pencarian dari URL
$sql = "SELECT * FROM pasien WHERE nik LIKE :searchTerm OR nama LIKE :searchTerm"; 
$stmt = $conn->prepare($sql);
$stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR); 
$stmt->execute();
$pasienList = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete_id_pasien'])) {
    $deleteIdPasien = $_GET['delete_id_pasien'];

    // Menghapus data reservasi yang terkait dengan pasien
    $deleteReservasiSql = "DELETE FROM reservasi WHERE nik = :nik";
    $deleteReservasiStmt = $conn->prepare($deleteReservasiSql);
    $deleteReservasiStmt->bindParam(':nik', $deleteIdPasien);
    $deleteReservasiStmt->execute(); // Menghapus data reservasi terlebih dahulu

    // Menghapus data pasien
    $deleteSql = "DELETE FROM pasien WHERE nik = :nik";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bindParam(':nik', $deleteIdPasien);
    if ($deleteStmt->execute()) {
        header("Location: view_pasien.php"); // Redirect setelah berhasil menghapus
        exit();
    } else {
        echo "Gagal menghapus data pasien.";
    }
}


// Cek jika ada parameter 'nik' untuk menampilkan detail pasien via AJAX
if (isset($_GET['nik'])) {
    $nik = $_GET['nik'];
    // Query untuk mengambil data pasien berdasarkan NIK
    $sql = "SELECT * FROM pasien WHERE nik = :nik";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nik', $nik, PDO::PARAM_STR);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        echo json_encode($patient); // Kirimkan data pasien dalam format JSON
    } else {
        echo json_encode(array('error' => 'Data pasien tidak ditemukan.'));
    }
    exit(); // Hentikan eksekusi PHP setelah mengirim respons JSON
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Kelola Pasien - E-Reservasi Rumah Sehat</title>
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
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="view_pasien.php">Kelola Pasien</a></li>
                <li class="nav-item"><a class="nav-link" href="view_dokter.php">Kelola Dokter</a></li>
                <li class="nav-item"><a class="nav-link" href="view_reservasi.php">Kelola Reservasi</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-primary px-3" href="admin_dashboard.php?logout=true">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="content wow fadeInUp">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">Daftar Pasien</div>
            <div class="card-body">
                <!-- Form Pencarian -->
                <form method="get" action="view_reservasi.php" class="mb-3">
                    <div class="row d-flex justify-content-between">
                        <!-- Kolom Pencarian -->
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="text" name="search" id="searchName" class="form-control" placeholder="Cari Nama" value="<?php echo htmlspecialchars($searchTerm); ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text" id="searchIcon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Lahir</th>
                            <th>Jenis Kelamin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pasienList)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data pasien</td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $no = 1;
                            foreach ($pasienList as $p) : 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($p['nik']); ?></td>
                                <td><?php echo htmlspecialchars($p['nama']); ?></td>
                                <td><?php echo htmlspecialchars($p['email']); ?></td>
                                <td><?php echo htmlspecialchars($p['tanggal_lahir']); ?></td>
                                <td><?php echo htmlspecialchars($p['jenis_kelamin']); ?></td>
                                <td>
                                    <!-- Lihat Detail Button -->
                                    <a href="javascript:void(0);" class="btn btn-info btn-sm" data-toggle="modal" data-target="#patientDetailModal" 
                                       onclick="loadPatientDetails('<?php echo $p['nik']; ?>')">Lihat Detail</a>

                                    <!-- Hapus Button -->
                                    <a href="javascript:void(0);" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteConfirmModal" 
                                       onclick="setDeleteLink('<?php echo $p['nik']; ?>')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        <!-- Modal Detail Pasien -->
        <div class="modal fade" id="patientDetailModal" tabindex="-1" role="dialog" aria-labelledby="patientDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientDetailModalLabel">Detail Pasien</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Nama:</strong> <span id="patientName"></span></p>
                    <p><strong>Email:</strong> <span id="patientEmail"></span></p>
                    <p><strong>Tanggal Lahir:</strong> <span id="patientDOB"></span></p>
                    <p><strong>Jenis Kelamin:</strong> <span id="patientGender"></span></p>
                    <p><strong>Alamat:</strong> <span id="patientAddress"></span></p>
                    <p><strong>No HP:</strong> <span id="patientPhone"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Penghapusan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pasien ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a id="confirmDelete" href="#" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p id="copyright">
            Copyright &copy; 2025 <a href="#" target="_blank">Rumah Sehat</a>
        </p>
    </footer>

    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function setDeleteLink(nik) {
            // Set URL penghapusan di dalam tombol konfirmasi
            document.getElementById('confirmDelete').href = 'view_pasien.php?delete_id_pasien=' + nik;
        }

        function loadPatientDetails(nik) {
            $.ajax({
                url: 'view_pasien.php', // Memanggil file yang sama untuk AJAX
                method: 'GET',
                data: { nik: nik },
                success: function(response) {
                    var patient = JSON.parse(response); // Mengambil data pasien dalam format JSON
                    $('#patientName').text(patient.nama);
                    $('#patientEmail').text(patient.email);
                    $('#patientDOB').text(patient.tanggal_lahir);
                    $('#patientGender').text(patient.jenis_kelamin);
                    $('#patientAddress').text(patient.alamat);
                    $('#patientPhone').text(patient.no_hp);
                }
            });
        }
    </script>
</body>
</html>
