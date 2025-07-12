<?php
session_start();
include '../includes/db.php';

// Periksa apakah sesi 'user' tersedia
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../auth/login.php");
    exit;
}

$active_page = "absensi_guru"; // Untuk menandai menu aktif di sidebar
$message = ''; // Variabel untuk menyimpan pesan sukses

try {
    // Ambil daftar guru
    $stmt_guru = $conn->prepare("SELECT * FROM Guru");
    $stmt_guru->execute();
    $guru_list = $stmt_guru->fetchAll(PDO::FETCH_ASSOC);

    // Jika form absensi disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_absensi'])) {
        $tanggal = date('Y-m-d');

        foreach ($_POST['status'] as $id_guru => $status_kehadiran) {
            // Cek apakah absensi sudah ada untuk hari ini
            $stmt_check = $conn->prepare("SELECT * FROM Absensi_Guru WHERE id_guru = :id_guru AND tanggal = :tanggal");
            $stmt_check->bindParam(':id_guru', $id_guru);
            $stmt_check->bindParam(':tanggal', $tanggal);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {
                continue; // Lewati jika absensi sudah ada
            }

            // Ambil data dari form
            $catatan = filter_var($_POST['catatan'][$id_guru], FILTER_SANITIZE_STRING);

            // Simpan absensi baru untuk tiap guru
            $stmt_insert = $conn->prepare("
                INSERT INTO Absensi_Guru (id_guru, tanggal, status_kehadiran, catatan)
                VALUES (:id_guru, :tanggal, :status_kehadiran, :catatan)
            ");
            $stmt_insert->bindParam(':id_guru', $id_guru);
            $stmt_insert->bindParam(':tanggal', $tanggal);
            $stmt_insert->bindParam(':status_kehadiran', $status_kehadiran);
            $stmt_insert->bindParam(':catatan', $catatan);
            $stmt_insert->execute();
        }

        // Set pesan sukses
        $message = 'Absensi berhasil disimpan.';
    }

    // Pagination setup
    $limit = 10; // Jumlah data per halaman
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Hitung total data
    $stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM Absensi_Guru");
    $stmt_count->execute();
    $total_data = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_data / $limit);

    // Ambil riwayat absensi dengan limit dan offset
    $stmt_history = $conn->prepare("
        SELECT 
            ag.id_absensi_guru AS id_absensi,
            ag.tanggal,
            g.nama_guru,
            ag.status_kehadiran AS status_kehadiran,
            ag.catatan,
            kh.timestamp AS waktu_kehadiran,
            kh.verification_mode AS mode_verifikasi,
            kh.status AS status_verifikasi
        FROM Absensi_Guru ag
        JOIN Guru g ON ag.id_guru = g.id_guru
        LEFT JOIN tbl_kehadiran kh ON g.user_id = kh.user_id
        ORDER BY ag.tanggal DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt_history->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt_history->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_history->execute();
    $absensi_list = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Absensi Guru - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Absensi Guru</h1>
            </nav>
            <div class="container-fluid">
                <!-- Tampilkan pesan sukses jika ada -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Form Absensi Guru -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Form Absensi Guru</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama Guru</th>
                                            <th>NIP</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Status Kehadiran</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($guru_list as $guru): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($guru['nama_guru']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['jenis_kelamin']); ?></td>
                                                <td>
                                                    <select name="status[<?php echo $guru['id_guru']; ?>]" class="form-control">
                                                        <option value="Hadir">Hadir</option>
                                                        <option value="Telat">Telat</option>
                                                        <option value="Izin">Izin</option>
                                                        <option value="Sakit">Sakit</option>
                                                        <option value="Alfa">Alfa</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="catatan[<?php echo $guru['id_guru']; ?>]" class="form-control" placeholder="Catatan">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="submit_absensi" class="btn btn-primary">Simpan Absensi</button>
                        </form>
                    </div>
                </div>

                <!-- Riwayat Absensi -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Riwayat Absensi</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nama Guru</th>
                                                <th>Status Kehadiran</th>
                                                <th>Catatan</th>
                                                <th>Waktu Kehadiran</th>
                                                <th>Mode Verifikasi</th>
                                                <th>Status Verifikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($absensi_list)): ?>
                                                <?php foreach ($absensi_list as $absensi): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($absensi['tanggal']); ?></td>
                                                        <td><?php echo htmlspecialchars($absensi['nama_guru']); ?></td>
                                                        <td><?php echo htmlspecialchars($absensi['status_kehadiran']); ?></td>
                                                        <td><?php echo htmlspecialchars($absensi['catatan']); ?></td>
                                                        <td><?php echo htmlspecialchars(isset($absensi['waktu_kehadiran']) ? $absensi['waktu_kehadiran'] : 'Belum Ada'); ?></td>
                                                        <td><?php echo htmlspecialchars(isset($absensi['mode_verifikasi']) ? $absensi['mode_verifikasi'] : 'Tidak Ada'); ?></td>
                                                        <td><?php echo htmlspecialchars(isset($absensi['status_verifikasi']) ? $absensi['status_verifikasi'] : 'Belum Diverifikasi'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">Tidak ada riwayat absensi.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination -->
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-end">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">Previous</span>
                                            </li>
                                        <?php endif; ?>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">Next</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>

</html>