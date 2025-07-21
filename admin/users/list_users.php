<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}
$title = "List Users";
$active_page = "list_users"; // Untuk menandai menu aktif di sidebar
include '../../templates/header.php';
include '../../templates/sidebar.php';

// Koneksi ke database
include '../../includes/db.php';

// Pagination: retrieve current page and set limit
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Ambil data dari tabel users untuk ditampilkan
try {
    $stmt = $conn->query("SELECT SQL_CALC_FOUND_ROWS * FROM users LIMIT $limit OFFSET $offset");
    $dbUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of rows and compute total pages
    $total = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();
    $totalPages = ceil($total / $limit);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Cek status dari query string
$status = isset($_GET['status']) ? $_GET['status'] : '';
$message = '';
switch ($status) {
    case 'add_success':
        $message = 'Data user berhasil ditambahkan.';
        $alert_class = 'alert-success';
        break;
    case 'edit_success':
        $message = 'Data user berhasil diperbarui.';
        $alert_class = 'alert-warning';
        break;
    case 'delete_success':
        $message = 'Data user berhasil dihapus.';
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
        <?php include '../../templates/navbar.php'; ?>
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
                            <h6 class="m-0 font-weight-bold text-primary">Data Users</h6>
                        </div>
                        <div class="card-body table-responsive-sm">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>UID</th>
                                        <th>Created At</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = $offset + 1;
                                    foreach ($dbUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($no++); ?></td>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td><?php echo htmlspecialchars($user['uid']); ?></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        
                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Apakah Anda yakin ingin menghapus user ini?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                            Batal
                                                        </button>
                                                        <a href="hapus_users.php?id=<?php echo $user['id']; ?>" 
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
                            <!-- Dynamic Pagination -->
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-end">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Previous</span>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
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
</div>

<!-- Modal Konfirmasi Hapus -->
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
                <a class="btn btn-primary" href="hapus_users.php?id=<?php echo $users['id']; ?>">Hapus</a>
            </div>
        </div>
    </div>
    <?php include '../../templates/footer.php'; ?>
</div>

<script>
    // Handle modal delete button dynamically
    $('#logoutModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var userId = button.data('id'); // Extract info from data-* attributes
        var modal = $(this);
        modal.find('.delete-user-btn').attr('href', 'hapus_user.php?id=' + userId);
    });
</script>
