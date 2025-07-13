<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_guru = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM guru WHERE id_guru = :id_guru");
$stmt->bindParam(':id_guru', $id_guru);
$stmt->execute();
$guru = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Ambil data dari form
        $nama_guru = $_POST['nama_guru'];
        $nip = $_POST['nip'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $guru['password'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];

        // Validasi NIP unik
        $check_nip = $conn->prepare("SELECT id_guru FROM guru WHERE nip = ? AND id_guru != ?");
        $check_nip->execute([$nip, $id_guru]);
        
        if ($check_nip->rowCount() > 0) {
            throw new Exception("NIP sudah digunakan oleh guru lain");
        }

        // Update data di tabel guru
        $stmt = $conn->prepare("UPDATE guru SET nama_guru = ?, nip = ?, password = ?, jenis_kelamin = ?, tanggal_lahir = ?, alamat = ? WHERE id_guru = ?");
        $stmt->execute([$nama_guru, $nip, $password, $jenis_kelamin, $tanggal_lahir, $alamat, $id_guru]);

        // Update data di tabel users jika ada user_id
        if (!empty($guru['user_id'])) {
            $stmt_user = $conn->prepare("UPDATE users SET name = ?, password = ?, uid = ? WHERE id = ?");
            $stmt_user->execute([$nama_guru, $password, $nip, $guru['user_id']]);
        }

        $conn->commit();
        header("Location: list_guru.php?status=edit_success");
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
    <title>Edit Guru - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Edit Guru</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Edit Guru</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($error_message)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo htmlspecialchars($error_message); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <label>Nama Guru:</label>
                                    <input type="text" name="nama_guru" class="form-control" value="<?php echo $guru['nama_guru']; ?>" required><br>
                                    <label>NIP:</label>
                                    <input type="text" name="nip" class="form-control" value="<?php echo $guru['nip']; ?>" required><br>
                                    <label>Password (kosongkan jika tidak ingin diubah):</label>
                                    <input type="password" name="password" class="form-control"><br>
                                    <label>Jenis Kelamin:</label>
                                    <select name="jenis_kelamin" class="form-control" required>
                                        <option value="Laki-laki" <?php echo ($guru['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="Perempuan" <?php echo ($guru['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select><br>
                                    <label>Tanggal Lahir:</label>
                                    <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo $guru['tanggal_lahir']; ?>" required><br>
                                    <label>Alamat:</label>
                                    <textarea name="alamat" class="form-control" required><?php echo $guru['alamat']; ?></textarea><br>
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