<?php
session_start();
$title = "Kelola Pengguna Fingerprint";
$active_page = "manage_fingerprint_users";
include '../templates/header.php';
include '../templates/sidebar.php';
include '../includes/db.php';
include '../includes/fingerprint_config.php';

$message = '';
$alert_class = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $user_id = $_POST['user_id'];
                $user_name = $_POST['user_name'];
                $user_type = $_POST['user_type'];
                $device_ip = $_POST['device_ip'];
                
                try {
                    require '../includes/zklib/zklibrary.php';
                    $zk = new ZKLibrary($device_ip, 4370);
                    
                    if ($zk->connect()) {
                        $zk->disableDevice();
                        
                        // Tambah pengguna ke fingerprint
                        $result = $zk->setUser($user_id, $user_id, $user_name, '', 0);
                        
                        $zk->enableDevice();
                        $zk->disconnect();
                        
                        if ($result) {
                            $message = "Pengguna berhasil ditambahkan ke fingerprint: $user_name";
                            $alert_class = 'alert-success';
                        } else {
                            $message = "Gagal menambahkan pengguna ke fingerprint";
                            $alert_class = 'alert-danger';
                        }
                    } else {
                        $message = "Gagal terhubung ke perangkat fingerprint";
                        $alert_class = 'alert-danger';
                    }
                } catch (Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $alert_class = 'alert-danger';
                }
                break;
                
            case 'delete_user':
                $user_id = $_POST['user_id'];
                $device_ip = $_POST['device_ip'];
                
                try {
                    require '../includes/zklib/zklibrary.php';
                    $zk = new ZKLibrary($device_ip, 4370);
                    
                    if ($zk->connect()) {
                        $zk->disableDevice();
                        
                        // Hapus pengguna dari fingerprint
                        $result = $zk->deleteUser($user_id);
                        
                        $zk->enableDevice();
                        $zk->disconnect();
                        
                        if ($result) {
                            $message = "Pengguna berhasil dihapus dari fingerprint";
                            $alert_class = 'alert-success';
                        } else {
                            $message = "Gagal menghapus pengguna dari fingerprint";
                            $alert_class = 'alert-danger';
                        }
                    } else {
                        $message = "Gagal terhubung ke perangkat fingerprint";
                        $alert_class = 'alert-danger';
                    }
                } catch (Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $alert_class = 'alert-danger';
                }
                break;
                
            case 'sync_users':
                $device_ip = $_POST['device_ip'];
                
                try {
                    require '../includes/zklib/zklibrary.php';
                    $zk = new ZKLibrary($device_ip, 4370);
                    
                    if ($zk->connect()) {
                        $zk->disableDevice();
                        
                        // Ambil data pengguna dari fingerprint
                        $fingerprint_users = $zk->getUser();
                        
                        $zk->enableDevice();
                        $zk->disconnect();
                        
                        // Sinkronisasi dengan database
                        $synced_count = 0;
                        foreach ($fingerprint_users as $uid => $user) {
                            $user_id = $user[0];
                            $user_name = $user[1];
                            
                            // Cek apakah sudah ada di database
                            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE uid = ?");
                            $check_stmt->execute([$uid]);
                            
                            if ($check_stmt->fetchColumn() == 0) {
                                // Insert ke database
                                $insert_stmt = $conn->prepare("INSERT INTO users (uid, name, role) VALUES (?, ?, '')");
                                $insert_stmt->execute([$uid, $user_name]);
                                $synced_count++;
                            }
                        }
                        
                        $message = "Sinkronisasi selesai. $synced_count pengguna baru ditambahkan.";
                        $alert_class = 'alert-success';
                    } else {
                        $message = "Gagal terhubung ke perangkat fingerprint";
                        $alert_class = 'alert-danger';
                    }
                } catch (Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $alert_class = 'alert-danger';
                }
                break;
        }
    }
}

