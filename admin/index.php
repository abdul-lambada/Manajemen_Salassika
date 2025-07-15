<?php
session_start();
include '../includes/db.php';
$title = "Dashboard";
$active_page = "dashboard";
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../templates/header.php';
include '../templates/sidebar.php';
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include '../templates/navbar.php'; ?>
        <div class="container-fluid">
            <?php
            $role = $_SESSION['user']['role'];
            if ($role === 'admin') {
                // Konten dashboard admin
                echo '<h1 class="h3 mb-4 text-gray-800">Dashboard Admin</h1>';
                // ... tampilkan statistik admin ...
            } elseif ($role === 'guru') {
                // Konten dashboard guru
                echo '<h1 class="h3 mb-4 text-gray-800">Dashboard Guru</h1>';
                // ... tampilkan statistik guru ...
            } else {
                echo '<div class="alert alert-danger">Role tidak dikenali atau tidak diizinkan mengakses dashboard.</div>';
            }
            ?>
        </div>
    </div>
    <?php include '../templates/footer.php'; ?>
    <!-- JS SB Admin -->
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
</body>
</html>