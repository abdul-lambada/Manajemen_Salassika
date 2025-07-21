<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}
$title = "List Kelas";
$active_page = "list_kelas";
include '../../templates/header.php';
include '../../templates/sidebar.php';
// Pagination: retrieve current page and set limit
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
// Ambil data Kelas dengan pagination
include '../../includes/db.php';
$stmt = $conn->prepare("
    SELECT k.*, j.nama_jurusan 
    FROM kelas k 
    JOIN jurusan j ON k.id_jurusan = j.id_jurusan 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$kelas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get total number of rows and compute total pages
$countStmt = $conn->query("SELECT COUNT(*) as total FROM kelas");
$totalRows = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRows / $limit);
// Cek status dari query string
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = '';
switch ($status) {
    case 'add_success':
        $message = 'Data kelas berhasil ditambahkan.';
        $alert_class = 'alert-success';
        break;
    case 'edit_success':
        $message = 'Data kelas berhasil diperbarui.';
        $alert_class = 'alert-warning';
        break;
    case 'delete_success':
        $message = 'Data kelas berhasil dihapus.';
        $alert_class = 'alert-danger';
        break;
    case 'error':
        $message = isset($_GET['message']) ? $_GET['message'] : 'Terjadi kesalahan saat memproses data.';
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
        <?php include '../../templates/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">List Kelas</h1>
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Data Kelas</h6>
                        </div>
                        <div class="card-header py-3">
                            <a href="tambah_kelas.php" class="btn btn-success btn-sm"><i class="fas fa-plus-circle"> Tambah Data</i></a>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kelas</th>
                                        <th>Jurusan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kelas_list as $index => $kelas): ?>
                                        <tr>
                                            <td><?php echo ($page - 1) * $limit + $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                            <td><?php echo htmlspecialchars($kelas['nama_jurusan']); ?></td>
                                            <td>
                                                <a href="edit_kelas.php?id=<?php echo $kelas['id_kelas']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"> Edit</i></a>
                                                <!-- Tombol hapus dengan modal per baris -->
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#hapusModal<?php echo $kelas['id_kelas']; ?>">
                                                    <i class="fas fa-trash"> Hapus</i>
                                                </button>
                                                <!-- Modal Konfirmasi Hapus -->
                                                <div class="modal fade" id="hapusModal<?php echo $kelas['id_kelas']; ?>" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel<?php echo $kelas['id_kelas']; ?>" aria-hidden="true">
                                                  <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                      <div class="modal-header">
                                                        <h5 class="modal-title" id="hapusModalLabel<?php echo $kelas['id_kelas']; ?>">Hapus Data</h5>
                                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                          <span aria-hidden="true">&times;</span>
                                                        </button>
                                                      </div>
                                                      <div class="modal-body">Apakah Kamu Yakin, Akan Menghapus Data Ini.!</div>
                                                      <div class="modal-footer">
                                                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                                                        <a class="btn btn-primary" href="hapus_kelas.php?id=<?php echo $kelas['id_kelas']; ?>">Hapus</a>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- Dynamic Pagination -->
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-end">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Previous</span>
                                        </li>
                                    <?php endif; ?>
                                    <?php for($i=1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php if($page==$i) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Next</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../../templates/footer.php'; ?>
</div>