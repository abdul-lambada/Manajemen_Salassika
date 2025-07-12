<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data guru
if (isset($_GET['id'])) {
    $id_guru = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM Guru WHERE id_guru = :id_guru");
        $stmt->bindParam(':id_guru', $id_guru);
        $stmt->execute();

        // Redirect ke halaman list guru dengan status success
        header("Location: list_guru.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list guru dengan status error
        header("Location: list_guru.php?status=error");
        exit();
    }
}
?>