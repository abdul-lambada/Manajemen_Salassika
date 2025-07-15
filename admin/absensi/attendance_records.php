<?php
// Aktifkan output buffering untuk menghindari masalah header
ob_start();

$title = "Log Kehadiran";
$active_page = "attendance_records"; // Untuk menandai menu aktif di sidebar
include '../templates/header.php';
include '../templates/sidebar.php';

// Pagination: retrieve current page and set limit
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Jumlah data per halaman
$offset = ($page - 1) * $limit;

// Ambil nilai IP dari query string atau gunakan default kosong
$ip_address = isset($_GET['ip']) ? $_GET['ip'] : '';

// Cek apakah tombol submit ditekan untuk mengambil data dari mesin fingerprint
$message = '';
$alert_class = '';
if (isset($_POST['submit_ip'])) {
    $ip_address = trim($_POST['ip_address']);
    if (!empty($ip_address)) {
        // Redirect dengan parameter IP
        header("Location: attendance_records.php?ip=" . urlencode($ip_address));
        exit();
    } else {
        $message = 'IP Address tidak boleh kosong.';
        $alert_class = 'alert-danger';
    }
}

// Handle status messages
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = '';
$alert_class = '';

if ($status == 'delete_success') {
    $message = 'Data log kehadiran berhasil dihapus.';
    $alert_class = 'alert-success';
} elseif ($status == 'error') {
    $message = 'Terjadi kesalahan saat menghapus data log kehadiran.';
    $alert_class = 'alert-danger';
}

// Ambil data log kehadiran dari database
include '../includes/db.php';

