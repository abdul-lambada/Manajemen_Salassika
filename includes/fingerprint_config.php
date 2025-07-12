<?php
/**
 * Konfigurasi Fingerprint System
 * File ini berisi pengaturan untuk sistem fingerprint
 */

// Konfigurasi perangkat fingerprint
define('FINGERPRINT_IP', '192.168.1.201'); // Ganti dengan IP perangkat fingerprint Anda
define('FINGERPRINT_PORT', 4370);
define('FINGERPRINT_TIMEOUT', 5); // Timeout dalam detik

// Konfigurasi sinkronisasi
define('AUTO_SYNC_ENABLED', true); // Aktifkan sinkronisasi otomatis
define('SYNC_INTERVAL', 300); // Interval sinkronisasi dalam detik (5 menit)

// Konfigurasi mapping
define('MAPPING_SISWA_BY_NIS', true); // Mapping siswa berdasarkan NIS
define('MAPPING_SISWA_BY_NISN', true); // Mapping siswa berdasarkan NISN
define('MAPPING_GURU_BY_NIP', true); // Mapping guru berdasarkan NIP

// Konfigurasi status kehadiran
define('STATUS_MASUK', 0); // Status masuk dari fingerprint
define('STATUS_KELUAR', 1); // Status keluar dari fingerprint

// Konfigurasi mode verifikasi
define('VERIFICATION_FINGERPRINT', 1);
define('VERIFICATION_PIN', 2);
define('VERIFICATION_CARD', 3);

// Fungsi untuk mendapatkan konfigurasi fingerprint
function getFingerprintConfig() {
    return [
        'ip' => FINGERPRINT_IP,
        'port' => FINGERPRINT_PORT,
        'timeout' => FINGERPRINT_TIMEOUT,
        'auto_sync' => AUTO_SYNC_ENABLED,
        'sync_interval' => SYNC_INTERVAL
    ];
}

// Fungsi untuk mendapatkan mapping configuration
function getMappingConfig() {
    return [
        'siswa_by_nis' => MAPPING_SISWA_BY_NIS,
        'siswa_by_nisn' => MAPPING_SISWA_BY_NISN,
        'guru_by_nip' => MAPPING_GURU_BY_NIP
    ];
}

// Fungsi untuk mendapatkan status configuration
function getStatusConfig() {
    return [
        'masuk' => STATUS_MASUK,
        'keluar' => STATUS_KELUAR
    ];
}

// Fungsi untuk mendapatkan verification mode configuration
function getVerificationConfig() {
    return [
        'fingerprint' => VERIFICATION_FINGERPRINT,
        'pin' => VERIFICATION_PIN,
        'card' => VERIFICATION_CARD
    ];
}

// Fungsi untuk mengkonversi status fingerprint ke teks
function convertFingerprintStatus($status) {
    $statusConfig = getStatusConfig();
    return $status == $statusConfig['masuk'] ? 'Masuk' : 'Keluar';
}

// Fungsi untuk mengkonversi mode verifikasi ke teks
function convertVerificationMode($mode) {
    $verificationConfig = getVerificationConfig();
    
    switch ($mode) {
        case $verificationConfig['fingerprint']:
            return 'Fingerprint';
        case $verificationConfig['pin']:
            return 'PIN';
        case $verificationConfig['card']:
            return 'Card';
        default:
            return 'Unknown';
    }
}

// Fungsi untuk mengecek apakah IP fingerprint valid
function isValidFingerprintIP($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

// Fungsi untuk mengecek koneksi fingerprint
function checkFingerprintConnection($ip = null, $port = null) {
    if ($ip === null) {
        $config = getFingerprintConfig();
        $ip = $config['ip'];
        $port = $config['port'];
    }
    
    if (!isValidFingerprintIP($ip)) {
        return ['status' => false, 'message' => 'IP Address tidak valid'];
    }
    
    try {
        require_once __DIR__ . '/zklib/zklibrary.php';
        $zk = new ZKLibrary($ip, $port);
        
        if ($zk->connect()) {
            $zk->disconnect();
            return ['status' => true, 'message' => 'Koneksi berhasil'];
        } else {
            return ['status' => false, 'message' => 'Gagal terhubung ke perangkat'];
        }
    } catch (Exception $e) {
        return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
?>