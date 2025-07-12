<?php
session_start();
$title = "Test Koneksi Fingerprint";
$active_page = "test_fingerprint";
include '../templates/header.php';
include '../templates/sidebar.php';
include '../includes/fingerprint_config.php';

$test_results = [];
$device_ip = isset($_POST['device_ip']) ? $_POST['device_ip'] : FINGERPRINT_IP;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_connection'])) {
    try {
        require '../includes/zklib/zklibrary.php';
        $zk = new ZKLibrary($device_ip, 4370);
        
        // Test 1: Koneksi dasar
        $test_results['connection'] = [
            'name' => 'Koneksi Dasar',
            'status' => $zk->connect() ? 'SUCCESS' : 'FAILED',
            'message' => $zk->connect() ? 'Berhasil terhubung ke perangkat' : 'Gagal terhubung ke perangkat'
        ];
        
        if ($zk->connect()) {
            // Test 2: Ping device
            $ping_result = $zk->ping();
            $test_results['ping'] = [
                'name' => 'Ping Device',
                'status' => $ping_result !== 'down' ? 'SUCCESS' : 'FAILED',
                'message' => $ping_result !== 'down' ? "Response time: {$ping_result}ms" : 'Device tidak merespon'
            ];
            
            // Test 3: Get device info
            $test_results['version'] = [
                'name' => 'Informasi Device',
                'status' => 'SUCCESS',
                'message' => 'Version: ' . $zk->getVersion()
            ];
            
            // Test 4: Get users count
            $zk->disableDevice();
            $users = $zk->getUser();
            $test_results['users'] = [
                'name' => 'Data Pengguna',
                'status' => 'SUCCESS',
                'message' => 'Total pengguna: ' . count($users)
            ];
            
            // Test 5: Get attendance count
            $attendance = $zk->getAttendance();
            $test_results['attendance'] = [
                'name' => 'Data Absensi',
                'status' => 'SUCCESS',
                'message' => 'Total record absensi: ' . count($attendance)
            ];
            
            // Test 6: Get device time
            $device_time = $zk->getTime();
            $test_results['time'] = [
                'name' => 'Waktu Device',
                'status' => 'SUCCESS',
                'message' => 'Device time: ' . $device_time
            ];
            
            $zk->enableDevice();
            $zk->disconnect();
            
        } else {
            $test_results['ping'] = [
                'name' => 'Ping Device',
                'status' => 'SKIPPED',
                'message' => 'Test dilewati karena koneksi gagal'
            ];
            $test_results['version'] = [
                'name' => 'Informasi Device',
                'status' => 'SKIPPED',
                'message' => 'Test dilewati karena koneksi gagal'
            ];
            $test_results['users'] = [
                'name' => 'Data Pengguna',
                'status' => 'SKIPPED',
                'message' => 'Test dilewati karena koneksi gagal'
            ];
            $test_results['attendance'] = [
                'name' => 'Data Absensi',
                'status' => 'SKIPPED',
                'message' => 'Test dilewati karena koneksi gagal'
            ];
            $test_results['time'] = [
                'name' => 'Waktu Device',
                'status' => 'SKIPPED',
                'message' => 'Test dilewati karena koneksi gagal'
            ];
        }
        
    } catch (Exception $e) {
        $test_results['error'] = [
            'name' => 'Error',
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">Test Koneksi Fingerprint</h1>
        </nav>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Test Koneksi Perangkat Fingerprint</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="device_ip">IP Address Perangkat:</label>
                                    <input type="text" class="form-control" id="device_ip" name="device_ip" 
                                           value="<?php echo htmlspecialchars($device_ip); ?>" required>
                                </div>
                                <button type="submit" name="test_connection" class="btn btn-primary">
                                    <i class="fas fa-plug"></i> Test Koneksi
                                </button>
                            </form>
                            
                            <?php if (!empty($test_results)): ?>
                                <hr>
                                <h6 class="m-0 font-weight-bold text-primary">Hasil Test</h6>
                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Test</th>
                                                <th>Status</th>
                                                <th>Pesan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($test_results as $test): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($test['name']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $badge_class = '';
                                                        switch ($test['status']) {
                                                            case 'SUCCESS':
                                                                $badge_class = 'badge-success';
                                                                break;
                                                            case 'FAILED':
                                                                $badge_class = 'badge-danger';
                                                                break;
                                                            case 'ERROR':
                                                                $badge_class = 'badge-danger';
                                                                break;
                                                            case 'SKIPPED':
                                                                $badge_class = 'badge-warning';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>">
                                                            <?php echo $test['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($test['message']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Summary -->
                                <div class="mt-3">
                                    <?php
                                    $success_count = 0;
                                    $failed_count = 0;
                                    $error_count = 0;
                                    $skipped_count = 0;
                                    
                                    foreach ($test_results as $test) {
                                        switch ($test['status']) {
                                            case 'SUCCESS':
                                                $success_count++;
                                                break;
                                            case 'FAILED':
                                                $failed_count++;
                                                break;
                                            case 'ERROR':
                                                $error_count++;
                                                break;
                                            case 'SKIPPED':
                                                $skipped_count++;
                                                break;
                                        }
                                    }
                                    ?>
                                    
                                    <div class="alert alert-info">
                                        <strong>Ringkasan Test:</strong><br>
                                        ✅ Berhasil: <?php echo $success_count; ?><br>
                                        ❌ Gagal: <?php echo $failed_count; ?><br>
                                        ⚠️ Error: <?php echo $error_count; ?><br>
                                        ⏭️ Dilewati: <?php echo $skipped_count; ?>
                                    </div>
                                    
                                    <?php if ($failed_count > 0 || $error_count > 0): ?>
                                        <div class="alert alert-warning">
                                            <strong>Troubleshooting:</strong><br>
                                            1. Periksa IP address perangkat fingerprint<br>
                                            2. Pastikan perangkat terhubung ke jaringan yang sama<br>
                                            3. Cek firewall dan port 4370<br>
                                            4. Restart perangkat fingerprint jika diperlukan
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 