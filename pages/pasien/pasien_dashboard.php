<?php
session_start();

// Periksa apakah pengguna sudah login dan memiliki role sebagai pasien
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: login.php"); // Arahkan kembali ke halaman login jika bukan pasien
    exit();
}
// Logout jika tombol logout ditekan
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
  session_destroy();
  unset($_SESSION);
  header("Location: ../../login.php");
  exit();
}
include '../../koneksi.php'; // Koneksi ke database

// Ambil data reservasi pasien berdasarkan NIK untuk hari ini
$nik = $_SESSION['nik']; // Menggunakan NIK yang disimpan di session untuk mencari data reservasi pasien
$today = date('Y-m-d'); // Tanggal hari ini

// Ambil parameter pencarian (Nama Dokter)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Ambil parameter pencarian dari URL

// Ambil daftar dokter dan poli untuk dropdown
$sqlDokter = "SELECT id_dokter, nama FROM dokter";
$stmtDokter = $conn->prepare($sqlDokter);
$stmtDokter->execute();
$dokterList = $stmtDokter->fetchAll(PDO::FETCH_ASSOC);

$sqlPoli = "SELECT id_poli, nama_poli FROM poli";
$stmtPoli = $conn->prepare($sqlPoli);
$stmtPoli->execute();
$poliList = $stmtPoli->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mencari berdasarkan Nama Dokter dan menampilkan data dengan tanggal >= hari ini
$sql = "SELECT r.id_reservasi, r.id_dokter, r.id_poli, r.tanggal, r.status, d.nama AS dokter, p.nama_poli
        FROM reservasi r
        JOIN dokter d ON r.id_dokter = d.id_dokter
        JOIN poli p ON r.id_poli = p.id_poli
        WHERE r.nik = :nik AND r.tanggal >= :today 
        AND d.nama LIKE :searchTerm";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nik', $nik);
