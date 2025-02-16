<?php
session_start();
include '../../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../login.php");
    exit();
}

$notification = '';

if (isset($_POST['id_reservasi']) && isset($_POST['status'])) {
    $id_reservasi = $_POST['id_reservasi'];
    $status = $_POST['status'];

    $sql = "UPDATE reservasi SET status = :status WHERE id_reservasi = :id_reservasi";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id_reservasi', $id_reservasi);

    if ($stmt->execute()) {
        echo "<script>alert('Status berhasil diperbarui!');</script>";
        header("Location: view_reservasi.php"); // Refresh halaman untuk menampilkan status yang diperbarui
        exit();
    } else {
        echo "Gagal memperbarui status.";
    }
}

if (isset($_POST['delete_id_reservasi'])) {
    $deleteIdReservasi = $_POST['delete_id_reservasi'];
    $deleteSql = "DELETE FROM reservasi WHERE id_reservasi = :id_reservasi";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bindParam(':id_reservasi', $deleteIdReservasi);
    if ($deleteStmt->execute()) {
        header("Location: view_reservasi.php"); // Redirect setelah berhasil menghapus
        exit();
    } else {
        echo "Gagal menghapus data reservasi.";
    }
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; // Ambil parameter pencarian dari URL
$query = "SELECT r.id_reservasi, p.nama AS pasien, d.nama AS dokter, r.tanggal, r.status 
        FROM reservasi r
        JOIN pasien p ON r.nik = p.nik
        JOIN dokter d ON r.id_dokter = d.id_dokter
        WHERE p.nama LIKE :searchTerm"; // Query pencarian
try {
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
    $stmt->execute();
    $reservasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Reservasi - E-Reservasi Rumah Sehat</title>
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
                <li class="nav-item"><a class="nav-link active" href="view_reservasi.php">Kelola Reservasi</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-primary px-3" href="admin_dashboard.php?logout=true">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="content wow fadeInUp">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">Daftar Reservasi</div>
            <div class="card-body">
                <form method="get" action="view_reservasi.php" class="mb-3">
                    <div class="row d-flex justify-content-between">
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
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservasi)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data reservasi</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservasi as $index => $r): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $r['pasien']; ?></td>
                                    <td><?php echo $r['dokter']; ?></td>
                                    <td><?php echo $r['tanggal']; ?></td>
                                    <td>
                                        <form action="view_reservasi.php" method="POST">
                                            <select name="status" class="form-control" onchange="this.form.submit()">
                                                <option value="Tunggu" <?php echo ($r['status'] == 'Tunggu') ? 'selected' : ''; ?>>Tunggu</option>
                                                <option value="Dikonfirmasi" <?php echo ($r['status'] == 'Dikonfirmasi') ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                                <option value="Selesai" <?php echo ($r['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                            </select>
                                            <input type="hidden" name="id_reservasi" value="<?php echo $r['id_reservasi']; ?>" />
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger" data-toggle="modal" data-target="#deleteConfirmModal" data-id="<?php echo $r['id_reservasi']; ?>">Hapus</button>
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

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data reservasi ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                    <form id="deleteForm" method="POST">
                        <input type="hidden" name="delete_id_reservasi" id="deleteIdReservasi" />
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/jquery-3.5.1.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>

    <script>
        
        $('#deleteConfirmModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id'); 
            $('#deleteIdReservasi').val(id); 
        });
    </script>
</body>
</html>
