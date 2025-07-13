<?php
// Define active page based on current script name
$active_page = basename($_SERVER['PHP_SELF'], '.php');
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
    
    <!-- Dashboard -->
    <li class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <hr class="sidebar-divider">
    
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <div class="sidebar-heading">
            Data Master
        </div>
        
        <!-- Fingerprint Management Dropdown -->
        <li class="nav-item <?php echo in_array($active_page, ['sync_fingerprint', 'manage_fingerprint_users', 'test_fingerprint_connection', 'setup_cron', 'view_logs']) ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFingerprint" 
               aria-expanded="<?php echo in_array($active_page, ['sync_fingerprint', 'manage_fingerprint_users', 'test_fingerprint_connection', 'setup_cron', 'view_logs']) ? 'true' : 'false'; ?>" 
               aria-controls="collapseFingerprint">
                <i class="fas fa-fw fa-fingerprint"></i>
                <span>Fingerprint</span>
            </a>
            <div id="collapseFingerprint" class="collapse <?php echo in_array($active_page, ['sync_fingerprint', 'manage_fingerprint_users', 'test_fingerprint_connection', 'setup_cron', 'view_logs']) ? 'show' : ''; ?>" 
                 aria-labelledby="headingFingerprint" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo $active_page === 'sync_fingerprint' ? 'active' : ''; ?>" href="sync_fingerprint.php">Sinkronisasi Data</a>
                    <a class="collapse-item <?php echo $active_page === 'manage_fingerprint_users' ? 'active' : ''; ?>" href="manage_fingerprint_users.php">Kelola Pengguna</a>
                    <a class="collapse-item <?php echo $active_page === 'test_fingerprint_connection' ? 'active' : ''; ?>" href="test_fingerprint_connection.php">Test Koneksi</a>
                    <a class="collapse-item <?php echo $active_page === 'setup_cron' ? 'active' : ''; ?>" href="setup_cron.php">Setup Cron Job</a>
                    <a class="collapse-item <?php echo $active_page === 'view_logs' ? 'active' : ''; ?>" href="view_logs.php">View Logs</a>
                </div>
            </div>
        </li>
        
        <!-- Data Master Dropdown -->
        <li class="nav-item <?php echo in_array($active_page, ['list_jurusan', 'list_kelas', 'list_guru', 'list_siswa', 'list_users']) ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDataMaster" 
               aria-expanded="<?php echo in_array($active_page, ['list_jurusan', 'list_kelas', 'list_guru', 'list_siswa', 'list_users']) ? 'true' : 'false'; ?>" 
               aria-controls="collapseDataMaster">
                <i class="fas fa-fw fa-database"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseDataMaster" class="collapse <?php echo in_array($active_page, ['list_jurusan', 'list_kelas', 'list_guru', 'list_siswa', 'list_users']) ? 'show' : ''; ?>" 
                 aria-labelledby="headingDataMaster" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo $active_page === 'list_jurusan' ? 'active' : ''; ?>" href="list_jurusan.php">Data Jurusan</a>
                    <a class="collapse-item <?php echo $active_page === 'list_kelas' ? 'active' : ''; ?>" href="list_kelas.php">Data Kelas</a>
                    <a class="collapse-item <?php echo $active_page === 'list_guru' ? 'active' : ''; ?>" href="list_guru.php">List Guru</a>
                    <a class="collapse-item <?php echo $active_page === 'list_siswa' ? 'active' : ''; ?>" href="list_siswa.php">List Siswa</a>
                    <a class="collapse-item <?php echo $active_page === 'list_users' ? 'active' : ''; ?>" href="list_users.php">Data Pengguna</a>
                </div>
            </div>
        </li>
        
        <!-- Absensi Dropdown -->
        <li class="nav-item <?php echo in_array($active_page, ['attendance_records', 'realtime_attendance', 'laporan_guru', 'laporan_siswa']) ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAttendance" 
               aria-expanded="<?php echo in_array($active_page, ['attendance_records', 'realtime_attendance', 'laporan_guru', 'laporan_siswa']) ? 'true' : 'false'; ?>" 
               aria-controls="collapseAttendance">
                <i class="fas fa-fw fa-clock"></i>
                <span>Absensi</span>
            </a>
            <div id="collapseAttendance" class="collapse <?php echo in_array($active_page, ['attendance_records', 'realtime_attendance', 'laporan_guru', 'laporan_siswa']) ? 'show' : ''; ?>" 
                 aria-labelledby="headingAttendance" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo $active_page === 'realtime_attendance' ? 'active' : ''; ?>" href="realtime_attendance.php">Real-time</a>
                    <a class="collapse-item <?php echo $active_page === 'attendance_records' ? 'active' : ''; ?>" href="attendance_records.php">Log Absensi</a>
                    <a class="collapse-item <?php echo $active_page === 'laporan_guru' ? 'active' : ''; ?>" href="laporan_guru.php">Laporan Absensi Guru</a>
                    <a class="collapse-item <?php echo $active_page === 'laporan_siswa' ? 'active' : ''; ?>" href="laporan_siswa.php">Laporan Absensi Siswa</a>
                </div>
            </div>
        </li>
        
        <!-- Layanan Pengaduan -->
        <li class="nav-item <?php echo $active_page === 'list_pengaduan' ? 'active' : ''; ?>">
            <a class="nav-link" href="list_pengaduan.php">
                <i class="fas fa-fw fa-exclamation-circle"></i>
                <span>Layanan Pengaduan</span>
            </a>
        </li>
        <hr class="sidebar-divider">
    <?php endif; ?>
    
    <?php if ($_SESSION['user']['role'] === 'guru'): ?>
        <div class="sidebar-heading">
            Guru Menu
        </div>
        
        <!-- Guru Menu Dropdown -->
        <li class="nav-item <?php echo in_array($active_page, ['list_users_guru', 'log_absensi', 'absensi_siswa', 'absensi_guru', 'laporan_siswa', 'laporan_guru', 'monitor_fingerprint']) ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseGuruMenu" 
               aria-expanded="<?php echo in_array($active_page, ['list_users_guru', 'log_absensi', 'absensi_siswa', 'absensi_guru', 'laporan_siswa', 'laporan_guru', 'monitor_fingerprint']) ? 'true' : 'false'; ?>" 
               aria-controls="collapseGuruMenu">
                <i class="fas fa-fw fa-chalkboard-teacher"></i>
                <span>Guru Menu</span>
            </a>
            <div id="collapseGuruMenu" class="collapse <?php echo in_array($active_page, ['list_users_guru', 'log_absensi', 'absensi_siswa', 'absensi_guru', 'realtime_attendance', 'laporan_siswa', 'laporan_guru', 'monitor_fingerprint']) ? 'show' : ''; ?>" 
                 aria-labelledby="headingGuruMenu" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo $active_page === 'list_users_guru' ? 'active' : ''; ?>" href="list_users_guru.php">Data Pengguna</a>
                    <a class="collapse-item <?php echo $active_page === 'log_absensi' ? 'active' : ''; ?>" href="log_absensi.php">Log Absensi</a>
                    <a class="collapse-item <?php echo $active_page === 'absensi_siswa' ? 'active' : ''; ?>" href="absensi_siswa.php">Absensi Siswa</a>
                    <a class="collapse-item <?php echo $active_page === 'absensi_guru' ? 'active' : ''; ?>" href="absensi_guru.php">Absensi Guru</a>
                    <a class="collapse-item <?php echo $active_page === 'monitor_fingerprint' ? 'active' : ''; ?>" href="monitor_fingerprint.php">Monitor Fingerprint</a>
                    <a class="collapse-item <?php echo $active_page === 'realtime_attendance' ? 'active' : ''; ?>" href="realtime_attendance.php">Real-time Attendance</a>
                    <a class="collapse-item <?php echo $active_page === 'laporan_siswa' ? 'active' : ''; ?>" href="laporan_siswa.php">Laporan Absensi Siswa</a>
                    <a class="collapse-item <?php echo $active_page === 'laporan_guru' ? 'active' : ''; ?>" href="laporan_guru.php">Laporan Absensi Guru</a>
                </div>
            </div>
        </li>
        <hr class="sidebar-divider">
    <?php endif; ?>
    
    <!-- Logout -->
    <li class="nav-item">
        <a class="nav-link" href="../auth/logout.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
    
    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle" type="button"></button>
    </div>
</ul>