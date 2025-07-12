<?php
session_start(); // Mulai session untuk menyimpan status

$title = "Sinkronisasi Fingerprint";
$active_page = "sync_fingerprint"; // Untuk menandai menu aktif di sidebar
include '../templates/header.php';
include '../templates/sidebar.php';

// Load library fingerprint
require '../includes/zklib/zklibrary.php';

// Variabel untuk menyimpan pesan status
$connection_message = '';
$connection_class = '';

// Cek apakah ada status koneksi yang tersimpan di session
if (isset($_SESSION['connection_message'])) {
    $connection_message = $_SESSION['connection_message'];
    $connection_class = $_SESSION['connection_class'];
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

            // Ambil data absensi
            $attendance = $zk->getAttendance();

            // Aktifkan kembali perangkat
            $zk->enableDevice();
            $zk->disconnect();

            // Simpan data absensi ke variabel untuk ditampilkan
            $synchronized_data = [];
            foreach ($attendance as $record) {
                $synchronized_data[] = [
                    'id_siswa' => $record['uid'], // ID siswa dari fingerprint
                    'tanggal' => date('Y-m-d', strtotime($record['timestamp'])),
                    'jam_masuk' => date('H:i:s', strtotime($record['timestamp']))
                ];
            }

            if (empty($synchronized_data)) {
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
                                            <th>ID Siswa</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($synchronized_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['id_siswa']); ?></td>
                                                <td><?php echo htmlspecialchars($data['tanggal']); ?></td>
                                                <td><?php echo htmlspecialchars($data['jam_masuk']); ?></td>
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