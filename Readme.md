# Sistem Absensi Sekolah

<img src="https://www.logotypes101.com/logos/203/272663FA02DE2DAA2BBAE2FC39F14783/php.png" alt="PHP Logo" width="200">

Proyek ini adalah sistem absensi sekolah berbasis web yang dibangun menggunakan PHP, MySQL, dan Bootstrap. Sistem ini memungkinkan sekolah untuk mencatat dan melacak kehadiran siswa secara efisien.

## Fitur

- **Manajemen Siswa**: Tambah, edit, dan hapus data siswa.
- **Manajemen Guru**: Tambah, edit, dan hapus data guru.
- **Manajemen Kelas**: Tambah, edit, dan hapus data kelas.
- **Absensi**: Catat kehadiran siswa setiap hari dengan status seperti Hadir, Tidak Hadir, Sakit, atau Izin.
- **Laporan**: Lihat dan cetak laporan kehadiran siswa berdasarkan tanggal dan kelas.
- **Login Multi-User**: Masuk sebagai admin atau guru dengan hak akses yang berbeda.
- **Riwayat Kehadiran**: Lihat riwayat kehadiran siswa dengan detail waktu masuk dan keluar.

## Persyaratan

- **XAMPP** atau server web dengan PHP (versi 7.4 atau lebih baru) dan MySQL.
- Browser web modern (Google Chrome, Mozilla Firefox, dll.).

## Instalasi

1. **Clone repositori ini** ke direktori `htdocs` XAMPP Anda:
    ```bash
    git clone https://github.com/username/absensi_sekolah.git
    ```

2. **Buat database baru** di MySQL dan impor file `database.sql` yang ada di folder `database`:
    ```sql
    CREATE DATABASE absensi_sekolah;
    USE absensi_sekolah;
    SOURCE path/to/database.sql;
    ```

3. **Konfigurasi koneksi database** di file `includes/db.php`:
    ```php
    // filepath: includes/db.php
    <?php
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'absensi_sekolah';

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
    ?>
    ```

4. **Jalankan XAMPP** dan buka browser, lalu akses `http://localhost/absensi_sekolah`.

## Penggunaan

1. **Login**:
    - **Admin**: Memiliki akses penuh untuk mengelola data siswa, guru, kelas, dan laporan.
    - **Guru**: Memiliki akses untuk mencatat kehadiran siswa dan melihat laporan.
2. **Manajemen Data**:
    - Tambah, edit, atau hapus data siswa, guru, dan kelas melalui antarmuka admin.
3. **Absensi**:
    - Guru dapat mencatat kehadiran siswa setiap hari dengan status seperti Hadir, Tidak Hadir, Sakit, atau Izin.
4. **Laporan**:
    - Admin dan guru dapat melihat laporan kehadiran siswa berdasarkan tanggal dan kelas.
    - Laporan dapat dicetak untuk dokumentasi.

## Struktur Folder

- **`admin/`**: Berisi halaman untuk admin, seperti manajemen data dan laporan.
- **`guru/`**: Berisi halaman untuk guru, seperti absensi siswa dan laporan.
- **`includes/`**: Berisi file konfigurasi database dan fungsi umum.
- **`css/`**: Berisi file CSS untuk antarmuka.
- **`js/`**: Berisi file JavaScript untuk interaktivitas.
- **`database/`**: Berisi file SQL untuk inisialisasi database.

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini, silakan fork repositori ini dan buat pull request dengan perubahan Anda.

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://github.com/username/absensi_sekolah/blob/main/LICENSE).