<?php
session_start();
include __DIR__ . '/../includes/db.php';
$title = "Dashboard Admin";
$active_page = "dashboard";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Check if user has valid role
$role = $_SESSION['user']['role'];
if (!in_array($role, ['admin', 'guru'])) {
    header("Location: ../auth/login.php");
    exit;
}

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include __DIR__ . '/../templates/navbar.php'; ?>
        
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <?php if ($role === 'admin'): ?>
                        Dashboard Admin
                    <?php elseif ($role === 'guru'): ?>
                        Dashboard Guru
                    <?php endif; ?>
                </h1>
                <div class="d-none d-sm-inline-block">
                    <span class="badge bg-primary"><?= ucfirst($role) ?></span>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <?php if ($role === 'admin'): ?>
                    <!-- Admin Dashboard Content -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Guru</div>
                                        <?php
                                        $stmt = $conn->query("SELECT COUNT(*) as total FROM guru");
                                        $guru_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $guru_count ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Siswa</div>
                                        <?php
                                        $stmt = $conn->query("SELECT COUNT(*) as total FROM siswa");
                                        $siswa_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $siswa_count ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Absensi Hari Ini</div>
                                        <?php
                                        $today = date('Y-m-d');
                                        // Hitung absensi guru hari ini
                                        $stmt_guru = $conn->prepare("SELECT COUNT(*) as total FROM absensi_guru WHERE DATE(tanggal) = :today");
                                        $stmt_guru->bindParam(':today', $today);
                                        $stmt_guru->execute();
                                        $absensi_guru_today = $stmt_guru->fetch(PDO::FETCH_ASSOC)['total'];
                                        // Hitung absensi siswa hari ini
                                        $stmt_siswa = $conn->prepare("SELECT COUNT(*) as total FROM absensi_siswa WHERE DATE(tanggal) = :today");
                                        $stmt_siswa->bindParam(':today', $today);
                                        $stmt_siswa->execute();
                                        $absensi_siswa_today = $stmt_siswa->fetch(PDO::FETCH_ASSOC)['total'];
                                        $absensi_today = $absensi_guru_today + $absensi_siswa_today;
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $absensi_today ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Device Fingerprint</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Online</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-fingerprint fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($role === 'guru'): ?>
                    <!-- Guru Dashboard Content -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Absensi Hari Ini</div>
                                        <?php
                                        $today = date('Y-m-d');
                                        $user_id = $_SESSION['user']['id'];
                                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM absensi_guru WHERE DATE(tanggal) = :today AND user_id = :user_id");
                                        $stmt->bindParam(':today', $today);
                                        $stmt->bindParam(':user_id', $user_id);
                                        $stmt->execute();
                                        $absensi_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $absensi_today ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Siswa</div>
                                        <?php
                                        $stmt = $conn->query("SELECT COUNT(*) as total FROM siswa");
                                        $siswa_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $siswa_count ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Status Kehadiran</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $absensi_today > 0 ? 'Hadir' : 'Belum Absen' ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Menu Tersedia</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">10 Menu</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bars fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Welcome Message -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name']) ?>!
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">
                                <?php if ($role === 'admin'): ?>
                                    Anda login sebagai Administrator. Anda dapat mengelola data guru, siswa, 
                                    absensi, dan mengatur sistem fingerprint.
                                <?php elseif ($role === 'guru'): ?>
                                    Anda login sebagai Guru. Anda dapat melihat absensi siswa, 
                                    mengelola absensi pribadi, dan mengakses fitur-fitur guru lainnya.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</div>

<?php include __DIR__ . '/../templates/scripts.php'; ?>

</body>
</html>