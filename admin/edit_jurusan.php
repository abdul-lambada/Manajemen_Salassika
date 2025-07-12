<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_jurusan = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM Jurusan WHERE id_jurusan = :id_jurusan");
$stmt->bindParam(':id_jurusan', $id_jurusan);
$stmt->execute();
$jurusan = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama_jurusan = $_POST['nama_jurusan'];

    // Update data di database
    if (!empty($nama_jurusan)) {
        try {
            $stmt = $conn->prepare("UPDATE Jurusan SET nama_jurusan = :nama_jurusan WHERE id_jurusan = :id_jurusan");
            $stmt->bindParam(':nama_jurusan', $nama_jurusan);
            $stmt->bindParam(':id_jurusan', $id_jurusan);
            $stmt->execute();

            // Redirect ke halaman list jurusan dengan status success
            header("Location: list_jurusan.php?status=edit_success");
            exit();
        } catch (\PDOException $e) {
            // Redirect ke halaman list jurusan dengan status error
            header("Location: list_jurusan.php?status=error");
            exit();
        }
    } else {
        echo "<script>alert('Nama jurusan tidak boleh kosong.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Jurusan - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>

<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Edit Jurusan</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Edit Jurusan</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <label>Nama Jurusan:</label>
                                    <input type="text" name="nama_jurusan" class="form-control" value="<?php echo $jurusan['nama_jurusan']; ?>" required><br>
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