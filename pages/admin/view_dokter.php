<?php
session_start();
include '../../koneksi.php'; // Pastikan file ini menggunakan PDO

// Periksa apakah pengguna sudah login dan memiliki role sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Variabel untuk menampilkan notifikasi
$notification = '';

// Cek apakah ada permintaan tambah dokter melalui AJAX
if (isset($_POST['add_dokter'])) {
    $response = ['status' => 'error', 'message' => 'Gagal menambahkan dokter'];

    try {
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $spesialis = $_POST['spesialis'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $no_hp = $_POST['no_hp'];

        // Insert query tanpa hashing password
        $insertQuery = "INSERT INTO dokter (nama, email, password, spesialis, jenis_kelamin, no_hp) VALUES (:nama, :email, :password, :spesialis, :jenis_kelamin, :no_hp)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bindValue(':nama', $nama, PDO::PARAM_STR);
        $insertStmt->bindValue(':email', $email, PDO::PARAM_STR);
        $insertStmt->bindValue(':password', $password, PDO::PARAM_STR);
        $insertStmt->bindValue(':spesialis', $spesialis, PDO::PARAM_STR);
        $insertStmt->bindValue(':jenis_kelamin', $jenis_kelamin, PDO::PARAM_STR);
        $insertStmt->bindValue(':no_hp', $no_hp, PDO::PARAM_STR);
        $insertStmt->execute();

        $response = ['status' => 'success', 'message' => 'Dokter berhasil ditambahkan!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal menambahkan dokter: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Ambil data dokter dari database berdasarkan nama pencarian
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Ambil parameter pencarian dari URL
$query = "SELECT * FROM dokter WHERE nama LIKE :searchTerm"; // Query pencarian
try {
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
    $stmt->execute();
    $dokter = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query gagal: " . $e->getMessage());
}

// Proses update dokter
if (isset($_POST['update_dokter'])) {
    $response = ['status' => 'error', 'message' => 'Gagal memperbarui data dokter'];

    try {
        $id_dokter = $_POST['id_dokter'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $spesialis = $_POST['spesialis'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $no_hp = $_POST['no_hp'];

        // Update query tanpa hashing password
        $updateQuery = "UPDATE dokter SET nama = :nama, email = :email, password = :password, spesialis = :spesialis, jenis_kelamin = :jenis_kelamin, no_hp = :no_hp WHERE id_dokter = :id_dokter";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindValue(':nama', $nama, PDO::PARAM_STR);
        $updateStmt->bindValue(':email', $email, PDO::PARAM_STR);
        $updateStmt->bindValue(':password', $password, PDO::PARAM_STR);
        $updateStmt->bindValue(':spesialis', $spesialis, PDO::PARAM_STR);
        $updateStmt->bindValue(':jenis_kelamin', $jenis_kelamin, PDO::PARAM_STR);
        $updateStmt->bindValue(':no_hp', $no_hp, PDO::PARAM_STR);
        $updateStmt->bindValue(':id_dokter', $id_dokter, PDO::PARAM_INT);
        $updateStmt->execute();

        $response = ['status' => 'success', 'message' => 'Dokter berhasil diperbarui!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal memperbarui dokter: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Proses hapus dokter
if (isset($_POST['delete_id_dokter'])) {
    $response = ['status' => 'error', 'message' => 'Gagal menghapus dokter'];

    try {
        $deleteIdDokter = $_POST['delete_id_dokter'];
        $deleteQuery = "DELETE FROM dokter WHERE id_dokter = :id_dokter";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindValue(':id_dokter', $deleteIdDokter, PDO::PARAM_INT);
        $deleteStmt->execute();

        $response = ['status' => 'success', 'message' => 'Dokter berhasil dihapus!'];
    } catch (PDOException $e) {
        $response['message'] = 'Gagal menghapus dokter: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - E-Reservasi Rumah Sehat</title>
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
                <li class="nav-item"><a class="nav-link btn btn-light text-primary px-3" href="admin_dashboard.php?logout=true">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="content wow fadeInUp">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">Daftar Dokter</div>
            <div class="card-body">
                <!-- Form Pencarian dan Tombol Tambah Dokter Sejajar -->
                <form method="get" action="view_dokter.php" class="mb-3">
                    <div class="row d-flex justify-content-between">
                        <!-- Kolom Pencarian -->
                        <div class="col-md-">
                            <div class="input-group">
                                <input type="text" name="search" id="searchName" class="form-control" placeholder="Cari Nama" value="<?php echo htmlspecialchars($searchTerm); ?>" />
                                <div class="input-group-append">
                                    <span class="input-group-text" id="searchIcon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tambah Dokter -->
                        <div class="col-md-2 d-flex justify-content-end">
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#addDoctorModal">
                                <i class="fas fa-plus"></i> Tambah Dokter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabel Dokter -->
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Spesialis</th>
                            <th>Jenis Kelamin</th>
                            <th>No HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dokterTableBody">
                        <?php if (empty($dokter)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data dokter</td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $no = 1;
                            foreach ($dokter as $d) : 
                            ?>
                            <tr data-id="<?php echo $d['id_dokter']; ?>">
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($d['nama']); ?></td>
                                <td><?php echo htmlspecialchars($d['email']); ?></td>
                                <td><?php echo htmlspecialchars($d['password']); ?></td>
                                <td><?php echo htmlspecialchars($d['spesialis']); ?></td>
                                <td><?php echo htmlspecialchars($d['jenis_kelamin']); ?></td>
                                <td><?php echo htmlspecialchars($d['no_hp']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editDoctorModal" onclick="editDoctor(<?php echo htmlspecialchars(json_encode($d)); ?>)">Edit</button>
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteDoctorModal" onclick="setDeleteIdDokter('<?php echo $d['id_dokter']; ?>')">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <footer>
    <p id="copyright">
          Copyright &copy; 2025 <a href="#" target="_blank">RumahSehat</a>
        </p>
    </footer>

    <!-- Modal Konfirmasi Hapus Dokter -->
    <div class="modal fade" id="deleteDoctorModal" tabindex="-1" role="dialog" aria-labelledby="deleteDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDoctorModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Yakin ingin menghapus dokter ini? Data terkait juga akan dihapus.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Dokter -->
    <div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDoctorModalLabel">Edit Dokter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editDoctorForm">
                        <input type="hidden" name="id_dokter" id="editIdDokter">
                        <div class="form-group">
                            <label for="editNama">Nama</label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editPassword">Password</label>
                            <input type="password" name="password" id="editPassword" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editSpesialis">Spesialis</label>
                            <input type="text" name="spesialis" id="editSpesialis" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editJenisKelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="editJenisKelamin" class="form-control" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editNoHp">No HP</label>
                            <input type="text" name="no_hp" id="editNoHp" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="updateDoctor()">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Dokter -->
    <div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDoctorModalLabel">Tambah Dokter</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addDoctorForm">
                        <div class="form-group">
                            <label for="addNama">Nama</label>
                            <input type="text" name="nama" id="addNama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="addEmail">Email</label>
                            <input type="email" name="email" id="addEmail" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="addPassword">Password</label>
                            <input type="password" name="password" id="addPassword" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="addSpesialis">Spesialis</label>
                            <input type="text" name="spesialis" id="addSpesialis" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="addJenisKelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="addJenisKelamin" class="form-control" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="addNoHp">No HP</label>
                            <input type="text" name="no_hp" id="addNoHp" class="form-control" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="addDoctor()">Tambah Dokter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk menambahkan dokter baru
        function addDoctor() {
            var formData = $('#addDoctorForm').serialize(); // Mengambil data form

            $.post('view_dokter.php', formData + '&add_dokter=true', function(response) {
                var res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') {
                    $('#addDoctorModal').modal('hide');
                    location.reload(); // Refresh halaman
                }
            });
        }

        // Fungsi untuk mengedit data dokter
        function editDoctor(doctor) {
            $('#editIdDokter').val(doctor.id_dokter);
            $('#editNama').val(doctor.nama);
            $('#editEmail').val(doctor.email);
            $('#editPassword').val(doctor.password);
            $('#editSpesialis').val(doctor.spesialis);
            $('#editJenisKelamin').val(doctor.jenis_kelamin);
            $('#editNoHp').val(doctor.no_hp);
        }

        // Fungsi untuk menyimpan perubahan dokter
        function updateDoctor() {
            var formData = $('#editDoctorForm').serialize(); // Mengambil data form

            $.post('view_dokter.php', formData + '&update_dokter=true', function(response) {
                var res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') {
                    $('#editDoctorModal').modal('hide');
                    location.reload(); // Refresh halaman
                }
            });
        }

        // Simpan ID dokter yang ingin dihapus untuk digunakan di modal konfirmasi
        var deleteIdDokter = null;

        function setDeleteIdDokter(id) {
            deleteIdDokter = id;
        }

        // Fungsi untuk menghapus dokter
        $('#confirmDelete').on('click', function() {
            if (deleteIdDokter) {
                $.post('view_dokter.php', { delete_id_dokter: deleteIdDokter }, function(response) {
                    var res = JSON.parse(response);
                    alert(res.message);
                    if (res.status === 'success') {
                        $('#deleteDoctorModal').modal('hide');
                        location.reload(); // Refresh halaman setelah hapus
                    }
                });
            }
        });
    </script>
</body>
</html>
