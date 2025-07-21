<?php
session_start();
include '../../includes/db.php';
include '../../templates/header.php';
include '../../templates/sidebar.php';
?>
<?php include '../../templates/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Hapus Kehadiran</h4>
                </div>
                <div class="card-body">
                    <p>Apakah Anda yakin ingin menghapus data kehadiran ini?</p>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                        <a href="attendance_records.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>