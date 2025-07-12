<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi parameter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list_siswa.php?status=error");
    exit;
}

$id_siswa = $_GET['id'];

// Ambil data siswa dengan join ke kelas dan users
$stmt = $conn->prepare("
    SELECT 
        s.*, 
        k.nama_kelas, 
        u.name AS user_name 
    FROM Siswa s
    JOIN kelas k ON s.id_kelas = k.id_kelas
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.id_siswa = :id_siswa
");
$stmt->bindParam(':id_siswa', $id_siswa);
$stmt->execute();
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    header("Location: list_siswa.php?status=error");
    exit;
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        $nis = $_POST['nis'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $id_kelas = $_POST['id_kelas'];
        $user_id = $_POST['user_id'];

        // Validasi NIS unik
        $check_nis = $conn->prepare("SELECT id_siswa FROM Siswa WHERE nis = ? AND id_siswa != ?");
        $check_nis->execute([$nis, $id_siswa]);
        
        if ($check_nis->rowCount() > 0) {
            throw new Exception("NIS sudah digunakan oleh siswa lain");
        }

        // Update data siswa
        $stmt = $conn->prepare("
            UPDATE Siswa SET 
                nis = :nis, 
                jenis_kelamin = :jenis_kelamin, 
                tanggal_lahir = :tanggal_lahir, 
                alamat = :alamat, 
                id_kelas = :id_kelas, 
                user_id = :user_id 
            WHERE id_siswa = :id_siswa
        ");
        
        $stmt->execute([
            ':nis' => $nis,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tanggal_lahir' => $tanggal_lahir,
            ':alamat' => $alamat,
            ':id_kelas' => $id_kelas,
            ':user_id' => $user_id,
            ':id_siswa' => $id_siswa
        ]);

        $conn->commit();
        header("Location: list_siswa.php?status=edit_success");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = $e->getMessage();
    }
}

// Ambil daftar kelas untuk dropdown
$stmt_kelas = $conn->query("SELECT id_kelas, nama_kelas FROM kelas");
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar user untuk dropdown (SEMUA USER TANPA FILTER ROLE)
$stmt_users = $conn->query("SELECT id, name FROM users");
$users_list = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Siswa - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Edit Siswa</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Edit Siswa</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($error_message) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>NIS</label>
                                        <input type="text" name="nis" class="form-control" 
                                               value="<?= htmlspecialchars($siswa['nis']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-control" required>
                                            <option value="Laki-laki" <?= ($siswa['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                                            <option value="Perempuan" <?= ($siswa['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control" 
                                               value="<?= htmlspecialchars($siswa['tanggal_lahir']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea name="alamat" class="form-control" required><?= htmlspecialchars($siswa['alamat']) ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Kelas</label>
                                        <select name="id_kelas" class="form-control" required>
                                            <?php foreach ($kelas_list as $kelas): ?>
                                                <option value="<?= $kelas['id_kelas'] ?>" 
                                                    <?= ($siswa['id_kelas'] == $kelas['id_kelas']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>User</label>
                                        <select name="user_id" class="form-control" required>
                                            <?php if (!empty($users_list)): ?>
                                                <?php foreach ($users_list as $user): ?>
                                                    <option value="<?= $user['id'] ?>" 
                                                        <?= ($siswa['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option value="">Tidak ada user tersedia</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>