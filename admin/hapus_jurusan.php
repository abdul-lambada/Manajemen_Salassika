<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data jurusan
if (isset($_GET['id'])) {
    $id_jurusan = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM Jurusan WHERE id_jurusan = :id_jurusan");
        $stmt->bindParam(':id_jurusan', $id_jurusan);
        $stmt->execute();

        // Redirect ke halaman list jurusan dengan status success
        header("Location: list_jurusan.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list jurusan dengan status error
        header("Location: list_jurusan.php?status=error");
        exit();
    }
}
exit;
?>