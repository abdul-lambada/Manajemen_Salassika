<?php
session_start();
include '../includes/db.php';

// Pastikan hanya admin yang dapat mengakses halaman ini
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data pengaduan
if (isset($_GET['id'])) {
    $id_pengaduan = $_GET['id'];

    try {
        // Query untuk menghapus data pengaduan berdasarkan ID
        $stmt = $conn->prepare("DELETE FROM Pengaduan WHERE id_pengaduan = :id_pengaduan");
        $stmt->bindParam(':id_pengaduan', $id_pengaduan);
        $stmt->execute();

        // Redirect ke halaman list pengaduan dengan status delete_success
        header("Location: list_pengaduan.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list pengaduan dengan status error jika terjadi kesalahan
        header("Location: list_pengaduan.php?status=error");
        exit();
    }
}

exit;
?>