$stmt->bindParam(':today', $today);
$stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);  // Pencarian berdasarkan nama dokter
$stmt->execute();
$reservasiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses hapus reservasi
if (isset($_POST['delete_id_reservasi'])) {
    $response = ['status' => 'error', 'message' => 'Gagal menghapus reservasi'];

    try {
        $deleteIdReservasi = $_POST['delete_id_reservasi'];

        // Query hapus reservasi
        $deleteQuery = "DELETE FROM reservasi WHERE id_reservasi = :id_reservasi AND nik = :nik";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindValue(':id_reservasi', $deleteIdReservasi, PDO::PARAM_INT);
        $deleteStmt->bindValue(':nik', $nik, PDO::PARAM_STR);
        $deleteStmt->execute();

        $response = ['status' => 'success', 'message' => 'Reservasi berhasil dihapus!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal menghapus reservasi: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Proses update reservasi
if (isset($_POST['update_reservasi'])) {
    $response = ['status' => 'error', 'message' => 'Gagal memperbarui reservasi'];

    try {
        $id_reservasi = $_POST['id_reservasi'];
        $id_dokter = $_POST['id_dokter'];
        $id_poli = $_POST['id_poli'];
        $tanggal = $_POST['tanggal'];
        $status = 'Tunggu'; // Status default

        // Update query untuk memperbarui reservasi
        $updateQuery = "UPDATE reservasi SET id_dokter = :id_dokter, id_poli = :id_poli, tanggal = :tanggal, status = :status WHERE id_reservasi = :id_reservasi AND nik = :nik";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindValue(':id_dokter', $id_dokter, PDO::PARAM_INT);
        $updateStmt->bindValue(':id_poli', $id_poli, PDO::PARAM_INT);
        $updateStmt->bindValue(':tanggal', $tanggal, PDO::PARAM_STR);
        $updateStmt->bindValue(':status', $status, PDO::PARAM_STR);
        $updateStmt->bindValue(':id_reservasi', $id_reservasi, PDO::PARAM_INT);
        $updateStmt->bindValue(':nik', $nik, PDO::PARAM_STR);
        $updateStmt->execute();

        $response = ['status' => 'success', 'message' => 'Reservasi berhasil diperbarui!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal memperbarui reservasi: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Proses tambah reservasi
if (isset($_POST['add_reservasi'])) {
    $response = ['status' => 'error', 'message' => 'Gagal menambahkan reservasi'];

    try {
        $id_dokter = $_POST['id_dokter'];
        $id_poli = $_POST['id_poli'];
        $tanggal = $_POST['tanggal'];
        $status = 'Tunggu'; // Status default

        // Insert query untuk tambah reservasi
        $insertQuery = "INSERT INTO reservasi (nik, id_dokter, id_poli, tanggal, status) VALUES (:nik, :id_dokter, :id_poli, :tanggal, :status)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bindValue(':nik', $nik, PDO::PARAM_STR);
        $insertStmt->bindValue(':id_dokter', $id_dokter, PDO::PARAM_INT);
        $insertStmt->bindValue(':id_poli', $id_poli, PDO::PARAM_INT);
        $insertStmt->bindValue(':tanggal', $tanggal, PDO::PARAM_STR);
        $insertStmt->bindValue(':status', $status, PDO::PARAM_STR);
        $insertStmt->execute();

        $response = ['status' => 'success', 'message' => 'Reservasi berhasil ditambahkan!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal menambahkan reservasi: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Pasien Dashboard - E-Reservasi Rumah Sehat</title>
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
        <!-- Tabel Reservasi Hari Ini -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <span>Reservasi Saya</span>
            </div>
            <div class="card-body">
                <!-- Form Pencarian dan Tombol Tambah Reservasi Sejajar -->
                <form method="get" action="pasien_dashboard.php" class="mb-3">
                    <div class="row d-flex justify-content-between">
                        <!-- Kolom Pencarian -->
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="search" id="searchName" class="form-control" placeholder="Cari Nama Dokter" value="<?php echo htmlspecialchars($searchTerm); ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text" id="searchIcon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tambah Reservasi -->
                        <div class="col-md-4 d-flex justify-content-end">
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#addReservasiModal">
                                <i class="fas fa-plus"></i> Tambah Reservasi
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Reservasi -->
                <div class="table-container">
                    <?php if (empty($reservasiList)): ?>
                        <p>Tidak ada reservasi hari ini.</p>
                    <?php else: ?>
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Dokter</th>
                                <th>Poli</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
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
                                <td>
                                    <?php if ($r['status'] == 'Tunggu'): ?>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editReservasiModal" onclick="editReservasi(<?php echo htmlspecialchars(json_encode($r)); ?>)">Edit</button>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteReservasiModal" onclick="setDeleteIdReservasi('<?php echo $r['id_reservasi']; ?>')">Hapus</button>
                                    <?php else: ?>
                                        <button class="btn btn-dark btn-sm" disabled>Edit</button>
                                        <button class="btn btn-dark btn-sm" disabled>Hapus</button>
                                    <?php endif; ?>
                                </td>
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

    <!-- Modal Konfirmasi Hapus Reservasi -->
    <div class="modal fade" id="deleteReservasiModal" tabindex="-1" role="dialog" aria-labelledby="deleteReservasiModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteReservasiModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Yakin ingin menghapus reservasi ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteReservasi">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Reservasi -->
    <div class="modal fade" id="editReservasiModal" tabindex="-1" role="dialog" aria-labelledby="editReservasiModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editReservasiModalLabel">Edit Reservasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editReservasiForm">
                        <input type="hidden" name="id_reservasi" id="editIdReservasi">
                        <div class="form-group">
                            <label for="editDokter">Dokter</label>
                            <select name="id_dokter" id="editDokter" class="form-control" required>
                                <?php foreach ($dokterList as $dokter): ?>
                                    <option value="<?php echo $dokter['id_dokter']; ?>"><?php echo htmlspecialchars($dokter['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editPoli">Poli</label>
                            <select name="id_poli" id="editPoli" class="form-control" required>
                                <?php foreach ($poliList as $poli): ?>
                                    <option value="<?php echo $poli['id_poli']; ?>"><?php echo htmlspecialchars($poli['nama_poli']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editTanggal">Tanggal</label>
                            <input type="date" name="tanggal" id="editTanggal" class="form-control" min="<?php echo $today; ?>" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="updateReservasi()">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Reservasi -->
    <div class="modal fade" id="addReservasiModal" tabindex="-1" role="dialog" aria-labelledby="addReservasiModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReservasiModalLabel">Tambah Reservasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addReservasiForm">
                        <div class="form-group">
                            <label for="addDokter">Dokter</label>
                            <select name="id_dokter" id="addDokter" class="form-control" required>
                                <?php foreach ($dokterList as $dokter): ?>
                                    <option value="<?php echo $dokter['id_dokter']; ?>"><?php echo htmlspecialchars($dokter['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="addPoli">Poli</label>
                            <select name="id_poli" id="addPoli" class="form-control" required>
                                <?php foreach ($poliList as $poli): ?>
                                    <option value="<?php echo $poli['id_poli']; ?>"><?php echo htmlspecialchars($poli['nama_poli']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="addTanggal">Tanggal</label>
                            <input type="date" name="tanggal" id="addTanggal" class="form-control" min="<?php echo $today; ?>" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="addReservasi()">Tambah Reservasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengedit data reservasi
        function editReservasi(reservasi) {
            $('#editIdReservasi').val(reservasi.id_reservasi);
            $('#editDokter').val(reservasi.id_dokter);
            $('#editPoli').val(reservasi.id_poli);
            $('#editTanggal').val(reservasi.tanggal);
        }

        // Fungsi untuk menyimpan perubahan reservasi
        function updateReservasi() {
            var formData = $('#editReservasiForm').serialize(); // Mengambil data form

            $.post('pasien_dashboard.php', formData + '&update_reservasi=true', function(response) {
                var res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') {
                    $('#editReservasiModal').modal('hide');
                    location.reload(); // Refresh halaman setelah update
                }
            });
        }

        // Fungsi untuk menambah reservasi baru
        function addReservasi() {
            var formData = $('#addReservasiForm').serialize(); // Mengambil data form

            $.post('pasien_dashboard.php', formData + '&add_reservasi=true', function(response) {
                var res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') {
                    $('#addReservasiModal').modal('hide');
                    location.reload(); // Refresh halaman setelah tambah
                }
            });
        }

        // Simpan ID reservasi yang ingin dihapus untuk digunakan di modal konfirmasi
        var deleteIdReservasi = null;

        function setDeleteIdReservasi(id) {
            deleteIdReservasi = id;
        }

        // Fungsi untuk menghapus reservasi
        $('#confirmDeleteReservasi').on('click', function() {
            if (deleteIdReservasi) {
                $.post('pasien_dashboard.php', { delete_id_reservasi: deleteIdReservasi }, function(response) {
                    var res = JSON.parse(response);
                    alert(res.message);
                    if (res.status === 'success') {
                        $('#deleteReservasiModal').modal('hide');
                        location.reload(); // Refresh halaman setelah hapus
                    }
                });
            }
        });
    </script>
  </body>
</html>
