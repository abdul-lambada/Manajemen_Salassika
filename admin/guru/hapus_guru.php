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
        $conn->beginTransaction();
        
        // Ambil user_id dari guru
        $stmt_get = $conn->prepare("SELECT user_id FROM guru WHERE id_guru = ?");
        $stmt_get->execute([$id_guru]);
        $guru = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        if ($guru && $guru['user_id']) {
            // Hapus data dari tabel users
            $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt_user->execute([$guru['user_id']]);
        }
        
        // Hapus data dari tabel guru
        $stmt = $conn->prepare("DELETE FROM guru WHERE id_guru = ?");
        $stmt->execute([$id_guru]);

        $conn->commit();
        // Redirect ke halaman list guru dengan status success
        header("Location: list_guru.php?status=delete_success");
        exit();
    } catch (\PDOException $e) {
        $conn->rollBack();
        // Redirect ke halaman list guru dengan status error
        header("Location: list_guru.php?status=error");
        exit();
    }
}
?>