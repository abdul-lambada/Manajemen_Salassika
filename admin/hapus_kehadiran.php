<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data kelas
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Corrected variable name

    try {
        $stmt = $conn->prepare("DELETE FROM tbl_kehadiran WHERE id = :id");
        $stmt->bindParam(':id', $id); // Corrected variable name
        $stmt->execute();

        // Redirect ke halaman list kelas dengan status success
        header("Location: attendance_records.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list kelas dengan status error
        header("Location: attendance_records.php?status=error");
        exit();
    }
}
exit;
?>