<?php
/**
 * Konfigurasi Fingerprint Device
 * File ini berisi konfigurasi untuk perangkat fingerprint X100-C
 */

// IP Address default perangkat fingerprint
define('FINGERPRINT_IP', '192.168.1.201');

// Port default perangkat fingerprint
define('FINGERPRINT_PORT', 4370);

// Timeout koneksi (dalam detik)
define('FINGERPRINT_TIMEOUT', 5);

// Interval sinkronisasi otomatis (dalam detik)
define('SYNC_INTERVAL', 300); // 5 menit

// Path log file
define('FINGERPRINT_LOG_FILE', __DIR__ . '/../logs/fingerprint_sync.log');

// Konfigurasi database untuk log
define('LOG_TABLE', 'fingerprint_logs');

// Status koneksi
define('CONNECTION_STATUS', [
    'SUCCESS' => 'success',
    'ERROR' => 'error',
    'WARNING' => 'warning'
]);

// Mode verifikasi
define('VERIFICATION_MODES', [
    1 => 'Fingerprint',
    2 => 'PIN',
    3 => 'Card',
    4 => 'Face',
    5 => 'Password'
]);

// Status kehadiran
define('ATTENDANCE_STATUS', [
    0 => 'Masuk',
    1 => 'Keluar'
]);

/**
 * Fungsi untuk menulis log fingerprint
 */
function writeFingerprintLog($action, $message, $status = 'success') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("INSERT INTO " . LOG_TABLE . " (action, message, status) VALUES (?, ?, ?)");
        $stmt->execute([$action, $message, $status]);
        
        // Juga tulis ke file log
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$status] [$action] $message" . PHP_EOL;
        file_put_contents(FINGERPRINT_LOG_FILE, $log_message, FILE_APPEND | LOCK_EX);
        
        return true;
    } catch (Exception $e) {
        error_log("Error writing fingerprint log: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk mendapatkan status koneksi device
 */
function getDeviceStatus($ip = null) {
    if (!$ip) {
        $ip = FINGERPRINT_IP;
    }
    
    try {
        require_once __DIR__ . '/zklib/zklibrary.php';
        $zk = new ZKLibrary($ip, FINGERPRINT_PORT);
        
        if ($zk->connect()) {
            $zk->disconnect();
            return [
                'status' => 'connected',
                'message' => 'Device terhubung',
                'ip' => $ip
            ];
        } else {
            return [
                'status' => 'disconnected',
                'message' => 'Device tidak dapat dihubungi',
                'ip' => $ip
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage(),
            'ip' => $ip
        ];
    }
}

/**
 * Fungsi untuk mendapatkan statistik fingerprint
 */
function getFingerprintStats() {
    global $conn;
    
    try {
        // Total user di device
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $total_users = $stmt->fetchColumn();
        
        // Total kehadiran hari ini
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE DATE(timestamp) = ?");
        $stmt->execute([$today]);
        $today_attendance = $stmt->fetchColumn();
        
        // Total kehadiran bulan ini
        $month = date('Y-m');
        $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE DATE_FORMAT(timestamp, '%Y-%m') = ?");
        $stmt->execute([$month]);
        $month_attendance = $stmt->fetchColumn();
        
        // Status koneksi device
        $device_status = getDeviceStatus();
        
        return [
            'total_users' => $total_users,
            'today_attendance' => $today_attendance,
            'month_attendance' => $month_attendance,
            'device_status' => $device_status
        ];
    } catch (Exception $e) {
        writeFingerprintLog('GET_STATS', 'Error: ' . $e->getMessage(), 'error');
        return false;
    }
}
?>