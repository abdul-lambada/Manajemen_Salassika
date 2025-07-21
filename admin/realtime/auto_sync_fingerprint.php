<?php
// Set zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Path ke file konfigurasi dan library
$base_path = dirname(__DIR__, 2); // Kembali dua level dari admin/realtime/ ke root
include $base_path . '/includes/db.php';
include $base_path . '/includes/fingerprint_config.php';
require_once $base_path . '/includes/zklib/zklibrary.php';

// Path untuk file log cron
$log_file = $base_path . '/logs/cron_sync.log';

/**
 * Fungsi untuk menulis log ke file
 * @param string $message Pesan log
 * @param string $status  Status [INFO|SUCCESS|ERROR]
 */
function write_log($message, $status = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$status] - $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

write_log('Memulai proses sinkronisasi fingerprint...');

// Ambil IP dari konfigurasi
$ip_address = FINGERPRINT_IP;

if (empty($ip_address)) {
    write_log('IP address mesin fingerprint tidak diatur di fingerprint_config.php.', 'ERROR');
    exit('IP Address tidak dikonfigurasi.');
}

try {
    // Inisialisasi koneksi ke mesin
    $zk = new ZKLibrary($ip_address, FINGERPRINT_PORT);
    $zk->connect();
    $zk->disableDevice();

    write_log("Berhasil terhubung ke mesin di IP: $ip_address");

    // Ambil data log kehadiran dari mesin
    $log_kehadiran_mesin = $zk->getAttendance();

    if (empty($log_kehadiran_mesin)) {
        write_log('Tidak ada data absensi baru di mesin.');
    } else {
        write_log('Ditemukan ' . count($log_kehadiran_mesin) . ' data absensi baru. Memproses...');
        
        // Simpan data ke database
        $new_records = 0;
        foreach ($log_kehadiran_mesin as $row) {
            $user_id = $row[1];
            $timestamp = date('Y-m-d H:i:s', strtotime($row[3]));

            // Cek apakah data sudah ada di database untuk menghindari duplikat
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE user_id = ? AND timestamp = ?");
            $stmt_check->execute([$user_id, $timestamp]);
            $exists = $stmt_check->fetchColumn();

            if (!$exists) {
                $status = isset($ATTENDANCE_STATUS[$row[2]]) ? $ATTENDANCE_STATUS[$row[2]] : 'Unknown';
                $verification_mode = isset($VERIFICATION_MODES[$row[4]]) ? $VERIFICATION_MODES[$row[4]] : 'Unknown';
                
                // Ambil nama dari tabel users
                $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
                $stmt_user->execute([$user_id]);
                $user_name = $stmt_user->fetchColumn();

                $stmt_insert = $conn->prepare("
                    INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt_insert->execute([$user_id, $user_name, $timestamp, $verification_mode, $status]);
                $new_records++;
            }
        }
        write_log("Berhasil menyimpan $new_records data absensi baru ke database.", 'SUCCESS');
    }

    // Aktifkan kembali mesin dan putuskan koneksi
    $zk->enableDevice();
    $zk->disconnect();

    write_log('Proses sinkronisasi selesai.');

} catch (Exception $e) {
    write_log('Gagal terhubung atau memproses data: ' . $e->getMessage(), 'ERROR');
}

exit('Proses selesai.'); 