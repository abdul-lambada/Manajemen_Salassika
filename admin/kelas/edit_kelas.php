<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_kelas = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM Kelas WHERE id_kelas = :id_kelas");
$stmt->bindParam(':id_kelas', $id_kelas);
$stmt->execute();
$kelas = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = $_POST['nama_kelas'];
    $id_jurusan = $_POST['id_jurusan'];

    if (!empty($nama_kelas) && !empty($id_jurusan)) {
        try {
            $stmt = $conn->prepare("UPDATE Kelas SET nama_kelas = :nama_kelas, id_jurusan = :id_jurusan WHERE id_kelas = :id_kelas");
            $stmt->bindParam(':nama_kelas', $nama_kelas);
            $stmt->bindParam(':id_jurusan', $id_jurusan);
            $stmt->bindParam(':id_kelas', $id_kelas);
            $stmt->execute();

            // Redirect ke halaman list kelas dengan status success
            header("Location: list_kelas.php?status=edit_success");
            exit();
        } catch (\PDOException $e) {
            // Redirect ke halaman list kelas dengan status error
            header("Location: list_kelas.php?status=error");
            exit();
        }
    } else {
        echo "<script>alert('Nama kelas dan jurusan tidak boleh kosong.');</script>";
    }
    exit;
}

// Ambil daftar kelas untuk dropdown
$stmt_jurusan = $conn->query("SELECT * FROM Jurusan");
$jurusan_list = $stmt_jurusan->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Kelas - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Edit Kelas</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Edit Kelas</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <label>Nama Kelas:</label>
                                    <input type="text" name="nama_kelas" class="form-control" value="<?php echo $kelas['nama_kelas']; ?>" required><br>
                                    <label>Jurusan:</label>
                                    <select name="id_jurusan" class="form-control">
                                        <?php foreach ($jurusan_list as $jurusan): ?>
                                            <option value="<?php echo $jurusan['id_jurusan']; ?>" <?php echo ($kelas['id_jurusan'] == $jurusan['id_jurusan']) ? 'selected' : ''; ?>>
                                                <?php echo $jurusan['nama_jurusan']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select><br>
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