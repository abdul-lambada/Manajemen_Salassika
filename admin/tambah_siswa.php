<?php
session_start();
include '../includes/db.php';

// Redirect jika bukan admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil daftar kelas untuk dropdown
$stmt_kelas = $conn->prepare("SELECT id_kelas, nama_kelas FROM kelas");
$stmt_kelas->execute();
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar user untuk dropdown (hanya ambil name dan id)
$stmt_users = $conn->prepare("SELECT id, name FROM users");
$stmt_users->execute();
$users_list = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Ambil data form
        $nis = $_POST['nis'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $id_kelas = $_POST['id_kelas'];
        $user_id = $_POST['user_id'];

        // Validasi NIS unik
        $check_nis = $conn->prepare("SELECT id_siswa FROM siswa WHERE nis = ?");
        $check_nis->execute(array($nis));
        
        if ($check_nis->rowCount() > 0) {
            throw new Exception("NIS sudah digunakan");
        }

        // Insert ke tabel siswa
        $stmt = $conn->prepare("
            INSERT INTO siswa 
            (nis, jenis_kelamin, tanggal_lahir, alamat, id_kelas, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute(array(
            $nis,
            $jenis_kelamin,
            $tanggal_lahir,
            $alamat,
            $id_kelas,
            $user_id
        ));

        $conn->commit();
        header("Location: list_siswa.php?status=add_success");
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
    <title>Tambah Siswa</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Tambah Siswa</h1>
            </nav>
            <div class="container-fluid">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Siswa</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>NIS</label>
                                <input type="text" name="nis" class="form-control" 
                                       value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control" required>
                                    <option value="Laki-laki" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" 
                                       value="<?php echo isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : ''; ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="id_kelas" class="form-control" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                        <option value="<?php echo $kelas['id_kelas']; ?>" 
                                            <?php echo (isset($_POST['id_kelas']) && $_POST['id_kelas'] == $kelas['id_kelas']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>User</label>
                                <select name="user_id" class="form-control" required>
                                    <option value="">Pilih User</option>
                                    <?php if (!empty($users_list)): ?>
                                        <?php foreach ($users_list as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Tidak ada user tersedia</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="list_siswa.php" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../templates/footer.php'; ?>
</body>
</html>