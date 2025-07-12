<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses hapus data kelas
if (isset($_GET['id'])) {
    $id_kelas = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM Users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Redirect ke halaman list kelas dengan status success
        header("Location: list_users.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        // Redirect ke halaman list kelas dengan status error
        header("Location: list_users.php?status=error");
        exit();
    }
}
exit;
?>