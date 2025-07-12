<?php
require_once '../vendor/fpdf/fpdf.php';
include '../includes/db.php';

// Ambil ID pengaduan dari query string
$id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query data pengaduan
$stmt = $conn->prepare("SELECT * FROM Pengaduan WHERE id_pengaduan = :id_pengaduan");
$stmt->bindParam(':id_pengaduan', $id_pengaduan);
$stmt->execute();
$pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengaduan) {
    die("Data pengaduan tidak ditemukan!");
}

// Buat PDF
class PDF extends FPDF {
    // Header kop surat
    function Header() {
        // Logo sekolah (ganti path sesuai lokasi logo)
        $this->Image('../assets/logo.jpg', 15, 4, 30); // Ukuran logo: 30px lebar

        // Judul Kop Surat
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'YAYASAN PENDIDIKAN SEKOLAH', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 5, 'Alamat: Jl. Contoh No. 123, Kota Contoh', 0, 1, 'C');
        $this->Cell(0, 5, 'Telepon: (021) 123-456 | Email: info@sekolah.com', 0, 1, 'C');

        // Garis pemisah
        $this->SetLineWidth(0.5);
        $this->Line(10, 35, 200, 35);
        $this->Ln(15); // Jarak antara header dan konten
    }

    // Footer titimangsa
    function Footer() {
        // Posisi 1.5 cm dari bawah
        $this->SetY(-25);

        // Tanda tangan/titimangsa
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Jakarta, ' . date('d F Y'), 0, 1, 'R'); // Tanggal saat ini
        $this->Cell(0, 10, 'Hormat Kami,', 0, 1, 'R');
        $this->Ln(15); // Ruang kosong untuk tanda tangan
        $this->Cell(0, 10, '(Nama Penandatangan)', 0, 1, 'R');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'LAPORAN PENGADUAN', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);

// Lebar kolom tetap untuk label
$labelWidth = 50;
$contentWidth = 140;

// Nama Pelapor
$pdf->Cell($labelWidth, 10, 'Nama Pelapor:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['nama_pelapor']), 0, 1);

// Nomor WA
$pdf->Cell($labelWidth, 10, 'Nomor WA:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['no_wa']), 0, 1);

// Email Pelapor
$pdf->Cell($labelWidth, 10, 'Email Pelapor:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['email_pelapor']), 0, 1);

// Peran Pelapor
$pdf->Cell($labelWidth, 10, 'Peran Pelapor:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['role_pelapor']), 0, 1);

// Kategori
$pdf->Cell($labelWidth, 10, 'Kategori:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['kategori']), 0, 1);

// Judul Pengaduan
$pdf->Cell($labelWidth, 10, 'Judul Pengaduan:', 0, 0);
$pdf->MultiCell($contentWidth, 10, htmlspecialchars($pengaduan['judul_pengaduan']));

// Pesan
$pdf->Cell($labelWidth, 10, 'Pesan:', 0, 0);
$pdf->MultiCell($contentWidth, 10, htmlspecialchars($pengaduan['isi_pengaduan']));

// Keterangan
$pdf->Cell($labelWidth, 10, 'Keterangan:', 0, 0);
$pdf->MultiCell($contentWidth, 10, htmlspecialchars($pengaduan['keterangan']));

// File Pendukung
$pdf->Cell($labelWidth, 10, 'File Pendukung:', 0, 0);
if (!empty($pengaduan['file_pendukung'])) {
    $filePath = '../uploads/' . $pengaduan['file_pendukung'];
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

    // Daftar ekstensi gambar yang didukung
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($fileExtension), $imageExtensions)) {
        // Jika file adalah gambar, tampilkan di PDF
        $pdf->Ln(10); // Jarak antara teks dan gambar
        $pdf->Image($filePath, $pdf->GetX(), $pdf->GetY(), 80); // Gambar dengan lebar 80px
        $pdf->Ln(15); // Jarak setelah gambar
    } else {
        // Jika bukan gambar, tampilkan nama file
        $pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['file_pendukung']), 0, 1);
    }
} else {
    $pdf->Cell($contentWidth, 10, 'Tidak ada file', 0, 1);
}

// Status
$pdf->Cell($labelWidth, 10, 'Status:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['status']), 0, 1);

// Tanggal Pengaduan
$pdf->Cell($labelWidth, 10, 'Tanggal Pengaduan:', 0, 0);
$pdf->Cell($contentWidth, 10, htmlspecialchars($pengaduan['tanggal_pengaduan']), 0, 1);

// Output PDF
$pdf->Output('I', 'laporan_pengaduan_' . $pengaduan['id_pengaduan'] . '.pdf');
?>