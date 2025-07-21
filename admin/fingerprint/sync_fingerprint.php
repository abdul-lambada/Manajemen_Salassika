<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$title = "Sinkronisasi Fingerprint";
$active_page = "sync_fingerprint";
include '../../templates/header.php';
include '../../templates/sidebar.php';

require '../../includes/zklib/zklibrary.php';
include '../../includes/db.php';

$connection_message = '';
$connection_class = '';
$synchronized_data = [];
$user_sync_data = [];

if (isset($_SESSION['connection_message'])) {
    $connection_message = $_SESSION['connection_message'];
    $connection_class = $_SESSION['connection_class'];
    unset($_SESSION['connection_message'], $_SESSION['connection_class']);
}

if (isset($_SESSION['synchronized_data'])) {
    $synchronized_data = $_SESSION['synchronized_data'];
    unset($_SESSION['synchronized_data']);
}

// Fungsi mapping hak fingerprint ke role sistem
function mapFingerprintRoleToSystemRole($fingerprintRole) {
    // Angka
    if ($fingerprintRole == 0) return 'guru'; // User
    if ($fingerprintRole == 14 || $fingerprintRole == 15) return false; // Admin/Superadmin abaikan

    // String (case-insensitive)
    $role = strtolower($fingerprintRole);
    if ($role === 'user') return 'guru';
    if ($role === 'pendaftar') return 'siswa';
    if ($role === 'admin') return false; // Abaikan admin

    // Default abaikan
    return false;
}

