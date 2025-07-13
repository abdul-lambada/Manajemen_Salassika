<?php
session_start(); // Mulai session untuk menyimpan status

$title = "Sinkronisasi Fingerprint";
$active_page = "sync_fingerprint"; // Untuk menandai menu aktif di sidebar
include '../templates/header.php';
include '../templates/sidebar.php';

// Load library fingerprint dan database
require '../includes/zklib/zklibrary.php';
include '../includes/db.php';

// Variabel untuk menyimpan pesan status
$connection_message = '';
$connection_class = '';
$synchronized_data = [];

// Cek apakah ada status koneksi yang tersimpan di session
if (isset($_SESSION['connection_message'])) {
    $connection_message = $_SESSION['connection_message'];
    $connection_class = $_SESSION['connection_class'];
    unset($_SESSION['connection_message'], $_SESSION['connection_class']); // Clear session
}

// Cek apakah tombol "Sinkronkan" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_ip'])) {
    $device_ip = $_POST['device_ip']; // Ambil IP dari input form
    $device_port = 4370; // Port default perangkat fingerprint

    try {
        // Inisialisasi koneksi ke perangkat
        $zk = new ZKLibrary($device_ip, $device_port);

        // Coba terhubung ke perangkat
        if ($zk->connect()) {
            $connection_message = 'Berhasil terhubung ke perangkat fingerprint dengan IP: ' . htmlspecialchars($device_ip, ENT_QUOTES);
            $connection_class = 'alert-success';

            // Nonaktifkan perangkat sementara
            $zk->disableDevice();

            // Ambil data pengguna dan absensi
            $users = $zk->getUser(); // Data pengguna dari fingerprint
            $attendance = $zk->getAttendance(); // Data absensi

            // Aktifkan kembali perangkat
            $zk->enableDevice();
            $zk->disconnect();

            // Proses data absensi dan simpan ke database
            $processed_count = 0;
            $error_count = 0;

            foreach ($attendance as $record) {
                $uid = $record[0]; // ID unik internal mesin
                $user_id = $record[1]; // ID pengguna
                $status = $record[2]; // Status kehadiran (0 = Masuk, 1 = Keluar)
                $timestamp = date('Y-m-d H:i:s', strtotime($record[3])); // Format waktu
                $verification_mode = isset($record[4]) ? $record[4] : 'Unknown';

                // Ambil nama pengguna dari data pengguna
                $user_name = isset($users[$user_id]) ? $users[$user_id][1] : 'Unknown';

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
                        // Insert data baru ke tbl_kehadiran dengan user_id yang benar
                        $insert_stmt = $conn->prepare("INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status) VALUES (?, ?, ?, ?, ?)");
                        $insert_stmt->execute([$user['id'], $user['name'], $timestamp, $verification_mode, $status_text]);

                        // Coba mapping dengan data siswa berdasarkan user_id
                        $siswa_stmt = $conn->prepare("SELECT id_siswa, nama_siswa FROM siswa WHERE user_id = ?");
                        $siswa_stmt->execute([$user['id']]);
                        $siswa = $siswa_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($siswa) {
                            // Mapping dengan siswa berhasil
                            $tanggal = date('Y-m-d', strtotime($timestamp));
                            $jam_masuk = date('H:i:s', strtotime($timestamp));
                            
                            // Cek apakah absensi siswa sudah ada untuk hari ini
                            $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_siswa WHERE id_siswa = ? AND tanggal = ?");
                            $check_absensi->execute([$siswa['id_siswa'], $tanggal]);
                            
                            if ($check_absensi->fetchColumn() == 0) {
                                // Insert ke tabel absensi_siswa
                                $insert_absensi = $conn->prepare("INSERT INTO absensi_siswa (id_siswa, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                                $insert_absensi->execute([$siswa['id_siswa'], $tanggal, $jam_masuk]);
                            }
                            
                            $synchronized_data[] = [
                                'id_siswa' => $siswa['id_siswa'],
                                'nama' => $siswa['nama_siswa'],
                                'tanggal' => $tanggal,
                                'jam_masuk' => $jam_masuk,
                                'tipe' => 'Siswa'
                            ];
                        } else {
                            // Coba mapping dengan data guru berdasarkan user_id
                            $guru_stmt = $conn->prepare("SELECT id_guru, nama_guru FROM guru WHERE user_id = ?");
                            $guru_stmt->execute([$user['id']]);
                            $guru = $guru_stmt->fetch(PDO::FETCH_ASSOC);

                            if ($guru) {
                                // Mapping dengan guru berhasil
                                $tanggal = date('Y-m-d', strtotime($timestamp));
                                $jam_masuk = date('H:i:s', strtotime($timestamp));
                                
                                // Cek apakah absensi guru sudah ada untuk hari ini
                                $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_guru WHERE id_guru = ? AND tanggal = ?");
                                $check_absensi->execute([$guru['id_guru'], $tanggal]);
                                
                                if ($check_absensi->fetchColumn() == 0) {
                                    // Insert ke tabel absensi_guru
                                    $insert_absensi = $conn->prepare("INSERT INTO absensi_guru (id_guru, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                                    $insert_absensi->execute([$guru['id_guru'], $tanggal, $jam_masuk]);
                                }
                                
                                $synchronized_data[] = [
                                    'id_guru' => $guru['id_guru'],
                                    'nama' => $guru['nama_guru'],
                                    'tanggal' => $tanggal,
                                    'jam_masuk' => $jam_masuk,
                                    'tipe' => 'Guru'
                                ];
                            } else {
                                // Tidak ada mapping yang ditemukan
                                $synchronized_data[] = [
                                    'user_id' => $user['id'],
                                    'nama' => $user['name'],
                                    'tanggal' => date('Y-m-d', strtotime($timestamp)),
                                    'jam_masuk' => date('H:i:s', strtotime($timestamp)),
                                    'tipe' => 'Tidak Dikenal'
                                ];
                                $error_count++;
                            }
                        }
                        $processed_count++;
                    } else {
                        // User tidak ditemukan di tabel users
                        $synchronized_data[] = [
                            'user_id' => $user_id,
                            'nama' => $user_name,
                            'tanggal' => date('Y-m-d', strtotime($timestamp)),
                            'jam_masuk' => date('H:i:s', strtotime($timestamp)),
                            'tipe' => 'User Tidak Ditemukan'
                        ];
                        $error_count++;
                    }
                }
            }

            if ($processed_count > 0) {
                $connection_message .= " Berhasil memproses $processed_count data absensi.";
                if ($error_count > 0) {
                    $connection_message .= " $error_count data tidak dapat dipetakan.";
                }
            } else {
                $connection_message .= ' Tidak ada data absensi baru.';
            }
        } else {
            // Jika gagal terhubung
            $connection_message = 'Gagal terhubung ke perangkat fingerprint. Periksa IP Address: ' . htmlspecialchars($device_ip, ENT_QUOTES);
            $connection_class = 'alert-danger';
        }
    } catch (Exception $e) {
        // Tangkap error jika terjadi masalah saat menghubungkan
        $connection_message = 'Terjadi kesalahan: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        $connection_class = 'alert-danger';
    }

    // Simpan status koneksi ke session
    $_SESSION['connection_message'] = $connection_message;
    $_SESSION['connection_class'] = $connection_class;

    // Redirect ke halaman yang sama untuk menghindari resubmission form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">Sinkronisasi Fingerprint</h1>
        </nav>
        <div class="container-fluid">
            <!-- Begin Alert SB Admin 2 -->
            <?php if (!empty($connection_message)): ?>
                <div class="alert <?php echo $connection_class; ?>" role="alert">
                    <?php echo $connection_message; ?>
                </div>
            <?php endif; ?>
            <!-- End Alert SB Admin 2 -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sinkronisasi Data Absensi</h6>
                        </div>
                        <div class="card-body">
                            <!-- Form untuk input IP dan tombol Sinkronkan -->
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="device_ip">IP Address Perangkat Fingerprint:</label>
                                    <input type="text" id="device_ip" name="device_ip" placeholder="Contoh: 192.168.1.201" required class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Sinkronkan</button>
                            </form>

                            <!-- Tampilkan data absensi jika ada -->
                            <?php if (!empty($synchronized_data)): ?>
                                <hr>
                                <h6 class="m-0 font-weight-bold text-primary">Data Absensi yang Disinkronkan</h6>
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                            <th>Tipe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($synchronized_data as $data): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                    if (isset($data['id_siswa'])) {
                                                        echo 'S-' . htmlspecialchars($data['id_siswa']);
                                                    } elseif (isset($data['id_guru'])) {
                                                        echo 'G-' . htmlspecialchars($data['id_guru']);
                                                    } else {
                                                        echo htmlspecialchars($data['user_id']);
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($data['tanggal']); ?></td>
                                                <td><?php echo htmlspecialchars($data['jam_masuk']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $data['tipe'] == 'Siswa' ? 'primary' : ($data['tipe'] == 'Guru' ? 'success' : 'warning'); ?>">
                                                        <?php echo htmlspecialchars($data['tipe']); ?>
                                                    </span>
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
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>