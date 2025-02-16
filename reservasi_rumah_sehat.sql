-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Waktu pembuatan: 16 Feb 2025 pada 01.59
-- Versi server: 8.0.35
-- Versi PHP: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reservasi_rumah_sehat`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `password`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id_dokter` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `spesialis` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id_dokter`, `nama`, `email`, `password`, `spesialis`, `jenis_kelamin`, `no_hp`) VALUES
(3, 'Dr.Mawar', 'dokter3@gmail.com', 'dokter3', 'Umum', 'P', '088229459145'),
(4, 'Dr.Ucup', 'dokter4@gmail.com', 'dokter4', 'Dermatologi', 'L', '0846262644624'),
(5, 'Dr.Bertrand', 'dokter5@gmail.com', 'dokter5', 'Neurologi', 'L', '0852534466422'),
(7, 'Dr.Sasuke', 'dokter6@gmail.com', 'dokter6', 'Onkologi', 'L', '083267546384'),
(10, 'Dr.Siti', 'dokter1@gmail.com', 'dokter1', 'Kardiologi', 'P', '088229459145'),
(11, 'Dr.Putra', 'dokter2@gmail.com', 'dokter2', 'Radiologi', 'L', '082352323432');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pasien`
--

CREATE TABLE `pasien` (
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text,
  `no_hp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `pasien`
--

INSERT INTO `pasien` (`nik`, `nama`, `email`, `password`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`) VALUES
('3217081411040010', 'hari', 'hari@gmail.com', 'hari123', '2025-02-13', 'L', 'ciampel', '088229459145'),
('987654321123010', 'kiki', 'kiki@gmail.com', 'kiki123', '2018-07-19', 'L', 'garut', '082446462424');

-- --------------------------------------------------------

--
-- Struktur dari tabel `poli`
--

CREATE TABLE `poli` (
  `id_poli` int NOT NULL,
  `nama_poli` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `poli`
--

INSERT INTO `poli` (`id_poli`, `nama_poli`) VALUES
(1, 'Jantung'),
(2, 'Radiologi'),
(3, 'Umum'),
(4, 'Dermatologi'),
(5, 'Neurologi'),
(6, 'Onkologi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservasi`
--

CREATE TABLE `reservasi` (
  `id_reservasi` int NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `id_dokter` int DEFAULT NULL,
  `id_poli` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `status` enum('Tunggu','Dikonfirmasi','Selesai') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `reservasi`
--

INSERT INTO `reservasi` (`id_reservasi`, `nik`, `id_dokter`, `id_poli`, `tanggal`, `status`) VALUES
(26, '3217081411040010', 4, 3, '2025-02-15', 'Dikonfirmasi'),
(31, '3217081411040010', 10, 1, '2025-02-15', 'Tunggu'),
(33, '987654321123010', 10, 1, '2025-02-15', 'Dikonfirmasi');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id_dokter`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id_poli`);

--
-- Indeks untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `nik` (`nik`),
  ADD KEY `id_dokter` (`id_dokter`),
  ADD KEY `id_poli` (`id_poli`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id_dokter` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `poli`
--
ALTER TABLE `poli`
  MODIFY `id_poli` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `pasien` (`nik`),
  ADD CONSTRAINT `reservasi_ibfk_2` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`),
  ADD CONSTRAINT `reservasi_ibfk_3` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