// Ambil data pengguna dari database
$stmt = $conn->query("SELECT * FROM users ORDER BY name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data siswa dan guru untuk dropdown
$stmt_siswa = $conn->query("SELECT id_siswa, nama_siswa, nis, nisn FROM siswa ORDER BY nama_siswa");
$siswa_list = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

$stmt_guru = $conn->query("SELECT id_guru, nama_guru, nip FROM guru ORDER BY nama_guru");
$guru_list = $stmt_guru->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <h1 class="h3 mb-0 text-gray-800">Kelola Pengguna Fingerprint</h1>
        </nav>
        
        <div class="container-fluid">
            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Form Tambah Pengguna -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tambah Pengguna Fingerprint</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="add_user">
                                
                                <div class="form-group">
                                    <label for="device_ip">IP Address Perangkat:</label>
                                    <input type="text" class="form-control" id="device_ip" name="device_ip" 
                                           value="<?php echo FINGERPRINT_IP; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="user_type">Tipe Pengguna:</label>
                                    <select class="form-control" id="user_type" name="user_type" required>
                                        <option value="">Pilih Tipe</option>
                                        <option value="siswa">Siswa</option>
                                        <option value="guru">Guru</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="siswa_select" style="display: none;">
                                    <label for="siswa_id">Pilih Siswa:</label>
                                    <select class="form-control" id="siswa_id" name="siswa_id">
                                        <option value="">Pilih Siswa</option>
                                        <?php foreach ($siswa_list as $siswa): ?>
                                            <option value="<?php echo $siswa['id_siswa']; ?>" 
                                                    data-nis="<?php echo $siswa['nis']; ?>" 
                                                    data-nisn="<?php echo $siswa['nisn']; ?>"
                                                    data-nama="<?php echo $siswa['nama_siswa']; ?>">
                                                <?php echo $siswa['nama_siswa']; ?> (NIS: <?php echo $siswa['nis']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="guru_select" style="display: none;">
                                    <label for="guru_id">Pilih Guru:</label>
                                    <select class="form-control" id="guru_id" name="guru_id">
                                        <option value="">Pilih Guru</option>
                                        <?php foreach ($guru_list as $guru): ?>
                                            <option value="<?php echo $guru['id_guru']; ?>" 
                                                    data-nip="<?php echo $guru['nip']; ?>"
                                                    data-nama="<?php echo $guru['nama_guru']; ?>">
                                                <?php echo $guru['nama_guru']; ?> (NIP: <?php echo $guru['nip']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <input type="hidden" id="user_id" name="user_id">
                                <input type="hidden" id="user_name" name="user_name">
                                
                                <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sinkronisasi Pengguna -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sinkronisasi Pengguna</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="sync_users">
                                
                                <div class="form-group">
                                    <label for="sync_device_ip">IP Address Perangkat:</label>
                                    <input type="text" class="form-control" id="sync_device_ip" name="device_ip" 
                                           value="<?php echo FINGERPRINT_IP; ?>" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">Sinkronisasi Pengguna</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Pengguna -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna Fingerprint</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>UID</th>
                                            <th>Nama</th>
                                            <th>Role</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['uid']); ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['uid']; ?>">
                                                    <input type="hidden" name="device_ip" value="<?php echo FINGERPRINT_IP; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('user_type').addEventListener('change', function() {
    const userType = this.value;
    const siswaSelect = document.getElementById('siswa_select');
    const guruSelect = document.getElementById('guru_select');
    
    if (userType === 'siswa') {
        siswaSelect.style.display = 'block';
        guruSelect.style.display = 'none';
    } else if (userType === 'guru') {
        siswaSelect.style.display = 'none';
        guruSelect.style.display = 'block';
    } else {
        siswaSelect.style.display = 'none';
        guruSelect.style.display = 'none';
    }
});

document.getElementById('siswa_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const nis = selectedOption.getAttribute('data-nis');
        const nama = selectedOption.getAttribute('data-nama');
        document.getElementById('user_id').value = nis;
        document.getElementById('user_name').value = nama;
    }
});

document.getElementById('guru_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const nip = selectedOption.getAttribute('data-nip');
        const nama = selectedOption.getAttribute('data-nama');
        document.getElementById('user_id').value = nip;
        document.getElementById('user_name').value = nama;
    }
});
</script>

<?php include '../templates/footer.php'; ?> 