// Query untuk mengambil data log kehadiran dengan informasi mapping
$stmt = $conn->query("
    SELECT SQL_CALC_FOUND_ROWS 
        tk.*,
        CASE 
            WHEN s.nama_siswa IS NOT NULL THEN CONCAT('Siswa: ', s.nama_siswa)
            WHEN g.nama_guru IS NOT NULL THEN CONCAT('Guru: ', g.nama_guru)
            ELSE tk.user_name
        END as mapped_name,
        CASE 
            WHEN s.id_siswa IS NOT NULL THEN 'Siswa'
            WHEN g.id_guru IS NOT NULL THEN 'Guru'
            ELSE 'Tidak Dikenal'
        END as user_type
    FROM tbl_kehadiran tk
    LEFT JOIN users u ON tk.user_id = u.id
    LEFT JOIN siswa s ON s.user_id = u.id
    LEFT JOIN guru g ON g.user_id = u.id
    ORDER BY tk.timestamp DESC 
    LIMIT $limit OFFSET $offset
");
$log_kehadiran = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of rows and compute total pages
$total = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($total / $limit);

// Ambil data dari mesin fingerprint jika IP address tersedia
if (!empty($ip_address)) {
    require '../includes/zklib/zklibrary.php';

    try {
        // Inisialisasi koneksi ke mesin X100-C
        $zk = new ZKLibrary($ip_address, 4370);
        $zk->connect();
        $zk->disableDevice();

        // Ambil data pengguna dan log kehadiran
        $users = $zk->getUser(); // Data pengguna
        $log_kehadiran_mesin = $zk->getAttendance(); // Data kehadiran

        // Simpan data kehadiran ke database
        $processed_count = 0;
        $mapped_count = 0;
        
        foreach ($log_kehadiran_mesin as $row) {
            $uid = $row[0]; // ID unik internal mesin (tidak digunakan)
            $user_id = $row[1]; // ID pengguna
            $status = $row[2]; // Status kehadiran (0 = Masuk, 1 = Keluar)
            $timestamp = date('Y-m-d H:i:s', strtotime($row[3])); // Format waktu
            $verification_mode = isset($row[4]) ? $row[4] : 'Unknown'; // Mode verifikasi

            // Mapping mode verifikasi ke teks yang lebih deskriptif
            switch ($verification_mode) {
                case 1:
                    $verification_mode_text = 'Fingerprint';
                    break;
                case 2:
                    $verification_mode_text = 'PIN';
                    break;
                case 3:
                    $verification_mode_text = 'Card';
                    break;
                default:
                    $verification_mode_text = 'Unknown';
                    break;
            }

            // Ambil nama pengguna dari data pengguna
            $user_name = isset($users[$user_id]) ? $users[$user_id][1] : 'Unknown'; // Nama pengguna

            // Konversi status ke teks
            $status_text = $status == 0 ? 'Masuk' : 'Keluar';

            // Cek apakah data sudah ada di database
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE user_id = ? AND timestamp = ?");
            $check_stmt->execute([$user_id, $timestamp]);
            $exists = $check_stmt->fetchColumn();

            if (!$exists) {
                // Cari user_id yang sesuai di tabel users berdasarkan uid
                $user_stmt = $conn->prepare("SELECT id, name FROM users WHERE uid = ?");
                $user_stmt->execute([$user_id]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Insert data baru ke database dengan user_id yang benar
                    $insert_stmt = $conn->prepare("INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status) VALUES (?, ?, ?, ?, ?)");
                    $insert_stmt->execute([$user['id'], $user['name'], $timestamp, $verification_mode_text, $status_text]);
                    $processed_count++;

                    // Coba mapping dengan data siswa berdasarkan user_id
                    $siswa_stmt = $conn->prepare("SELECT id_siswa, nama_siswa FROM siswa WHERE user_id = ?");
                    $siswa_stmt->execute([$user['id']]);
                    $siswa = $siswa_stmt->fetch(PDO::FETCH_ASSOC);

                    if ($siswa) {
                        $tanggal = date('Y-m-d', strtotime($timestamp));
                        $jam_masuk = date('H:i:s', strtotime($timestamp));
                        
                        // Cek apakah absensi siswa sudah ada untuk hari ini
                        $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_siswa WHERE id_siswa = ? AND tanggal = ?");
                        $check_absensi->execute([$siswa['id_siswa'], $tanggal]);
                        
                        if ($check_absensi->fetchColumn() == 0) {
                            // Insert ke tabel absensi_siswa
                            $insert_absensi = $conn->prepare("INSERT INTO absensi_siswa (id_siswa, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                            $insert_absensi->execute([$siswa['id_siswa'], $tanggal, $jam_masuk]);
                            $mapped_count++;
                        }
                    } else {
                        // Coba mapping dengan data guru berdasarkan user_id
                        $guru_stmt = $conn->prepare("SELECT id_guru, nama_guru FROM guru WHERE user_id = ?");
                        $guru_stmt->execute([$user['id']]);
                        $guru = $guru_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($guru) {
                            $tanggal = date('Y-m-d', strtotime($timestamp));
                            $jam_masuk = date('H:i:s', strtotime($timestamp));
                            
                            // Cek apakah absensi guru sudah ada untuk hari ini
                            $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_guru WHERE id_guru = ? AND tanggal = ?");
                            $check_absensi->execute([$guru['id_guru'], $tanggal]);
                            
                            if ($check_absensi->fetchColumn() == 0) {
                                // Insert ke tabel absensi_guru
                                $insert_absensi = $conn->prepare("INSERT INTO absensi_guru (id_guru, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                                $insert_absensi->execute([$guru['id_guru'], $tanggal, $jam_masuk]);
                                $mapped_count++;
                            }
                        }
                    }
                }
            }
        }

        // Aktifkan kembali mesin dan putuskan koneksi
        $zk->enableDevice();
        $zk->disconnect();

        $message = "Data log kehadiran berhasil diambil dari mesin. Diproses: $processed_count, Terpetakan: $mapped_count";
        $alert_class = 'alert-success';

        // Refresh data log kehadiran setelah menyimpan data baru
        $stmt = $conn->query("
            SELECT SQL_CALC_FOUND_ROWS 
                tk.*,
                CASE 
                    WHEN s.nama_siswa IS NOT NULL THEN CONCAT('Siswa: ', s.nama_siswa)
                    WHEN g.nama_guru IS NOT NULL THEN CONCAT('Guru: ', g.nama_guru)
                    ELSE tk.user_name
                END as mapped_name,
                CASE 
                    WHEN s.id_siswa IS NOT NULL THEN 'Siswa'
                    WHEN g.id_guru IS NOT NULL THEN 'Guru'
                    ELSE 'Tidak Dikenal'
                END as user_type
            FROM tbl_kehadiran tk
            LEFT JOIN users u ON tk.user_id = u.id
            LEFT JOIN siswa s ON s.user_id = u.id
            LEFT JOIN guru g ON g.user_id = u.id
            ORDER BY tk.timestamp DESC 
            LIMIT $limit OFFSET $offset
        ");
        $log_kehadiran = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $message = 'Gagal terhubung ke mesin fingerprint: ' . $e->getMessage();
        $alert_class = 'alert-danger';
    }
}
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">Log Kehadiran</h1>
        </nav>
        <div class="container-fluid">
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Form untuk input IP mesin fingerprint -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Masukkan IP Mesin Fingerprint</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group row">
                                    <label for="ip_address" class="col-sm-2 col-form-label">IP Address:</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?php echo htmlspecialchars($ip_address); ?>" placeholder="Contoh: 192.168.1.201">
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="submit" name="submit_ip" class="btn btn-primary btn-block">Ambil Data</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Log Kehadiran -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Data Log Kehadiran</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>User ID</th>
                                            <th>Nama</th>
                                            <th>Tipe</th>
                                            <th>Tanggal & Waktu</th>
                                            <th>Status</th>
                                            <th>Mode Verifikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        foreach ($log_kehadiran as $row): 
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['mapped_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['user_type'] == 'Siswa' ? 'primary' : ($row['user_type'] == 'Guru' ? 'success' : 'warning'); ?>">
                                                    <?php echo htmlspecialchars($row['user_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $row['status'] == 'Masuk' ? 'success' : 'danger'; ?>">
                                                    <?php echo htmlspecialchars($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['verification_mode']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&ip=<?php echo urlencode($ip_address); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&ip=<?php echo urlencode($ip_address); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&ip=<?php echo urlencode($ip_address); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>

<?php
// Kirim output buffered ke browser
ob_end_flush();
?>