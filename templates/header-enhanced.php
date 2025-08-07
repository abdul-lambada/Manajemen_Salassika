<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Absensi Sekolah - Manajemen kehadiran siswa dan guru">
    <meta name="author" content="Sistem Absensi Sekolah">
    <meta name="theme-color" content="#4e73df">
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Absensi Sekolah">
    
    <title><?php echo isset($title) ? $title . " - " : ""; ?>Sistem Absensi Sekolah</title>
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="/absensi_sekolah/assets/css/sb-admin-2.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- CSS -->
    <link href="/absensi_sekolah/assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="/absensi_sekolah/assets/css/mobile-enhancements.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css" rel="stylesheet">
    
    <!-- Custom Favicon -->
    <link rel="icon" type="image/x-icon" href="/absensi_sekolah/assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="/absensi_sekolah/assets/img/apple-touch-icon.png">
    
    <!-- Security Headers -->
    <?php
    // Set security headers
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    ?>
</head>

<body id="page-top" class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    
    <!-- Skip to main content for accessibility -->
    <a class="sr-only sr-only-focusable" href="#main-content">Skip to main content</a>
    
    <!-- Page Wrapper -->
    <div id="wrapper">
