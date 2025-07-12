-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 05 Mar 2025 pada 10.07
-- Versi Server: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_sekolah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_guru`
--

CREATE TABLE `absensi_guru` (
  `id_absensi_guru` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('Hadir','Alpa','Sakit','Ijin') NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_siswa`
--

CREATE TABLE `absensi_siswa` (
  `id_absensi_siswa` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('Hadir','Alpa','Sakit','Ijin') NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(11) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru') DEFAULT 'guru'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id_guru`, `nama_guru`, `nip`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `password`, `role`) VALUES
(1, 'Guru_A', '12345678', 'Laki-laki', '2001-09-13', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'admin'),
(2, 'Guru_B', '12345678', 'Laki-laki', '2001-09-14', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'admin'),
(3, 'Guru_C', '12345679', 'Laki-laki', '2001-09-15', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'guru'),
(4, 'Guru_D', '12345680', 'Laki-laki', '2001-09-16', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'guru'),
(5, 'Guru_E', '12345681', 'Laki-laki', '2001-09-17', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'guru'),
(6, 'Guru_F', '12345682', 'Laki-laki', '2001-09-18', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'guru'),
(7, 'Guru_G', '12345683', 'Laki-laki', '2001-09-19', 'mjl', '$2y$10$x7xWgiXMLwF/Rq4oH6Lpz.Jt1jzM5a.q8jUamJ.K4qbyXkgtdoELi', 'guru'),
(8, 'Jokiawan', '123', 'Laki-laki', '2025-02-28', '-', '$2y$10$AbD5jXSqLU7pfXMUWSv6q.4E2RTIObaDr77WLt7AnKNBuxsTgZVdO', 'guru');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int(11) NOT NULL,
  `nama_jurusan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `jurusan`
--

INSERT INTO `jurusan` (`id_jurusan`, `nama_jurusan`) VALUES
(1, 'Teknik Komputer dan Jaringan'),
(2, 'Teknik Kendaraan Ringan dan Otomotif'),
(3, 'Akuntansi Keuangan dan Lembaga');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `id_jurusan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `id_jurusan`) VALUES
(1, 'X - TKJ 2', 1),
(3, 'X - TKJ 1', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_absensi`
--

CREATE TABLE `laporan_absensi` (
  `id_laporan` int(11) NOT NULL,
  `id_absensi_guru` int(11) DEFAULT NULL,
  `id_absensi_siswa` int(11) DEFAULT NULL,
  `periode` enum('Harian','Mingguan','Bulanan') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `jumlah_hadir` int(11) NOT NULL,
  `jumlah_tidak_hadir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `nama_pelapor` varchar(255) NOT NULL,
  `no_wa` varchar(15) DEFAULT NULL,
  `email_pelapor` varchar(255) DEFAULT NULL,
  `role_pelapor` enum('siswa','guru','umum') NOT NULL,
  `kategori` enum('saran','kritik','pembelajaran','organisasi','administrasi','lainnya') NOT NULL,
  `judul_pengaduan` varchar(255) NOT NULL,
  `isi_pengaduan` text NOT NULL,
  `keterangan` text,
  `file_pendukung` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai') DEFAULT 'pending',
  `tanggal_pengaduan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `nama_pelapor`, `no_wa`, `email_pelapor`, `role_pelapor`, `kategori`, `judul_pengaduan`, `isi_pengaduan`, `keterangan`, `file_pendukung`, `status`, `tanggal_pengaduan`) VALUES
(2, 'contoh', '8952365', 'lapor@gmail.com', 'siswa', 'saran', 'saran contoh', 'contoh', 'hyvt', 'gbanner.jpg', 'pending', '2025-02-24 04:18:21'),
(3, 'contoh', '8952365', 'lapor@gmail.com', 'siswa', 'saran', 'saran contoh', 'contoh', 'hyvt', 'gbanner.jpg', 'pending', '2025-02-24 04:18:59'),
(4, 'contoh', '8952365', 'lapor@gmail.com', 'siswa', 'saran', 'saran contoh', 'contoh', 'hyvt', 'gbanner.jpg', 'pending', '2025-02-24 04:19:34'),
(5, 'contoh', '8952365', 'lapor@gmail.com', 'siswa', 'saran', 'saran contoh', 'nhbg', ',juhy', 'gbanner.jpg', 'diproses', '2025-02-24 04:19:59'),
(6, 'contoh', '8952365', 'lapor@gmail.com', 'siswa', 'saran', 'saran contoh', 'nhbg', ',juhy', 'gbanner.jpg', 'selesai', '2025-02-24 04:20:25'),
(9, 'abdul kholik', '085146522', 'abdul@gmail.com', 'guru', 'pembelajaran', 'judul pengaduan', 'pesan saja', 'keterangan saja', 'Hasil Pengjuan RPL.png', 'diproses', '2025-02-24 06:15:07'),
(10, 'contoh lagi', '8911456223', 'cbsgvsatft@gamil', 'umum', 'organisasi', 'fvgr', 'ewwqs', 'sdfrt', 'logo apple.png', 'pending', '2025-02-24 06:35:27'),
(11, 'contoh lagi', '8911456223', 'cbsgvsatft@gamil', 'umum', 'organisasi', 'fvgr', 'ewwqs', 'sdfrt', 'logo apple.png', 'pending', '2025-02-24 06:35:33'),
(12, 'abdul kholik', '856244789', 'lapor@gmail.com', 'umum', 'organisasi', 'saran contoh', '-', 'hgytrdeer', 'icons8-apple-logo-70.png', 'pending', '2025-02-25 01:53:28'),
(13, 'abdul kholik', '856244789', 'lapor@gmail.com', 'umum', 'organisasi', 'saran contoh', '-', 'hgytrdeer', 'icons8-apple-logo-70.png', 'selesai', '2025-02-25 01:53:44'),
(14, 'contoh laporan', '784524566', 'lapor@gmail.com', 'guru', 'organisasi', 'judul pengaduan', 'qwertydfgh', 'dsshejkmccvtf', 'logo apple.png', 'pending', '2025-02-25 02:06:37');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nisn`, `nama_siswa`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `id_kelas`, `nis`) VALUES
(1, '26549414', 'Siswa1', 'Laki-laki', '2001-03-24', 'mjl', 1, '2345678'),
(2, '26549415', 'Siswa2', 'Laki-laki', '2001-03-25', 'mjl', 3, '2345679'),
(3, '26549416', 'Siswa3', 'Laki-laki', '2001-03-26', 'mjl', 3, '2345680'),
(4, '26549417', 'Siswa4', 'Laki-laki', '2001-03-27', 'mjl', 1, '2345681'),
(5, '26549418', 'Siswa5', 'Laki-laki', '2001-03-28', 'mjl', 1, '2345682'),
(6, '26549419', 'Siswa6', 'Perempuan', '2001-03-29', 'mjl', 1, '2345683'),
(7, '26549420', 'Siswa7', 'Perempuan', '2001-03-30', 'mjl', 1, '2345684'),
(8, '26549421', 'Siswa8', 'Perempuan', '2001-03-31', 'mjl', 1, '2345685'),
(9, '26549422', 'Siswa9', 'Perempuan', '2001-04-01', 'mjl', 1, '2345686'),
(10, '26549423', 'Siswa10', 'Perempuan', '2001-04-02', 'mjl', 1, '2345687'),
(11, '999999', 'AJis', 'Laki-laki', '2025-02-13', '-', 3, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kehadiran`
--

CREATE TABLE `tbl_kehadiran` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `verification_mode` varchar(50) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `tbl_kehadiran`
--

INSERT INTO `tbl_kehadiran` (`id`, `user_id`, `user_name`, `timestamp`, `verification_mode`, `status`) VALUES
(1, '1', '1', '2025-02-25 13:56:15', NULL, 'Masuk'),
(2, '1', '1', '2025-02-25 14:05:29', NULL, 'Masuk'),
(3, '1', '1', '2025-02-25 14:09:45', NULL, 'Masuk'),
(4, '1', '1', '2025-02-25 14:21:28', NULL, 'Masuk'),
(5, '1', '1', '2025-02-25 15:15:30', NULL, 'Masuk'),
(6, '2', '2', '2025-02-26 09:31:20', NULL, 'Keluar'),
(7, '1', '1', '2025-02-26 10:01:41', NULL, 'Masuk'),
(8, '1', '1', '2025-02-26 11:04:16', NULL, 'Masuk'),
(9, '1', '1', '2025-02-26 11:29:02', NULL, 'Masuk'),
(10, '1', '1', '2025-02-26 13:16:41', NULL, 'Masuk'),
(11, '1', '1', '2025-02-26 13:51:47', NULL, 'Masuk'),
(12, '1', '1', '2025-02-26 16:14:37', NULL, 'Masuk'),
(13, '1', '1', '2025-02-25 13:56:15', 'Unknown', 'Masuk'),
(14, '1', '1', '2025-02-25 14:05:29', 'Unknown', 'Masuk'),
(15, '1', '1', '2025-02-25 14:09:45', 'Unknown', 'Masuk'),
(16, '1', '1', '2025-02-25 14:21:28', 'Unknown', 'Masuk'),
(17, '1', '1', '2025-02-25 15:15:30', 'Unknown', 'Masuk'),
(18, '2', '2', '2025-02-26 09:31:20', 'Unknown', 'Keluar'),
(19, '1', '1', '2025-02-26 10:01:41', 'Unknown', 'Masuk'),
(20, '1', '1', '2025-02-26 11:04:16', 'Unknown', 'Masuk'),
(21, '1', '1', '2025-02-26 11:29:02', 'Unknown', 'Masuk'),
(22, '1', '1', '2025-02-26 13:16:41', 'Unknown', 'Masuk'),
(23, '1', '1', '2025-02-26 13:51:47', 'Unknown', 'Masuk'),
(24, '1', '1', '2025-02-26 16:14:37', 'Unknown', 'Masuk'),
(25, '1', '1', '2025-02-26 18:15:25', 'Unknown', 'Masuk'),
(26, '1', 'admin', '2025-02-25 13:56:15', 'Unknown', 'Masuk'),
(27, '1', 'admin', '2025-02-25 14:05:29', 'Unknown', 'Masuk'),
(28, '1', 'admin', '2025-02-25 14:09:45', 'Unknown', 'Masuk'),
(29, '1', 'admin', '2025-02-25 14:21:28', 'Unknown', 'Masuk'),
(30, '1', 'admin', '2025-02-25 15:15:30', 'Unknown', 'Masuk'),
(31, '2', '2', '2025-02-26 09:31:20', 'Unknown', 'Keluar'),
(32, '1', 'admin', '2025-02-26 10:01:41', 'Unknown', 'Masuk'),
(33, '1', 'admin', '2025-02-26 11:04:16', 'Unknown', 'Masuk'),
(34, '1', 'admin', '2025-02-26 11:29:02', 'Unknown', 'Masuk'),
(35, '1', 'admin', '2025-02-26 13:16:41', 'Unknown', 'Masuk'),
(36, '1', 'admin', '2025-02-26 13:51:47', 'Unknown', 'Masuk'),
(37, '1', 'admin', '2025-02-26 16:14:37', 'Unknown', 'Masuk'),
(38, '1', 'admin', '2025-02-26 18:15:25', 'Unknown', 'Masuk'),
(39, '1', 'admin', '2025-02-26 18:16:48', 'Unknown', 'Masuk'),
(40, '1', 'admin', '2025-02-26 18:16:56', 'Unknown', 'Masuk'),
(41, '1', 'admin', '2025-02-25 13:56:15', 'Unknown', 'Masuk'),
(42, '1', 'admin', '2025-02-25 14:05:29', 'Unknown', 'Masuk'),
(43, '1', 'admin', '2025-02-25 14:09:45', 'Unknown', 'Masuk'),
(44, '1', 'admin', '2025-02-25 14:21:28', 'Unknown', 'Masuk'),
(45, '1', 'admin', '2025-02-25 15:15:30', 'Unknown', 'Masuk'),
(46, '2', '2', '2025-02-26 09:31:20', 'Unknown', 'Keluar'),
(47, '1', 'admin', '2025-02-26 10:01:41', 'Unknown', 'Masuk'),
(48, '1', 'admin', '2025-02-26 11:04:16', 'Unknown', 'Masuk'),
(49, '1', 'admin', '2025-02-26 11:29:02', 'Unknown', 'Masuk'),
(50, '1', 'admin', '2025-02-26 13:16:41', 'Unknown', 'Masuk'),
(51, '1', 'admin', '2025-02-26 13:51:47', 'Unknown', 'Masuk'),
(52, '1', 'admin', '2025-02-26 16:14:37', 'Unknown', 'Masuk'),
(53, '1', 'admin', '2025-02-26 18:15:25', 'Unknown', 'Masuk'),
(54, '1', 'admin', '2025-02-26 18:16:48', 'Unknown', 'Masuk'),
(55, '1', 'admin', '2025-02-26 18:16:56', 'Unknown', 'Masuk'),
(56, '5', 'arief', '2025-03-05 15:57:07', 'Unknown', 'Masuk'),
(57, '1', 'admin', '2025-02-25 13:56:15', 'Unknown', 'Masuk'),
(58, '1', 'admin', '2025-02-25 14:05:29', 'Unknown', 'Masuk'),
(59, '1', 'admin', '2025-02-25 14:09:45', 'Unknown', 'Masuk'),
(60, '1', 'admin', '2025-02-25 14:21:28', 'Unknown', 'Masuk'),
(61, '1', 'admin', '2025-02-25 15:15:30', 'Unknown', 'Masuk'),
(62, '2', '2', '2025-02-26 09:31:20', 'Unknown', 'Keluar'),
(63, '1', 'admin', '2025-02-26 10:01:41', 'Unknown', 'Masuk'),
(64, '1', 'admin', '2025-02-26 11:04:16', 'Unknown', 'Masuk'),
(65, '1', 'admin', '2025-02-26 11:29:02', 'Unknown', 'Masuk'),
(66, '1', 'admin', '2025-02-26 13:16:41', 'Unknown', 'Masuk'),
(67, '1', 'admin', '2025-02-26 13:51:47', 'Unknown', 'Masuk'),
(68, '1', 'admin', '2025-02-26 16:14:37', 'Unknown', 'Masuk'),
(69, '1', 'admin', '2025-02-26 18:15:25', 'Unknown', 'Masuk'),
(70, '1', 'admin', '2025-02-26 18:16:48', 'Unknown', 'Masuk'),
(71, '1', 'admin', '2025-02-26 18:16:56', 'Unknown', 'Masuk'),
(72, '5', 'arief', '2025-03-05 15:57:07', 'Unknown', 'Masuk'),
(73, '1', 'admin', '2025-03-05 15:57:48', 'Unknown', 'Masuk'),
(74, '1', 'admin', '2025-02-25 13:56:15', 'Unknown', 'Masuk'),
(75, '1', 'admin', '2025-02-25 14:05:29', 'Unknown', 'Masuk'),
(76, '1', 'admin', '2025-02-25 14:09:45', 'Unknown', 'Masuk'),
(77, '1', 'admin', '2025-02-25 14:21:28', 'Unknown', 'Masuk'),
(78, '1', 'admin', '2025-02-25 15:15:30', 'Unknown', 'Masuk'),
(79, '2', '2', '2025-02-26 09:31:20', 'Unknown', 'Keluar'),
(80, '1', 'admin', '2025-02-26 10:01:41', 'Unknown', 'Masuk'),
(81, '1', 'admin', '2025-02-26 11:04:16', 'Unknown', 'Masuk'),
(82, '1', 'admin', '2025-02-26 11:29:02', 'Unknown', 'Masuk'),
(83, '1', 'admin', '2025-02-26 13:16:41', 'Unknown', 'Masuk'),
(84, '1', 'admin', '2025-02-26 13:51:47', 'Unknown', 'Masuk'),
(85, '1', 'admin', '2025-02-26 16:14:37', 'Unknown', 'Masuk'),
(86, '1', 'admin', '2025-02-26 18:15:25', 'Unknown', 'Masuk'),
(87, '1', 'admin', '2025-02-26 18:16:48', 'Unknown', 'Masuk'),
(88, '1', 'admin', '2025-02-26 18:16:56', 'Unknown', 'Masuk'),
(89, '5', 'arief', '2025-03-05 15:57:07', 'Unknown', 'Masuk'),
(90, '1', 'admin', '2025-03-05 15:57:48', 'Unknown', 'Masuk');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `uid` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `role`, `uid`, `created_at`) VALUES
(1, 'Admin', '', 'admin', '1', '2025-02-26 07:53:17'),
(2, '2', '', '', '2', '2025-02-26 07:59:45'),
(3, '5', '', '', '3', '2025-02-26 07:59:45'),
(4, 'ayam', '', '', '4', '2025-02-26 07:59:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  ADD PRIMARY KEY (`id_absensi_guru`),
  ADD KEY `id_guru` (`id_guru`);

--
-- Indexes for table `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  ADD PRIMARY KEY (`id_absensi_siswa`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `id_jurusan` (`id_jurusan`);

--
-- Indexes for table `laporan_absensi`
--
ALTER TABLE `laporan_absensi`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_absensi_guru` (`id_absensi_guru`),
  ADD KEY `id_absensi_siswa` (`id_absensi_siswa`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `tbl_kehadiran`
--
ALTER TABLE `tbl_kehadiran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`name`),
  ADD UNIQUE KEY `id_fingerprint` (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi_guru`
--
ALTER TABLE `absensi_guru`
  MODIFY `id_absensi_guru` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  MODIFY `id_absensi_siswa` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id_jurusan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `laporan_absensi`
--
ALTER TABLE `laporan_absensi`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `tbl_kehadiran`
--
ALTER TABLE `tbl_kehadiran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_guru`
--
ALTER TABLE `absensi_guru`
  ADD CONSTRAINT `absensi_guru_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `absensi_siswa`
--
ALTER TABLE `absensi_siswa`
  ADD CONSTRAINT `absensi_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id_jurusan`);

--
-- Ketidakleluasaan untuk tabel `laporan_absensi`
--
ALTER TABLE `laporan_absensi`
  ADD CONSTRAINT `laporan_absensi_ibfk_1` FOREIGN KEY (`id_absensi_guru`) REFERENCES `absensi_guru` (`id_absensi_guru`),
  ADD CONSTRAINT `laporan_absensi_ibfk_2` FOREIGN KEY (`id_absensi_siswa`) REFERENCES `absensi_siswa` (`id_absensi_siswa`);

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
