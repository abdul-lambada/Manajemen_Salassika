<?php
function sendWaNotification($to, $message) {
    $api_key = 'GhTonGUNyvWHHKycxChiJ9P2SMEx'; // Ganti dengan API Key Fonnte Anda
    $url = "https://api.fonnte.com/send";
    $data = [
        'target' => $to, // Nomor tujuan, format 628xxxx
        'message' => $message,
        'countryCode' => '62', // Optional, default 62
    ];
    $headers = [
        "Authorization: $api_key"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
} 