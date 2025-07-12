<?php
// /**
//  * Auto Sync Fingerprint - File untuk sinkronisasi otomatis data fingerprint
//  * Dapat dijalankan via cron job: */5 * * * * php /path/to/auto_sync_fingerprint.php*
//  **/

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load library fingerprint dan database
require_once __DIR__ . '/../includes/zklib/zklibrary.php';
require_once __DIR__ . '/../includes/db.php';

// Konfigurasi perangkat fingerprint
$device_ip = '192.168.1.201'; // Ganti dengan IP perangkat fingerprint Anda
$device_port = 4370;

// Log file
$log_file = __DIR__ . '/../logs/fingerprint_sync.log';

// Fungsi untuk menulis log
function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

try {
    writeLog("Memulai sinkronisasi otomatis fingerprint...");
    
    // Inisialisasi koneksi ke perangkat
    $zk = new ZKLibrary($device_ip, $device_port);
    
    // Coba terhubung ke perangkat
    if ($zk->connect()) {
        writeLog("Berhasil terhubung ke perangkat fingerprint: $device_ip");
        
        // Nonaktifkan perangkat sementara
        $zk->disableDevice();
        
        // Ambil data pengguna dan absensi
        $users = $zk->getUser();
        $attendance = $zk->getAttendance();
        
        // Aktifkan kembali perangkat
        $zk->enableDevice();
        $zk->disconnect();
        
        writeLog("Berhasil mengambil data: " . count($attendance) . " record absensi");
        
        // Proses data absensi
        $processed_count = 0;
        $error_count = 0;
        $siswa_count = 0;
        $guru_count = 0;
        
        foreach ($attendance as $record) {
            $uid = $record[0];
            $user_id = $record[1];
            $status = $record[2];
            $timestamp = date('Y-m-d H:i:s', strtotime($record[3]));
            $verification_mode = isset($record[4]) ? $record[4] : 'Unknown';
            
            // Ambil nama pengguna
            $user_name = isset($users[$user_id]) ? $users[$user_id][1] : 'Unknown';
            $status_text = $status == 0 ? 'Masuk' : 'Keluar';
            
            // Cek apakah data sudah ada di database
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_kehadiran WHERE user_id = ? AND timestamp = ?");
            $check_stmt->execute([$user_id, $timestamp]);
            $exists = $check_stmt->fetchColumn();
            
            if (!$exists) {
                // Insert data baru ke tbl_kehadiran
                $insert_stmt = $conn->prepare("INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, verification_mode, status) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->execute([$user_id, $user_name, $timestamp, $verification_mode, $status_text]);
                
                // Mapping dengan data siswa
                $siswa_stmt = $conn->prepare("SELECT id_siswa, nama_siswa FROM siswa WHERE nis = ? OR nisn = ?");
                $siswa_stmt->execute([$user_id, $user_id]);
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
                        $siswa_count++;
                        writeLog("Absensi siswa: {$siswa['nama_siswa']} - $tanggal $jam_masuk");
                    }
                } else {
                    // Mapping dengan data guru
                    $guru_stmt = $conn->prepare("SELECT id_guru, nama_guru FROM guru WHERE nip = ?");
                    $guru_stmt->execute([$user_id]);
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
                            $guru_count++;
                            writeLog("Absensi guru: {$guru['nama_guru']} - $tanggal $jam_masuk");
                        }
                    } else {
                        $error_count++;
                        writeLog("WARNING: User ID $user_id ($user_name) tidak dapat dipetakan");
                    }
                }
                $processed_count++;
            }
        }
        
        writeLog("Sinkronisasi selesai. Diproses: $processed_count, Siswa: $siswa_count, Guru: $guru_count, Error: $error_count");
        
    } else {
        writeLog("ERROR: Gagal terhubung ke perangkat fingerprint: $device_ip");
    }
    
} catch (Exception $e) {
    writeLog("ERROR: " . $e->getMessage());
}

writeLog("Sinkronisasi otomatis selesai." . PHP_EOL);
?> 