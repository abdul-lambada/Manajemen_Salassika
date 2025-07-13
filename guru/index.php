<?php
session_start();
include '../includes/db.php';
$title = "Dashboard Guru";
$active_page = "dashboard"; // Untuk menandai menu aktif di sidebar

// Periksa apakah sesi 'user' tersedia
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Pastikan data 'nama' tersedia di sesi
if (!isset($_SESSION['user']['nama'])) {
    die("Error: Session 'nama' tidak ditemukan.");
}

$nama_guru = $_SESSION['user']['nama'];

// Ambil statistik untuk dashboard
try {
    // Total siswa hari ini
    $stmt_siswa_hari_ini = $conn->prepare("
        SELECT COUNT(*) as total_hadir 
        FROM Absensi_Siswa 
        WHERE tanggal = CURDATE() AND status_kehadiran = 'Hadir'
    ");
    $stmt_siswa_hari_ini->execute();
    $siswa_hadir_hari_ini = $stmt_siswa_hari_ini->fetch(PDO::FETCH_ASSOC)['total_hadir'];

    // Total guru hari ini
    $stmt_guru_hari_ini = $conn->prepare("
        SELECT COUNT(*) as total_hadir 
        FROM absensi_guru 
        WHERE tanggal = CURDATE() AND status_kehadiran = 'Hadir'
    ");
    $stmt_guru_hari_ini->execute();
    $guru_hadir_hari_ini = $stmt_guru_hari_ini->fetch(PDO::FETCH_ASSOC)['total_hadir'];

    // Total siswa keseluruhan
    $stmt_total_siswa = $conn->prepare("SELECT COUNT(*) as total FROM siswa");
    $stmt_total_siswa->execute();
    $total_siswa = $stmt_total_siswa->fetch(PDO::FETCH_ASSOC)['total'];

    // Total guru keseluruhan
    $stmt_total_guru = $conn->prepare("SELECT COUNT(*) as total FROM guru");
    $stmt_total_guru->execute();
    $total_guru = $stmt_total_guru->fetch(PDO::FETCH_ASSOC)['total'];

    // Absensi fingerprint hari ini
    $stmt_fingerprint = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM tbl_kehadiran 
        WHERE DATE(timestamp) = CURDATE()
    ");
    $stmt_fingerprint->execute();
    $fingerprint_hari_ini = $stmt_fingerprint->fetch(PDO::FETCH_ASSOC)['total'];

    // Absensi terbaru (5 terakhir)
    $stmt_absensi_terbaru = $conn->prepare("
        SELECT 
            'Siswa' as tipe,
            COALESCE(u.name, s.nama_siswa) as nama,
            asis.status_kehadiran,
            asis.tanggal,
            asis.catatan
        FROM absensi_siswa asis
        JOIN siswa s ON asis.id_siswa = s.id_siswa
        LEFT JOIN users u ON s.user_id = u.id
        WHERE asis.tanggal = CURDATE()
        UNION ALL
        SELECT 
            'Guru' as tipe,
            g.nama_guru as nama,
            ag.status_kehadiran,
            ag.tanggal,
            ag.catatan
        FROM absensi_guru ag
        JOIN guru g ON ag.id_guru = g.id_guru
        WHERE ag.tanggal = CURDATE()
        ORDER BY tanggal DESC
        LIMIT 5
    ");
    $stmt_absensi_terbaru->execute();
    $absensi_terbaru = $stmt_absensi_terbaru->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Guru</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>

                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Divider -->
                    <div class="topbar-divider d-none d-sm-block"></div>

                    <!-- Nav Item - User Information -->
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo htmlspecialchars($nama_guru); ?>
                            </span>
                            <img class="img-profile rounded-circle"
                                src="../assets/img/undraw_profile.svg" alt="Profil">
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <!-- <a class="dropdown-item" href="#">
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Profil
                            </a> -->
                            <!-- <div class="dropdown-divider"></div> -->
                            <a class="dropdown-item" href="../auth/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard Guru</h1>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-download fa-sm text-white-50"></i> Generate Laporan
                    </a>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Total Siswa Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Siswa</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_siswa; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Siswa Hadir Hari Ini Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Siswa Hadir Hari Ini</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $siswa_hadir_hari_ini; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Guru Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Guru</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_guru; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guru Hadir Hari Ini Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Guru Hadir Hari Ini</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $guru_hadir_hari_ini; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Fingerprint Status -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Status Fingerprint</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <i class="fas fa-fingerprint fa-3x text-primary mb-3"></i>
                                            <h4 class="text-primary"><?php echo $fingerprint_hari_ini; ?></h4>
                                            <p class="text-gray-600">Absensi Fingerprint Hari Ini</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <i class="fas fa-clock fa-3x text-success mb-3"></i>
                                            <h4 class="text-success"><?php echo date('H:i'); ?></h4>
                                            <p class="text-gray-600">Waktu Sekarang</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="realtime_attendance.php" class="btn btn-primary btn-block">
                                        <i class="fas fa-eye"></i> Lihat Absensi Real-time
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Absensi Terbaru -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Absensi Terbaru Hari Ini</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($absensi_terbaru)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Tipe</th>
                                                    <th>Nama</th>
                                                    <th>Status</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($absensi_terbaru as $absensi): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge badge-<?php echo $absensi['tipe'] == 'Siswa' ? 'primary' : 'info'; ?>">
                                                                <?php echo htmlspecialchars($absensi['tipe']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($absensi['nama']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php 
                                                                echo $absensi['status_kehadiran'] == 'Hadir' ? 'success' : 
                                                                    ($absensi['status_kehadiran'] == 'Telat' ? 'warning' : 'danger'); 
                                                            ?>">
                                                                <?php echo htmlspecialchars($absensi['status_kehadiran']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($absensi['catatan'] ?: '-'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-gray-500">Belum ada data absensi hari ini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="absensi_siswa.php" class="btn btn-primary btn-block">
                                            <i class="fas fa-user-check"></i> Absensi Siswa
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="absensi_guru.php" class="btn btn-info btn-block">
                                            <i class="fas fa-chalkboard-teacher"></i> Absensi Guru
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="laporan_siswa.php" class="btn btn-success btn-block">
                                            <i class="fas fa-file-alt"></i> Laporan Siswa
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="laporan_guru.php" class="btn btn-warning btn-block">
                                            <i class="fas fa-chart-bar"></i> Laporan Guru
                                        </a>
                                    </div>
                                </div>
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