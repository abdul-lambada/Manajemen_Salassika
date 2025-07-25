<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Otomatis deteksi role jika belum di-set
if (!isset($_SESSION['user']['role'])) {
    // fallback: deteksi dari path
    $script = $_SERVER['SCRIPT_NAME'];
    if (strpos($script, '/admin/') !== false) {
        $role = 'admin';
    } elseif (strpos($script, '/guru/') !== false) {
        $role = 'guru';
    } else {
        $role = null;
    }
} else {
    $role = $_SESSION['user']['role'];
}
// Otomatis aktifkan menu berdasarkan path file
$script = $_SERVER['SCRIPT_NAME'];
$active_page = '';
if (preg_match('#/admin/index\.php$#', $script) || preg_match('#/guru/index\.php$#', $script)) {
    $active_page = 'dashboard';
} elseif (preg_match('#/admin/guru/#', $script)) {
    $active_page = 'guru';
} elseif (preg_match('#/admin/siswa/#', $script)) {
    $active_page = 'siswa';
} elseif (preg_match('#/admin/kelas/#', $script)) {
    $active_page = 'kelas';
} elseif (preg_match('#/admin/jurusan/#', $script)) {
    $active_page = 'jurusan';
} elseif (preg_match('#/admin/users/#', $script)) {
    $active_page = 'users';
} elseif (preg_match('#/admin/laporan/laporan_absensi\.php$#', $script)) {
    $active_page = 'laporan_absensi';
} elseif (preg_match('#/admin/laporan/laporan_siswa\.php$#', $script)) {
    $active_page = 'laporan_siswa';
} elseif (preg_match('#/admin/laporan/laporan_guru\.php$#', $script)) {
    $active_page = 'laporan_guru';
} elseif (preg_match('#/admin/laporan/export_pdf\.php$#', $script)) {
    $active_page = 'export_pdf';
} elseif (preg_match('#/admin/pengaduan/#', $script)) {
    $active_page = 'pengaduan';
} elseif (preg_match('#/admin/fingerprint/manage_devices\.php$#', $script)) {
    $active_page = 'manage_devices';
} elseif (preg_match('#/admin/fingerprint/#', $script)) {
    $active_page = 'fingerprint';
} elseif (preg_match('#/admin/realtime/#', $script)) {
    $active_page = 'realtime';
} elseif (preg_match('#/admin/pengaturan_jam_kerja\.php$#', $script)) {
    $active_page = 'pengaturan_jam_kerja';
} elseif (preg_match('#/admin/jalankan_sinkronisasi\.php$#', $script)) {
    $active_page = 'jalankan_sinkronisasi';
} elseif (preg_match('#/admin/optimize_database\.php$#', $script)) {
    $active_page = 'optimize_database';
} elseif (preg_match('#/profil\.php$#', $script)) {
    $active_page = 'profil';
} elseif (preg_match('#/guru/absensi_guru\.php$#', $script)) {
    $active_page = 'absensi_guru';
} elseif (preg_match('#/guru/absensi_siswa\.php$#', $script)) {
    $active_page = 'absensi_siswa';
} elseif (preg_match('#/guru/laporan_guru\.php$#', $script)) {
    $active_page = 'laporan_guru';
} elseif (preg_match('#/guru/laporan_siswa\.php$#', $script)) {
    $active_page = 'laporan_siswa';
} elseif (preg_match('#/guru/log_absensi\.php$#', $script)) {
    $active_page = 'log_absensi';
} elseif (preg_match('#/guru/monitor_fingerprint\.php$#', $script)) {
    $active_page = 'monitor_fingerprint';
} elseif (preg_match('#/guru/realtime_attendance\.php$#', $script)) {
    $active_page = 'realtime_attendance';
} elseif (preg_match('#/guru/list_users_guru\.php$#', $script)) {
    $active_page = 'list_users_guru';
} elseif (preg_match('#/guru/profil\.php$#', $script)) { // Keep this for backward compatibility if needed, but new logic is better
    $active_page = 'profil';
}
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo ($role === 'admin') ? '/absensi_sekolah/admin/index.php' : '/absensi_sekolah/guru/index.php'; ?>">
        <div class="sidebar-brand-icon">
            <img class="img-profile rounded-circle"
                src="<?php echo isset($_SESSION['user']['avatar']) && $_SESSION['user']['avatar'] ? '/absensi_sekolah/' . $_SESSION['user']['avatar'] : '/absensi_sekolah/assets/img/undraw_profile.svg'; ?>"
                alt="Profil" style="width: 40px; height: 40px;">
        </div>
        <div class="sidebar-brand-text mx-3">Absensi Sekolah</div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo ($role === 'admin') ? '/absensi_sekolah/admin/index.php' : '/absensi_sekolah/guru/index.php'; ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <?php if ($role === 'admin'): ?>
        <div class="sidebar-heading">Manajemen Data</div>
        <?php
        $data_master_pages = array('guru','siswa','kelas','jurusan','users');
        $is_data_master_active = in_array($active_page, $data_master_pages);
        ?>
        <li class="nav-item <?php echo $is_data_master_active ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseDataMaster"
               aria-expanded="<?php echo $is_data_master_active ? 'true' : 'false'; ?>" aria-controls="collapseDataMaster">
                <i class="fas fa-database"></i>
                <span>Data Master</span>
            </a>
            <div id="collapseDataMaster" class="collapse <?php echo $is_data_master_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo ($active_page === 'guru') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/guru/list_guru.php"><i class="fas fa-chalkboard-teacher mr-2 text-gray-400"></i>Data Guru</a>
                    <a class="collapse-item <?php echo ($active_page === 'siswa') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/siswa/list_siswa.php"><i class="fas fa-users mr-2 text-gray-400"></i>Data Siswa</a>
                    <a class="collapse-item <?php echo ($active_page === 'kelas') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/kelas/list_kelas.php"><i class="fas fa-door-open mr-2 text-gray-400"></i>Data Kelas</a>
                    <a class="collapse-item <?php echo ($active_page === 'jurusan') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/jurusan/list_jurusan.php"><i class="fas fa-graduation-cap mr-2 text-gray-400"></i>Data Jurusan</a>
                    <a class="collapse-item <?php echo ($active_page === 'users') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/users/list_users.php"><i class="fas fa-user-cog mr-2 text-gray-400"></i>Manajemen User</a>
                </div>
            </div>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Fitur</div>

        <?php
        $laporan_pages = array('laporan_absensi','laporan_siswa','laporan_guru');
        $is_laporan_active = in_array($active_page, $laporan_pages);
        ?>
        <li class="nav-item <?php echo $is_laporan_active ? 'active' : ''; ?>">
            <a class="nav-link collapsed" href="javascript:void(0);" data-toggle="collapse" data-target="#collapseLaporan"
               aria-expanded="<?php echo $is_laporan_active ? 'true' : 'false'; ?>" aria-controls="collapseLaporan">
                <i class="fas fa-file-alt"></i>
                <span>Laporan</span>
            </a>
            <div id="collapseLaporan" class="collapse <?php echo $is_laporan_active ? 'show' : ''; ?>" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item <?php echo ($active_page === 'laporan_absensi') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/laporan/laporan_absensi.php">Laporan Absensi</a>
                    <a class="collapse-item <?php echo ($active_page === 'laporan_siswa') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/laporan/laporan_siswa.php">Laporan Siswa</a>
                    <a class="collapse-item <?php echo ($active_page === 'laporan_guru') ? 'active' : ''; ?>" href="/absensi_sekolah/admin/laporan/laporan_guru.php">Laporan Guru</a>
                </div>
            </div>
        </li>

        <li class="nav-item <?php echo ($active_page === 'pengaduan') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/pengaduan/list_pengaduan.php">
                <i class="fas fa-comments"></i><span>Pengaduan</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'fingerprint') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/fingerprint/manage_fingerprint_users.php">
                <i class="fas fa-fingerprint"></i><span>Fingerprint</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'manage_devices') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/fingerprint/manage_devices.php">
                <i class="fas fa-server"></i><span>Manajemen Device</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'realtime') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/realtime/dashboard_realtime.php">
                <i class="fas fa-broadcast-tower"></i><span>Realtime</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'pengaturan_jam_kerja') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/pengaturan_jam_kerja.php">
                <i class="fas fa-clock"></i><span>Pengaturan Jam Kerja</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'jalankan_sinkronisasi') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/jalankan_sinkronisasi.php">
                <i class="fas fa-sync-alt"></i><span>Sinkronisasi Absensi</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'optimize_database') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/admin/optimize_database.php">
                <i class="fas fa-database"></i><span>Optimize Database</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        <li class="nav-item <?php echo ($active_page === 'profil') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/profil.php">
                <i class="fas fa-user"></i><span>Profil</span>
            </a>
        </li>

    <?php elseif ($role === 'guru'): ?>
        <div class="sidebar-heading">Menu Guru</div>
        <!-- <li class="nav-item <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/index.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li> -->
        <li class="nav-item <?php echo ($active_page === 'absensi_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/absensi_guru.php">
                <i class="fas fa-user-check"></i><span>Absensi Guru</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'absensi_siswa') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/absensi_siswa.php">
                <i class="fas fa-user-clock"></i><span>Absensi Siswa</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'laporan_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/laporan_guru.php">
                <i class="fas fa-file-alt"></i><span>Laporan Guru</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'laporan_siswa') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/laporan_siswa.php">
                <i class="fas fa-file-alt"></i><span>Laporan Siswa</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'log_absensi') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/log_absensi.php">
                <i class="fas fa-clipboard-list"></i><span>Log Absensi</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'monitor_fingerprint') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/monitor_fingerprint.php">
                <i class="fas fa-fingerprint"></i><span>Monitor Fingerprint</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'realtime_attendance') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/realtime_attendance.php">
                <i class="fas fa-broadcast-tower"></i><span>Realtime Attendance</span>
            </a>
        </li>
        <li class="nav-item <?php echo ($active_page === 'list_users_guru') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/guru/list_users_guru.php">
                <i class="fas fa-user-cog"></i><span>Data User Guru</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        <li class="nav-item <?php echo ($active_page === 'profil') ? 'active' : ''; ?>">
            <a class="nav-link" href="/absensi_sekolah/profil.php">
                <i class="fas fa-user"></i><span>Profil</span>
            </a>
        </li>
    <?php endif; ?>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<script>
$(document).ready(function() {
    // Handle dropdown toggle with proper accordion behavior
    $('.nav-link[data-toggle="collapse"]').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var target = $(this).data('target');
        var isCurrentlyExpanded = $(this).attr('aria-expanded') === 'true';
        
        // Close all other dropdowns first
        $('.collapse.show').not(target).collapse('hide');
        $('.nav-link[data-toggle="collapse"]').not(this).attr('aria-expanded', 'false');
        $('.nav-link[data-toggle="collapse"]').not(this).addClass('collapsed');
        
        // Toggle current dropdown
        if (isCurrentlyExpanded) {
            $(target).collapse('hide');
            $(this).attr('aria-expanded', 'false');
            $(this).addClass('collapsed');
        } else {
            $(target).collapse('show');
            $(this).attr('aria-expanded', 'true');
            $(this).removeClass('collapsed');
        }
    });
    
    // Prevent dropdown from closing when clicking on collapse items
    $('.collapse-item').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>