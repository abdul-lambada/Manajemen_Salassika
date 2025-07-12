<?php
$title = "List Siswa";
$active_page = "list_siswa";
include '../templates/header.php';
include '../templates/sidebar.php';
include '../includes/db.php';

// Konfigurasi pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil total data
$stmt_total = $conn->query("
    SELECT COUNT(*) AS total 
    FROM Siswa s
    JOIN kelas k ON s.id_kelas = k.id_kelas
");
$totalRecords = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Query dengan join ke tabel kelas dan users
$stmt = $conn->prepare("
    SELECT 
        s.id_siswa, 
        s.nis, 
        s.jenis_kelamin, 
        s.tanggal_lahir, 
        s.alamat, 
        k.nama_kelas, 
        u.name AS user_name
    FROM Siswa s
    JOIN kelas k ON s.id_kelas = k.id_kelas
    LEFT JOIN users u ON s.user_id = u.id
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$siswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handling status message
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = '';
$alert_class = '';

switch ($status) {
    case 'add_success':
        $message = 'Data siswa berhasil ditambahkan.';
        $alert_class = 'alert-success';
        break;
    case 'edit_success':
        $message = 'Data siswa berhasil diperbarui.';
        $alert_class = 'alert-warning';
        break;
    case 'delete_success':
        $message = 'Data siswa berhasil dihapus.';
        $alert_class = 'alert-danger';
        break;
    case 'error':
        $message = 'Terjadi kesalahan saat memproses data.';
        $alert_class = 'alert-danger';
        break;
}
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">List Siswa</h1>
        </nav>
        <div class="container-fluid">
            <?php if (!empty($message)): ?>
                <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
                    <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Data Siswa</h6>
                    <div>
                        <a href="tambah_siswa.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus-circle"></i> Tambah Siswa
                        </a>
                        <form method="POST" action="import_siswa.php" enctype="multipart/form-data" class="d-inline">
                            <input type="file" name="excel_file" accept=".xlsx, .xls" required>
                            <button type="submit" name="import_excel" class="btn btn-primary btn-sm">
                                <i class="fas fa-file-import"></i> Import Excel
                            </button>
                        </form>
                        <a href="../assets/format_siswa.xlsx" class="btn btn-info btn-sm" download>
                            <i class="fas fa-download"></i> Unduh Format Excel
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive-sm">
                    <table class="table table-bordered" id="dataTable">
                        <thead>
                            <tr>
                                <th>ID Siswa</th>
                                <th>NIS</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Alamat</th>
                                <th>Kelas</th>
                                <th>User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siswa_list as $siswa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($siswa['id_siswa']) ?></td>
                                    <td><?= htmlspecialchars($siswa['nis']) ?></td>
                                    <td><?= htmlspecialchars($siswa['jenis_kelamin']) ?></td>
                                    <td><?= htmlspecialchars($siswa['tanggal_lahir']) ?></td>
                                    <td><?= htmlspecialchars($siswa['alamat']) ?></td>
                                    <td><?= htmlspecialchars($siswa['nama_kelas']) ?></td>
                                    <td><?= htmlspecialchars($siswa['user_name'] ?? 'Tidak ada user') ?></td>
                                    <td>
                                        <a href="edit_siswa.php?id=<?= $siswa['id_siswa'] ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal<?= $siswa['id_siswa'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal<?= $siswa['id_siswa'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus data ini?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    Batal
                                                </button>
                                                <a href="hapus_siswa.php?id=<?= $siswa['id_siswa'] ?>" 
                                                   class="btn btn-danger">
                                                    Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?>