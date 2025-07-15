<?php
$role = $_SESSION['user']['role'];
?>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Absensi Sekolah</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    <?php if ($role === 'admin'): ?>
        <div class="sidebar-heading">Data Master</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFingerprint" aria-expanded="false" aria-controls="collapseFingerprint">
                <i class="fas fa-fw fa-fingerprint"></i>
                <span>Fingerprint</span>
            </a>
            <div id="collapseFingerprint" class="collapse" aria-labelledby="headingFingerprint" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="sync_fingerprint.php">Sinkronisasi Data</a>
                    <a class="collapse-item" href="manage_fingerprint_users.php">Kelola Pengguna</a>
                    <a class="collapse-item" href="test_fingerprint_connection.php">Test Koneksi</a>
                    <a class="collapse-item" href="setup_cron.php">Setup Cron Job</a>
                    <a class="collapse-item" href="view_logs.php">View Logs</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDataMaster" aria-expanded="false" aria-controls="collapseDataMaster">
                <i class="fas fa-fw fa-database"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseDataMaster" class="collapse" aria-labelledby="headingDataMaster" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="list_jurusan.php">Data Jurusan</a>
                    <a class="collapse-item" href="list_kelas.php">Data Kelas</a>
                    <a class="collapse-item" href="list_guru.php">List Guru</a>
                    <a class="collapse-item" href="list_siswa.php">List Siswa</a>
                    <a class="collapse-item" href="list_users.php">Data Pengguna</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAttendance" aria-expanded="false" aria-controls="collapseAttendance">
                <i class="fas fa-fw fa-clock"></i>
                <span>Absensi</span>
            </a>
            <div id="collapseAttendance" class="collapse" aria-labelledby="headingAttendance" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="realtime_attendance.php">Real-time</a>
                    <a class="collapse-item" href="attendance_records.php">Log Absensi</a>
                    <a class="collapse-item" href="laporan_guru.php">Laporan Absensi Guru</a>
                    <a class="collapse-item" href="laporan_siswa.php">Laporan Absensi Siswa</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="list_pengaduan.php">
                <i class="fas fa-fw fa-exclamation-circle"></i>
                <span>Layanan Pengaduan</span>
            </a>
        </li>
        <hr class="sidebar-divider">
    <?php elseif ($role === 'guru'): ?>
        <div class="sidebar-heading">Guru Menu</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseGuruMenu" aria-expanded="false" aria-controls="collapseGuruMenu">
                <i class="fas fa-fw fa-chalkboard-teacher"></i>
                <span>Guru Menu</span>
            </a>
            <div id="collapseGuruMenu" class="collapse" aria-labelledby="headingGuruMenu" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="list_users_guru.php">Data Pengguna</a>
                    <a class="collapse-item" href="log_absensi.php">Log Absensi</a>
                    <a class="collapse-item" href="absensi_siswa.php">Absensi Siswa</a>
                    <a class="collapse-item" href="absensi_guru.php">Absensi Guru</a>
                    <a class="collapse-item" href="monitor_fingerprint.php">Monitor Fingerprint</a>
                    <a class="collapse-item" href="realtime_attendance.php">Real-time Attendance</a>
                    <a class="collapse-item" href="laporan_siswa.php">Laporan Absensi Siswa</a>
                    <a class="collapse-item" href="laporan_guru.php">Laporan Absensi Guru</a>
                </div>
            </div>
        </li>
        <hr class="sidebar-divider">
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link" href="../auth/logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle" type="button"></button>
    </div>
</ul>