// Cek apakah tombol "Sinkronkan" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_ip'])) {
    $device_ip = $_POST['device_ip'];
    $device_port = 4370;
    $sync_type = isset($_POST['sync_type']) ? $_POST['sync_type'] : 'attendance'; // 'attendance' atau 'users'

    try {
        $zk = new ZKLibrary($device_ip, $device_port);

        if ($zk->connect()) {
            $connection_message = 'Berhasil terhubung ke perangkat fingerprint dengan IP: ' . htmlspecialchars($device_ip, ENT_QUOTES);
            $connection_class = 'alert-success';

            $zk->disableDevice();

            if ($sync_type === 'users') {
                // Sinkronisasi data user dari device fingerprint
                $users = $zk->getUser();
                $processed_users = 0;
                $updated_users = 0;

                foreach ($users as $user) {
                    $uid = $user[0]; // UID dari device
                    $name = $user[1]; // Nama dari device
                    $role = isset($user[2]) ? $user[2] : 'pendaftar'; // Hak/role dari device
                    $password = isset($user[3]) ? $user[3] : '123456'; // Password dari device (jika ada)

                    // Mapping role
                    $system_role = mapFingerprintRoleToSystemRole($role);
                    if ($system_role === false) continue; // Lewati user dengan role admin

                    // Cek apakah UID sudah ada di tabel users
                    $check_user = $conn->prepare("SELECT id, name, role FROM users WHERE uid = ?");
                    $check_user->execute([$uid]);
                    $existing_user = $check_user->fetch(PDO::FETCH_ASSOC);

                    if ($existing_user) {
                        // Update user yang sudah ada
                        $update_user = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE uid = ?");
                        $update_user->execute([$name, $system_role, $uid]);
                        $updated_users++;
                        $user_sync_data[] = [
                            'uid' => $uid,
                            'name' => $name,
                            'role' => $system_role,
                            'status' => 'Updated'
                        ];
                    } else {
                        // Insert user baru
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_user = $conn->prepare("INSERT INTO users (name, password, role, uid) VALUES (?, ?, ?, ?)");
                        $insert_user->execute([$name, $hashed_password, $system_role, $uid]);
                        $processed_users++;
                        $user_sync_data[] = [
                            'uid' => $uid,
                            'name' => $name,
                            'role' => $system_role,
                            'status' => 'Added'
                        ];
                    }
                }

                $connection_message .= " Berhasil sinkronisasi $processed_users user baru dan update $updated_users user.";
            } else {
                // Sinkronisasi absensi (kode yang sudah ada)
                $users = $zk->getUser();
                $attendance = $zk->getAttendance();

                if (empty($attendance)) {
                    $connection_message .= ' Tidak ada data absensi yang diambil dari mesin fingerprint.';
                }

                $processed_count = 0;
                $error_count = 0;

                foreach ($attendance as $record) {
                    $uid = $record[0];
                    $user_id = $record[1];
                    $status = $record[2];
                    $timestamp = date('Y-m-d H:i:s', strtotime($record[3]));
                    $verification_mode = isset($record[4]) ? $record[4] : 'Unknown';

                    $user_name = isset($users[$user_id]) ? $users[$user_id][1] : 'Unknown';
                    $status_text = $status == 0 ? 'Masuk' : 'Keluar';

                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE user_id = ? AND timestamp = ?");
                    $check_stmt->execute([$user_id, $timestamp]);
                    $exists = $check_stmt->fetchColumn();

                    if (!$exists) {
                        $user_stmt = $conn->prepare("SELECT id, name FROM users WHERE uid = ?");
                        $user_stmt->execute([$user_id]);
                        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($user) {
                            $insert_stmt = $conn->prepare("INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status) VALUES (?, ?, ?, ?, ?)");
                            $insert_stmt->execute([$user['id'], $user['name'], $timestamp, $verification_mode, $status_text]);

                            // Mapping dengan siswa
                            $siswa_stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE user_id = ?");
                            $siswa_stmt->execute([$user['id']]);
                            $siswa = $siswa_stmt->fetch(PDO::FETCH_ASSOC);

                            if ($siswa) {
                                $tanggal = date('Y-m-d', strtotime($timestamp));
                                $jam_masuk = date('H:i:s', strtotime($timestamp));
                                
                                $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_siswa WHERE id_siswa = ? AND tanggal = ?");
                                $check_absensi->execute([$siswa['id_siswa'], $tanggal]);
                                
                                if ($check_absensi->fetchColumn() == 0) {
                                    $insert_absensi = $conn->prepare("INSERT INTO absensi_siswa (id_siswa, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                                    $insert_absensi->execute([$siswa['id_siswa'], $tanggal, $jam_masuk]);
                                }
                                
                                $synchronized_data[] = [
                                    'id_siswa' => $siswa['id_siswa'],
                                    'nama' => $user['name'],
                                    'tanggal' => $tanggal,
                                    'jam_masuk' => $jam_masuk,
                                    'tipe' => 'Siswa'
                                ];
                            } else {
                                // Mapping dengan guru
                                $guru_stmt = $conn->prepare("SELECT id_guru FROM guru WHERE user_id = ?");
                                $guru_stmt->execute([$user['id']]);
                                $guru = $guru_stmt->fetch(PDO::FETCH_ASSOC);

                                if ($guru) {
                                    $tanggal = date('Y-m-d', strtotime($timestamp));
                                    $jam_masuk = date('H:i:s', strtotime($timestamp));
                                    
                                    $check_absensi = $conn->prepare("SELECT COUNT(*) FROM absensi_guru WHERE id_guru = ? AND tanggal = ?");
                                    $check_absensi->execute([$guru['id_guru'], $tanggal]);
                                    
                                    if ($check_absensi->fetchColumn() == 0) {
                                        $insert_absensi = $conn->prepare("INSERT INTO absensi_guru (id_guru, tanggal, status_kehadiran, jam_masuk, catatan) VALUES (?, ?, 'Hadir', ?, 'Absensi via Fingerprint')");
                                        $insert_absensi->execute([$guru['id_guru'], $tanggal, $jam_masuk]);
                                    }
                                    
                                    $synchronized_data[] = [
                                        'id_guru' => $guru['id_guru'],
                                        'nama' => $user['name'],
                                        'tanggal' => $tanggal,
                                        'jam_masuk' => $jam_masuk,
                                        'tipe' => 'Guru'
                                    ];
                                } else {
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
            }

            $zk->enableDevice();
            $zk->disconnect();
        } else {
            $connection_message = 'Gagal terhubung ke perangkat fingerprint. Periksa IP Address: ' . htmlspecialchars($device_ip, ENT_QUOTES);
            $connection_class = 'alert-danger';
        }
    } catch (Exception $e) {
        $connection_message = 'Terjadi kesalahan: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        $connection_class = 'alert-danger';
    }
}
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include '../../templates/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Sinkronisasi Fingerprint</h1>
            
            <?php if (!empty($connection_message)): ?>
                <div class="alert <?= $connection_class ?>">
                    <?= $connection_message ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sinkronisasi Data</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label>IP Address Device Fingerprint:</label>
                                    <input type="text" name="device_ip" class="form-control" placeholder="192.168.1.100" required>
                                </div>
                                <div class="form-group">
                                    <label>Tipe Sinkronisasi:</label>
                                    <select name="sync_type" class="form-control">
                                        <option value="users">Sinkronisasi User</option>
                                        <option value="attendance">Sinkronisasi Absensi</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Sinkronkan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($user_sync_data)): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Hasil Sinkronisasi User</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>UID</th>
                                            <th>Nama</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user_sync_data as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['uid']) ?></td>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td><?= htmlspecialchars($user['role']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $user['status'] === 'Added' ? 'success' : 'info' ?>">
                                                    <?= $user['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($synchronized_data)): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Hasil Sinkronisasi Absensi</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                            <th>Tipe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($synchronized_data as $data): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($data['nama']) ?></td>
                                            <td><?= htmlspecialchars($data['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($data['jam_masuk']) ?></td>
                                            <td><?= htmlspecialchars($data['tipe']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../../templates/footer.php'; ?>
</div>
</body>
</html>