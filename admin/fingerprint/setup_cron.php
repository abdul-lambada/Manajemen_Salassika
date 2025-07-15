<?php
session_start();
$title = "Setup Cron Job";
$active_page = "setup_cron";
include '../templates/header.php';
include '../templates/sidebar.php';
include '../includes/fingerprint_config.php';

$message = '';
$alert_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setup_cron'])) {
        $cron_interval = $_POST['cron_interval'];
        $php_path = $_POST['php_path'];
        $script_path = realpath(__DIR__ . '/../admin/auto_sync_fingerprint.php');
        
        // Generate cron command
        switch ($cron_interval) {
            case '1min':
                $cron_expression = '*/1 * * * *';
                break;
            case '5min':
                $cron_expression = '*/5 * * * *';
                break;
            case '10min':
                $cron_expression = '*/10 * * * *';
                break;
            case '30min':
                $cron_expression = '*/30 * * * *';
                break;
            case '1hour':
                $cron_expression = '0 * * * *';
                break;
            default:
                $cron_expression = '*/5 * * * *';
        }
        
        $cron_command = "$cron_expression $php_path $script_path";
        
        // Create cron job file
        $cron_file = __DIR__ . '/../logs/fingerprint_cron.txt';
        $cron_content = "# Fingerprint Auto Sync Cron Job\n";
        $cron_content .= "# Generated on: " . date('Y-m-d H:i:s') . "\n";
        $cron_content .= "# Interval: $cron_interval\n";
        $cron_content .= "# Command: $cron_command\n\n";
        $cron_content .= $cron_command . "\n";
        
        if (file_put_contents($cron_file, $cron_content)) {
            $message = "Cron job berhasil dibuat. File tersimpan di: $cron_file<br>";
            $message .= "Untuk mengaktifkan, jalankan perintah: <code>crontab $cron_file</code>";
            $alert_class = 'alert-success';
        } else {
            $message = "Gagal membuat file cron job.";
            $alert_class = 'alert-danger';
        }
    }
}

// Detect PHP path
$php_path = '';
if (function_exists('exec')) {
    $output = [];
    exec('which php', $output);
    if (!empty($output[0])) {
        $php_path = $output[0];
    } else {
        $php_path = '/usr/bin/php'; // Default path
    }
} else {
    $php_path = '/usr/bin/php';
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">Setup Cron Job</h1>
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

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Setup Cron Job untuk Sinkronisasi Otomatis</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="php_path">Path PHP:</label>
                                    <input type="text" class="form-control" id="php_path" name="php_path" 
                                           value="<?php echo htmlspecialchars($php_path); ?>" required>
                                    <small class="form-text text-muted">Path ke executable PHP di server Anda</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cron_interval">Interval Sinkronisasi:</label>
                                    <select class="form-control" id="cron_interval" name="cron_interval" required>
                                        <option value="1min">Setiap 1 menit</option>
                                        <option value="5min" selected>Setiap 5 menit</option>
                                        <option value="10min">Setiap 10 menit</option>
                                        <option value="30min">Setiap 30 menit</option>
                                        <option value="1hour">Setiap 1 jam</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="setup_cron" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Buat Cron Job
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Instruksi Setup</h6>
                        </div>
                        <div class="card-body">
                            <h6>Langkah-langkah:</h6>
                            <ol>
                                <li>Klik "Buat Cron Job"</li>
                                <li>File cron akan dibuat di folder logs</li>
                                <li>Login ke server via SSH</li>
                                <li>Jalankan perintah:
                                    <code>crontab logs/fingerprint_cron.txt</code>
                                </li>
                                <li>Verifikasi dengan:
                                    <code>crontab -l</code>
                                </li>
                            </ol>
                            
                            <hr>
                            
                            <h6>Perintah Berguna:</h6>
                            <ul>
                                <li><code>crontab -l</code> - Lihat cron job aktif</li>
                                <li><code>crontab -r</code> - Hapus semua cron job</li>
                                <li><code>crontab -e</code> - Edit cron job manual</li>
                            </ul>
                            
                            <hr>
                            
                            <h6>Monitoring:</h6>
                            <p>Log sinkronisasi akan tersimpan di:</p>
                            <code>logs/fingerprint_sync.log</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Cron Jobs -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Cron Job Saat Ini</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $cron_file = __DIR__ . '/../logs/fingerprint_cron.txt';
                            if (file_exists($cron_file)) {
                                $cron_content = file_get_contents($cron_file);
                                echo '<pre class="bg-light p-3">' . htmlspecialchars($cron_content) . '</pre>';
                            } else {
                                echo '<p class="text-muted">Belum ada file cron job yang dibuat.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Informasi Sistem</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Server Info:</h6>
                                    <ul>
                                        <li>OS: <?php echo php_uname('s') . ' ' . php_uname('r'); ?></li>
                                        <li>PHP Version: <?php echo PHP_VERSION; ?></li>
                                        <li>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                                        <li>User: <?php echo get_current_user(); ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Path Info:</h6>
                                    <ul>
                                        <li>Script Path: <?php echo realpath(__DIR__ . '/../admin/auto_sync_fingerprint.php'); ?></li>
                                        <li>Log Path: <?php echo realpath(__DIR__ . '/../logs/'); ?></li>
                                        <li>PHP Path: <?php echo $php_path; ?></li>
                                    </ul>
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