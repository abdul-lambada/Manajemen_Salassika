<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil daftar kelas untuk dropdown
$stmt_jurusan = $conn->query("SELECT * FROM Jurusan");
$jurusan_list = $stmt_jurusan->fetchAll(PDO::FETCH_ASSOC);

// Proses tambah data kelas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $id_jurusan = trim($_POST['id_jurusan']);

    if (!empty($nama_kelas) && !empty($id_jurusan)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Kelas (nama_kelas, id_jurusan) VALUES (:nama_kelas, :id_jurusan)");
            $stmt->bindParam(':nama_kelas', $nama_kelas);
            $stmt->bindParam(':id_jurusan', $id_jurusan);
            $stmt->execute();

            // Redirect ke halaman list kelas dengan status success
            header("Location: list_kelas.php?status=add_success");
            exit();
        } catch (\PDOException $e) {
            // Redirect ke halaman list kelas dengan status error
            header("Location: list_kelas.php?status=error");
            exit();
        }
    } else {
        echo "<script>alert('Nama kelas dan jurusan tidak boleh kosong.');</script>";
    }
}

// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     // Ambil data dari form
//     $nama_kelas = $_POST['nama_kelas'];
//     $id_jurusan = $_POST['id_jurusan'];

//     // Simpan data ke database
//     $stmt = $conn->prepare("INSERT INTO Kelas (nama_kelas, id_jurusan) VALUES (:nama_kelas, :id_jurusan)");
//     $stmt->bindParam(':nama_kelas', $nama_kelas);
//     $stmt->bindParam(':id_jurusan', $id_jurusan);

//     if ($stmt->execute()) {
//         header("Location: list_kelas.php");
//         exit;
//     } else {
//         echo "Gagal menambahkan data kelas.";
//     }
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tambah Kelas - Management Salassika</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
</head>
<body id="page-top">
    <?php include '../templates/header.php'; ?>
    <?php include '../templates/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h3 mb-0 text-gray-800">Tambah Kelas</h1>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Form Tambah Kelas</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <label>Nama Kelas:</label>
                                    <input type="text" name="nama_kelas" class="form-control" required><br>
                                    <label>Jurusan:</label>
                                    <select name="id_jurusan" class="form-control" required>
                                        <?php foreach ($jurusan_list as $jurusan): ?>
                                            <option value="<?php echo $jurusan['id_jurusan']; ?>">
                                                <?php echo $jurusan['nama_jurusan']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select><br>
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