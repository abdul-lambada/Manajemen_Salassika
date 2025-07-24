<?php
include_once '../../includes/email_util.php';

// ... (other code)

foreach ($devices as $device) {
    $ip = $device['ip'];
    $port = $device['port'];
    echo "<b>Sinkronisasi device: {$device['nama_lokasi']} ({$ip}:{$port})</b><br>";
    $zk = new ZKLibrary($ip, $port);
    if ($zk->connect()) {
        $zk->disableDevice();
        // ... sinkronisasi ...
        $zk->enableDevice();
        $zk->disconnect();
        echo "<span style='color:green'>Sukses sinkronisasi device $ip</span><br>";
    } else {
        echo "<span style='color:red'>Gagal koneksi ke device $ip</span><br>";
        sendDeviceOfflineNotification($device, 'Gagal koneksi ke device');
    }
}

// ... (other code) 