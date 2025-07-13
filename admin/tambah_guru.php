<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Ambil data dari form
        $nama_guru = $_POST['nama_guru'];
        $nip = $_POST['nip'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];

        // Validasi NIP unik
        $check_nip = $conn->prepare("SELECT id_guru FROM guru WHERE nip = ?");
        $check_nip->execute(array($nip));
        
        if ($check_nip->rowCount() > 0) {
            throw new Exception("NIP sudah digunakan");
        }

        // Insert ke tabel users terlebih dahulu
        $stmt_user = $conn->prepare("INSERT INTO users (name, password, role, uid) VALUES (?, ?, 'guru', ?)");
        $stmt_user->execute(array($nama_guru, $password, $nip));
        $user_id = $conn->lastInsertId();

        // Simpan data ke tabel guru dengan user_id
        $stmt = $conn->prepare("INSERT INTO guru (nama_guru, nip, password, jenis_kelamin, tanggal_lahir, alamat, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array($nama_guru, $nip, $password, $jenis_kelamin, $tanggal_lahir, $alamat, $user_id));

        $conn->commit();
        // Redirect ke halaman list guru dengan status success
        header("Location: list_guru.php?status=add_success");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tambah Guru - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Tambah Guru</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Tambah Guru</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo htmlspecialchars($error_message); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <label>Nama Guru:</label>
                                    <input type="text" name="nama_guru" class="form-control" required><br>
                                    <label>NIP:</label>
                                    <input type="text" name="nip" class="form-control" required><br>
                                    <label>Password:</label>
                                    <input type="password" name="password" class="form-control" required><br>
                                    <label>Jenis Kelamin:</label>
                                    <select name="jenis_kelamin" class="form-control" required>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select><br>
                                    <label>Tanggal Lahir:</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" required><br>
                                    <label>Alamat:</label>
                                    <textarea name="alamat" class="form-control" required></textarea><br>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
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