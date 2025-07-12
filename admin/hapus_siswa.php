<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data siswa
if (isset($_GET['id'])) {
    $id_siswa = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM Siswa WHERE id_siswa = :id_siswa");
        $stmt->bindParam(':id_siswa', $id_siswa);
        $stmt->execute();

        // Redirect ke halaman list siswa dengan status success
        header("Location: list_siswa.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list siswa dengan status error
        header("Location: list_siswa.php?status=error");
        exit();
    }
}
?>