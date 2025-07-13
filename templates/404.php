<?php
$title = "404 Not Found";
include 'header.php';
// Tentukan dashboard berdasarkan role
$dashboard_url = '/absensi_sekolah/';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $dashboard_url = '/absensi_sekolah/admin/index.php';
    } elseif ($_SESSION['role'] === 'guru') {
        $dashboard_url = '/absensi_sekolah/guru/index.php';
    }
}
?>
<div class="container text-center mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <h1 class="display-1 text-primary font-weight-bold">404</h1>
            <h2 class="mb-4">Halaman tidak ditemukan</h2>
            <p class="lead mb-4">Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan.</p>
            <a href="<?php echo $dashboard_url; ?>" class="btn btn-primary"><i class="fas fa-home"></i> Kembali ke Beranda</a>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?> 