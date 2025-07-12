<?php
$title = "List Guru";
$active_page = "list_guru"; // Untuk menandai menu aktif di sidebar
include '../templates/header.php';
include '../templates/sidebar.php';

// Koneksi database
include '../includes/db.php';


// Konfigurasi pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil total jumlah data guru
$stmt_total = $conn->query("SELECT COUNT(*) AS total FROM Guru");
$totalRecords = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

// Hitung total halaman
$totalPages = ceil($totalRecords / $limit);

// Ambil data guru dengan limit dan offset
$stmt = $conn->prepare("SELECT * FROM Guru LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$guru_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek status dari query string
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = '';

switch ($status) {
    case 'add_success':
        $message = 'Data guru berhasil ditambahkan.';
        $alert_class = 'alert-success';
        break;
    case 'edit_success':
        $message = 'Data guru berhasil diperbarui.';
        $alert_class = 'alert-warning';
        break;
    case 'delete_success':
        $message = 'Data guru berhasil dihapus.';
        $alert_class = 'alert-danger';
        break;
    case 'error':
        $message = 'Terjadi kesalahan saat memproses data.';
        $alert_class = 'alert-danger';
        break;
    default:
        $message = '';
        $alert_class = '';
        break;
}
?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">List Guru</h1>
        </nav>
        <div class="container-fluid">
            <!-- Begin Alert SB Admin 2 -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <!-- End Alert SB Admin 2 -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Data Guru</h6>
                        </div>
                        <div class="card-header py-3">
                            <a href="tambah_guru.php" class="btn btn-success btn-sm"><i class="fas fa-plus-circle"></i> Tambah Guru</a>
                            <!-- Form Upload Excel -->
                            <form method="POST" action="" enctype="multipart/form-data" style="display:inline;">
                                <input type="file" name="excel_file" accept=".xlsx, .xls" required>
                                <button type="submit" name="import_excel" class="btn btn-primary btn-sm"><i class="fas fa-file-import"></i> Import Excel</button>
                            </form>
                            <!-- Tombol Unduh Format Excel -->
                            <a href="../assets/format_data_guru.xlsx" class="btn btn-info btn-sm" download><i class="fas fa-download"></i> Unduh Format Excel</a>
                        </div>
                        <div class="card-body table-responsive-sm">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Guru</th>
                                        <th>NIP</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Alamat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($guru_list)): ?>
                                        <?php foreach ($guru_list as $index => $guru): ?>
                                            <tr>
                                                <td><?php echo ($page - 1) * $limit + $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($guru['nama_guru']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['jenis_kelamin']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['tanggal_lahir']); ?></td>
                                                <td><?php echo htmlspecialchars($guru['alamat']); ?></td>
                                                <td>
                                                    <a href="edit_guru.php?id=<?php echo htmlspecialchars($guru['id_guru']); ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#logoutModal"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data guru.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <!-- Dynamic pagination -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end">
                                    <!-- Tombol Previous -->
                                    <li class="page-item <?php echo ($page == 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                    </li>

                                    <!-- Nomor Halaman -->
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Tombol Next -->
                                    <li class="page-item <?php echo ($page == $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                            <!-- End Dynamic pagination -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Hapus Data</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Apakah Kamu Yakin, Akan Menghapus Data Ini.!</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-primary" href="hapus_guru.php?id=<?php echo htmlspecialchars($guru['id_guru']); ?>">Hapus</a>
            </div>
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>