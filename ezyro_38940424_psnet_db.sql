-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql107.ezyro.com
-- Generation Time: Jun 16, 2025 at 10:44 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezyro_38940424_psnet_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_log`
--

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `pesantren_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `judul_id` varchar(255) DEFAULT NULL,
  `judul_en` varchar(255) DEFAULT NULL,
  `judul_ms` varchar(255) DEFAULT NULL,
  `deskripsi` text NOT NULL,
  `deskripsi_id` text DEFAULT NULL,
  `deskripsi_en` text DEFAULT NULL,
  `deskripsi_ms` text DEFAULT NULL,
  `penyelenggara` varchar(255) DEFAULT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `jumlah_peserta` int(11) DEFAULT NULL,
  `suka` int(11) DEFAULT 0,
  `tanggal` date NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `pengguna_id`, `pesantren_id`, `judul`, `judul_id`, `judul_en`, `judul_ms`, `deskripsi`, `deskripsi_id`, `deskripsi_en`, `deskripsi_ms`, `penyelenggara`, `tempat`, `jumlah_peserta`, `suka`, `tanggal`, `gambar`, `created_at`) VALUES
(1, 4, NULL, 'Seminar Nasional Pesantren 2025', 'Seminar Nasional Pesantren 2025', NULL, NULL, 'Seminar untuk membahas masa depan pendidikan Islam.', 'Seminar untuk membahas masa depan pendidikan Islam.', NULL, NULL, 'Pondok Pesantren jatiwungu', 'Tegal', 22, 6, '2025-02-27', 'seminar.jpg', '2025-05-09 11:09:36'),
(2, 7, NULL, 'Lomba Tahfidz Antar-Pesantren', 'Lomba Tahfidz Antar-Pesantren', NULL, NULL, 'Lomba tahfidz Al-Quran tingkat nasional.', 'Lomba tahfidz Al-Qur’an tingkat nasional.', NULL, NULL, '', '', 0, 4, '2025-03-10', 'lomba.jpg', '2025-05-09 11:09:36'),
(3, 25, 3, 'Bazar Amal Pesantren Gontor', 'Bazar Amal Pesantren Gontor', 'Gontor Pesantren Charity Bazaar', NULL, 'Bazar amal untuk menggalang dana pendidikan santri kurang mampu.', 'Bazar amal untuk menggalang dana pendidikan santri kurang mampu.', 'Charity bazaar to raise funds for underprivileged santri education.', NULL, 'Pondok Pesantren Gontor', 'Ponorogo', 300, 50, '2025-04-01', '68274215d42b7.jpg', '2025-05-11 08:00:00'),
(4, 26, 4, 'Workshop Teknologi Informasi Santri', 'Workshop Teknologi Informasi Santri', 'Santri IT Workshop', NULL, 'Workshop untuk mengenalkan teknologi informasi kepada santri.', 'Workshop untuk mengenalkan teknologi informasi kepada santri.', 'Workshop to introduce information technology to santri.', NULL, 'Pondok Pesantren Al-Mansyur', 'Ngaliyan, Semarang', 60, 12, '2025-04-10', '6827415b4d6a8.jpeg', '2025-05-11 09:00:00'),
(5, 27, 6, 'Lomba Debat Islam Nusantara', 'Lomba Debat Islam Nusantara', 'Nusantara Islamic Debate Competition', NULL, 'Lomba debat tentang nilai-nilai Islam Nusantara.', 'Lomba debat tentang nilai-nilai Islam Nusantara.', 'Debate competition on Nusantara Islamic values.', NULL, 'Pondok Pesantren Darul Falah', 'Semarang', 40, 8, '2025-03-25', '68274254533c4.jpeg', '2025-05-11 10:00:00'),
(6, 28, 7, 'Kajian Kitab Kuning', 'Kajian Kitab Kuning', 'Yellow Book Study Session', NULL, 'Kajian intensif kitab kuning oleh ulama ternama.', 'Kajian intensif kitab kuning oleh ulama ternama.', 'Intensive study of yellow books by renowned scholars.', NULL, 'Pondok Pesantren Al-Anwar', 'Sarang, Rembang', 100, 20, '2025-04-20', '68273ffe70314.jpeg', '2025-05-12 08:00:00'),
(7, 29, 8, 'Tadarus Al-Quran Bersama', 'Tadarus Al-Qur\'an Bersama', 'Communal Qur\'an Recitation', NULL, 'Kegiatan tadarus Al-Quran bersama santri dan masyarakat.', 'Kegiatan tadarus Al-Qur\'an bersama santri dan masyarakat.', 'Communal Qur\'an recitation with santri and community.', NULL, 'Pondok Pesantren Nurul Huda', 'Ngaliyan, Semarang', 120, 30, '2025-04-05', '682741906c30b.jpeg', '2025-05-12 09:00:00'),
(8, 30, 9, 'Peringatan Maulid Nabi 2025', 'Peringatan Maulid Nabi 2025', 'Prophet\'s Birthday Commemoration 2025', NULL, 'Peringatan Maulid Nabi dengan shalawat dan ceramah agama.', 'Peringatan Maulid Nabi dengan shalawat dan ceramah agama.', 'Prophet\'s Birthday commemoration with shalawat and religious lectures.', NULL, 'Pondok Pesantren Al-Munawwar', 'Demak', 200, 35, '2025-04-12', '682740f7e165d.jpeg', '2025-05-12 10:00:00'),
(9, 31, 10, 'Pelatihan Kepemimpinan Santri', 'Pelatihan Kepemimpinan Santri', 'Santri Leadership Training', NULL, 'Pelatihan intensif untuk mengembangkan jiwa kepemimpinan santri.', 'Pelatihan intensif untuk mengembangkan jiwa kepemimpinan santri.', 'Intensive training to develop leadership skills for santri.', NULL, 'Pondok Pesantren Darul Hikmah', 'Ngaliyan, Semarang', 50, 10, '2025-03-20', '682742a5ee921.jpeg', '2025-05-12 11:00:00'),
(10, 32, 1, 'Festival Seni Islami', 'Festival Seni Islami', 'Islamic Art Festival', NULL, 'Festival seni yang menampilkan nasyid, kaligrafi, dan seni Islam lainnya.', 'Festival seni yang menampilkan nasyid, kaligrafi, dan seni Islam lainnya.', 'Art festival showcasing nasyid, calligraphy, and other Islamic arts.', NULL, 'Pondok Pesantren Al-Hidayah', 'Yogyakarta', 80, 15, '2025-04-15', '68274099713e2.jpeg', '2025-05-12 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan_komentar`
--

CREATE TABLE `kegiatan_komentar` (
  `id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `isi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan_komentar`
--

INSERT INTO `kegiatan_komentar` (`id`, `kegiatan_id`, `pengguna_id`, `isi`, `created_at`) VALUES
(1, 1, 4, 'menarik', '2025-05-10 18:54:22'),
(2, 3, 16, 'Bazar amal ini sangat bermakna, semoga dana terkumpul banyak!', '2025-05-11 08:30:00'),
(3, 4, 17, 'Workshop IT ini membuka wawasan saya tentang teknologi!', '2025-05-11 09:30:00'),
(4, 5, 18, 'Debatnya seru, banyak ide-ide baru tentang Islam Nusantara.', '2025-05-11 10:30:00'),
(5, 6, 19, 'Kajian kitab kuning ini sangat mendalam, terima kasih ustadz!', '2025-05-12 08:30:00'),
(6, 7, 20, 'Tadarus bersama ini bikin hati tenang, Alhamdulillah.', '2025-05-12 09:30:00'),
(7, 8, 21, 'Maulid Nabi kali ini sangat khidmat, semoga berkah!', '2025-05-12 10:30:00'),
(8, 9, 22, 'Pelatihan ini membantu saya jadi lebih percaya diri!', '2025-05-12 11:30:00'),
(9, 10, 23, 'Festival seni ini keren banget, nasyidnya bikin merinding!', '2025-05-12 12:30:00'),
(10, 1, 24, 'Seminar ini sangat inspiratif, semoga ada lagi tahun depan!', '2025-05-12 13:00:00'),
(11, 2, 33, 'Lomba tahfidz ini luar biasa, banyak hafidz muda berbakat!', '2025-05-12 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan_likes`
--

CREATE TABLE `kegiatan_likes` (
  `id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kolaborasi`
--

CREATE TABLE `kolaborasi` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `pesantren_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `deskripsi_id` text DEFAULT NULL,
  `deskripsi_en` text DEFAULT NULL,
  `deskripsi_ms` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipe` enum('kolaborasi','promosi') DEFAULT 'kolaborasi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kolaborasi`
--

INSERT INTO `kolaborasi` (`id`, `pengguna_id`, `pesantren_id`, `judul`, `deskripsi`, `deskripsi_id`, `deskripsi_en`, `deskripsi_ms`, `status`, `created_at`, `tipe`) VALUES
(1, 7, 5, 'Pertukaran Santri Antar Benua', 'Program inspiratif yang mempertemukan santri dari berbagai belahan dunia untuk saling berbagi ilmu, budaya, dan pengalaman spiritual.', NULL, NULL, NULL, 'pending', '2025-05-09 14:39:24', 'kolaborasi'),
(3, 7, 5, 'Wisuda', 'Momentum sakral untuk merayakan pencapaian santri dalam menuntaskan perjalanan ilmu dan menapaki langkah baru menuju pengabdian dan kemanfaatan umat.', NULL, NULL, NULL, 'pending', '2025-05-10 22:35:23', 'kolaborasi'),
(8, 4, 12, 'Seminar Nasional Pesantren 2025', 'Mari ambil bagian dalam gerakan kolaboratif untuk pesantren yang lebih maju dan berdaya!', NULL, NULL, NULL, 'pending', '2025-05-12 21:10:11', 'promosi'),
(9, 25, 3, 'Seminar Bersama Gontor dan Al-Hidayah', 'Seminar kolaborasi untuk membahas pendidikan Islam modern.', 'Seminar kolaborasi untuk membahas pendidikan Islam modern.', 'Collaborative seminar to discuss modern Islamic education.', NULL, 'approved', '2025-05-11 08:00:00', 'kolaborasi'),
(10, 26, 4, 'Pameran Produk Santri', 'Pameran produk kreatif santri untuk mempromosikan kewirausahaan.', 'Pameran produk kreatif santri untuk mempromosikan kewirausahaan.', 'Exhibition of santri creative products to promote entrepreneurship.', NULL, 'pending', '2025-05-11 09:00:00', 'promosi'),
(11, 27, 6, 'Pelatihan Guru Pesantren', 'Pelatihan untuk meningkatkan kualitas pengajaran di pesantren.', 'Pelatihan untuk meningkatkan kualitas pengajaran di pesantren.', 'Training to improve teaching quality in pesantren.', NULL, 'approved', '2025-05-11 10:00:00', 'kolaborasi'),
(12, 28, 7, 'Festival Budaya Islam', 'Festival budaya Islam antar pesantren di Jawa Tengah.', 'Festival budaya Islam antar pesantren di Jawa Tengah.', 'Islamic cultural festival among pesantren in Central Java.', NULL, 'pending', '2025-05-12 08:00:00', 'kolaborasi'),
(13, 29, 8, 'Promosi Pesantren Nurul Huda', 'Kampanye promosi untuk memperkenalkan program unggulan pesantren.', 'Kampanye promosi untuk memperkenalkan program unggulan pesantren.', 'Promotion campaign to introduce pesantren’s flagship programs.', NULL, 'pending', '2025-05-12 09:00:00', 'promosi'),
(14, 30, 9, 'Lomba Karya Tulis Ilmiah', 'Lomba karya tulis ilmiah untuk santri se-Jawa Tengah.', 'Lomba karya tulis ilmiah untuk santri se-Jawa Tengah.', 'Scientific writing competition for santri in Central Java.', NULL, 'approved', '2025-05-12 10:00:00', 'kolaborasi'),
(15, 31, 10, 'Program Beasiswa Santri', 'Program beasiswa untuk santri berprestasi.', 'Program beasiswa untuk santri berprestasi.', 'Scholarship program for high-achieving santri.', NULL, 'pending', '2025-05-12 11:00:00', 'kolaborasi'),
(16, 32, 1, 'Program Riset Bersama', 'Kolaborasi riset untuk pengembangan kurikulum berbasis teknologi.', 'Kolaborasi riset untuk pengembangan kurikulum berbasis teknologi.', 'Research collaboration for technology-based curriculum development.', NULL, 'approved', '2025-05-12 12:00:00', 'kolaborasi');

-- --------------------------------------------------------

--
-- Table structure for table `kolaborasi_feedback`
--

CREATE TABLE `kolaborasi_feedback` (
  `id` int(11) NOT NULL,
  `kolaborasi_id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kolaborasi_feedback`
--

INSERT INTO `kolaborasi_feedback` (`id`, `kolaborasi_id`, `pengguna_id`, `feedback`, `created_at`) VALUES
(1, 1, 4, 'Wah bagus sekali kang', '2025-05-09 19:56:47'),
(2, 1, 4, 'yoi', '2025-05-10 18:54:04'),
(3, 9, 16, 'Seminar ini sangat informatif, semoga ada kolaborasi lagi!', '2025-05-11 08:30:00'),
(4, 10, 17, 'Pameran produk santri ini keren, banyak produk inovatif!', '2025-05-11 09:30:00'),
(5, 11, 18, 'Pelatihan ini membantu guru pesantren jadi lebih profesional.', '2025-05-11 10:30:00'),
(6, 12, 19, 'Festival budaya ini mempererat silaturahmi antar pesantren.', '2025-05-12 08:30:00'),
(7, 13, 20, 'Promosi ini sangat menarik, programnya terlihat unggul!', '2025-05-12 09:30:00'),
(8, 14, 21, 'Lomba karya tulis ini memotivasi santri untuk berkarya.', '2025-05-12 10:30:00'),
(9, 15, 22, 'Beasiswa ini sangat membantu santri kurang mampu.', '2025-05-12 11:30:00'),
(10, 16, 23, 'Riset ini sangat penting untuk masa depan pendidikan pesantren.', '2025-05-12 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `isi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id`, `thread_id`, `pengguna_id`, `isi`, `created_at`) VALUES
(1, 1, 4, 'AAmiin', '2025-05-09 12:36:50'),
(2, 1, 4, 'AAmiin', '2025-05-09 12:37:00'),
(4, 3, 4, 'Mantap', '2025-05-10 18:44:23'),
(6, 5, 16, 'Pengalaman di pesantren benar-benar tak terlupakan.', '2025-05-11 08:30:00'),
(7, 6, 17, 'Tips yang sangat bermanfaat, saya juga suka hafal di waktu subuh!', '2025-05-11 09:30:00'),
(8, 7, 18, 'Cerita inspiratif ini bikin semangat belajar lagi!', '2025-05-11 10:30:00'),
(9, 8, 19, 'Belajar tajwid itu penting banget, makasih infonya!', '2025-05-12 08:30:00'),
(10, 9, 20, 'Santri jaman sekarang harus melek teknologi!', '2025-05-12 09:30:00'),
(11, 10, 21, 'Ceramah ini bikin saya makin cinta sama pesantren.', '2025-05-12 10:30:00'),
(12, 5, 22, 'Keren banget acara ini, semoga sering diadakan!', '2025-05-12 11:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `pengelola_pesantren`
--

CREATE TABLE `pengelola_pesantren` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `pesantren_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengelola_pesantren`
--

INSERT INTO `pengelola_pesantren` (`id`, `pengguna_id`, `pesantren_id`) VALUES
(3, 7, 5),
(10, 4, 12),
(11, 25, 3),
(12, 26, 4),
(13, 27, 6),
(14, 28, 7),
(15, 29, 8),
(16, 30, 9),
(17, 31, 10),
(18, 32, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `security_question` varchar(255) NOT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `role` enum('santri','pengelola','admin') NOT NULL,
  `pesantren_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_profil` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `verification_doc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `password`, `security_question`, `security_answer`, `role`, `pesantren_id`, `created_at`, `foto_profil`, `profile_picture`, `status`, `verification_doc`) VALUES
(1, 'Admin PSNet', 'admin@psnet.id', '$2y$10$kjOw6Ik9bKySCqRk.CPG6.XdZgsIn5SwjzYcdNBqzFh2Kv8zu76HO', '', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'admin', 1, '2025-05-09 11:09:36', NULL, NULL, NULL, NULL),
(2, 'Ronando Musyafiri', '28.ronando.m@gmail.com', '$2y$10$UQZcC3bNpCyzeM2AC1XKH.kMBUIXOWWqwOuDNY/uCrjO9EZeRjIL.', 'Berapa usiamu?', '21', 'santri', 0, '2025-05-09 11:44:23', NULL, '681e5ff2548e5.png', 'verified', NULL),
(4, 'Bima Pranawira', '2208066011@student.walisongo.ac.id', '$2y$10$GGNwOjga.J0ZRMbzH1mQAeYop.3oE/.0P1cGUaZ0P2sHnHCPktdjm', '', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'pengelola', NULL, '2025-05-09 12:01:01', NULL, '', 'verified', NULL),
(7, 'Ali Mustain', 'ronando280304@gmail.com', '$2y$10$yjPvm/Bhqv4iNbPgFOgsDufIj/MRrnIGavCzzqpgwT1CEF/XhZU5W', '', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'pengelola', NULL, '2025-05-09 14:30:16', NULL, NULL, 'verified', NULL),
(8, 'Budi', 'Budi123@gmail.com', '$2y$10$qjwBPXvCPa.P0M89nsM4IOo1DuqStSkR2IGz8r8zo6MnHNUvtzpFO', '', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', 'pengelola', NULL, '2025-05-10 10:21:20', NULL, NULL, 'verified', NULL),
(10, 'Jafar', 'jafar@gmail.com', '$2y$10$Q98CBNRCre2QtM8HQDv/Xeb0xoTUzOv6LYU9KAgXFvHRAOphUAhqC', 'Apa nama hewan peliharaan pertama Anda?', 'heri', 'santri', 12, '2025-05-12 21:53:09', NULL, NULL, 'rejected', NULL),
(11, 'imin', 'imin@gmail.com', '$2y$10$Ik/HFqLXuoqav6iIoM/U/O8wYbfd.d9YLoyL91wRMoMuKOcf4p5vq', 'Apa nama hewan peliharaan pertama Anda?', 'heri', 'pengelola', NULL, '2025-05-12 22:19:36', NULL, NULL, 'verified', '682273f8bc11c.png'),
(12, 'Upin', 'upin@gmail.com', '$2y$10$MfRIR5vMDAbaZ0L4dbNeTuiOsWkuYBF8ERU6g5uKr8Ugtk.K22Aym', 'Apa nama hewan peliharaan pertama Anda?', 'yudi', 'santri', 12, '2025-05-12 22:27:09', NULL, NULL, 'verified', NULL),
(13, 'Joko anwar', 'anwar@gmail.com', '$2y$10$j/VwLQCLubbw9VH.il3ro.y82EEx3O0279og1t/j1V3SwdbsP9rM2', 'Apa nama hewan peliharaan pertama Anda?', 'Susi', 'pengelola', NULL, '2025-05-12 22:28:02', NULL, NULL, 'rejected', '682275f2b816b.png'),
(14, 'Muhamad Alfarizi', 'aalfarizi3327@gmail.com', '$2y$10$PV7l3qQQhzBAl.pl9FrmYevWKqYy2C.uK5gbE1X2CE30KqZ8zSM5C', 'Di kota mana Anda lahir?', 'Di kota mana Anda lahir?', 'pengelola', NULL, '2025-05-14 06:10:07', NULL, NULL, 'verified', '682433bf7da0c.pdf'),
(15, 'Sanep Al Sanusi', 'sanusi@gmail.com', '$2y$10$cKJ.Yc0KQeKTsWOobx.SSO/Z8svLgxh7bcu8klSh8St6wJTTRxt.m', 'Di kota mana Anda lahir?', 'Tegal', 'santri', 5, '2025-05-14 06:54:09', NULL, NULL, 'verified', NULL),
(16, 'Ahmad Fauzi', 'ahmad.fauzi@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama hewan peliharaan pertama Anda?', 'Kucing', 'santri', 1, '2025-05-10 08:00:00', NULL, NULL, 'verified', NULL),
(17, 'Fatimah Azzahra', 'fatimah.azzahra@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Semarang', 'santri', 2, '2025-05-10 09:00:00', NULL, NULL, 'verified', NULL),
(18, 'Siti Aminah', 'siti.aminah@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama sekolah pertama Anda?', 'SDN Ngaliyan', 'santri', 3, '2025-05-10 10:00:00', NULL, NULL, 'verified', NULL),
(19, 'Zainab Khadijah', 'zainab.khadijah@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Ponorogo', 'santri', 4, '2025-05-10 11:00:00', NULL, NULL, 'verified', NULL),
(20, 'Aisyah Nur', 'aisyah.nur@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama sekolah pertama Anda?', 'SDN Semarang', 'santri', 5, '2025-05-10 12:00:00', NULL, NULL, 'verified', NULL),
(21, 'Maryam Salsabila', 'maryam.salsabila@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama hewan peliharaan pertama Anda?', 'Lulu', 'santri', 6, '2025-05-10 13:00:00', NULL, NULL, 'verified', NULL),
(22, 'Khadijah Aulia', 'khadijah.aulia@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Rembang', 'santri', 7, '2025-05-10 14:00:00', NULL, NULL, 'verified', NULL),
(23, 'Hafsa Nurul', 'hafsa.nurul@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama hewan peliharaan pertama Anda?', 'Milo', 'santri', 8, '2025-05-10 15:00:00', NULL, NULL, 'verified', NULL),
(24, 'Aminah Sofia', 'aminah.sofia@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama ibu Anda?', 'Sofia', 'santri', 9, '2025-05-10 16:00:00', NULL, NULL, 'verified', NULL),
(25, 'Hassanudin', 'hassanudin@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Yogyakarta', 'pengelola', NULL, '2025-05-10 17:00:00', NULL, NULL, 'verified', 'doc_hassanudin.pdf'),
(26, 'Rudi Hartono', 'rudi.hartono@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama hewan peliharaan pertama Anda?', 'Brownie', 'pengelola', NULL, '2025-05-10 18:00:00', NULL, NULL, 'verified', 'doc_rudi.pdf'),
(27, 'Ibrahim Malik', 'ibrahim.malik@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama ibu Anda?', 'Maryam', 'pengelola', NULL, '2025-05-10 19:00:00', NULL, NULL, 'pending', NULL),
(28, 'Yusuf Hamzah', 'yusuf.hamzah@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Demak', 'pengelola', NULL, '2025-05-10 20:00:00', NULL, NULL, 'verified', 'doc_yusuf.pdf'),
(29, 'Abdullah Fahmi', 'abdullah.fahmi@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama ibu Anda?', 'Fatimah', 'pengelola', NULL, '2025-05-10 21:00:00', NULL, NULL, 'verified', NULL),
(30, 'Musa Idris', 'musa.idris@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama sekolah pertama Anda?', 'SDN Ngaliyan 2', 'pengelola', NULL, '2025-05-10 22:00:00', NULL, NULL, 'pending', NULL),
(31, 'Ismail Zaki', 'ismail.zaki@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Semarang', 'pengelola', NULL, '2025-05-10 23:00:00', NULL, NULL, 'verified', 'doc_ismail.pdf'),
(32, 'Harun Al Rasyid', 'harun.rasyid@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama sekolah pertama Anda?', 'SDN Demak', 'pengelola', NULL, '2025-05-11 08:00:00', NULL, NULL, 'verified', NULL),
(33, 'Lutfi Rahman', 'lutfi.rahman@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Apa nama hewan peliharaan pertama Anda?', 'Budi', 'santri', 10, '2025-05-11 09:00:00', NULL, NULL, 'verified', NULL),
(34, 'Nurul Hidayah', 'nurul.hidayah@gmail.com', '$2y$10$1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Qz3o3Z6z8Z6z8u1z/yX8z6Q', 'Di kota mana Anda lahir?', 'Tegal', 'santri', 12, '2025-05-11 10:00:00', NULL, NULL, 'verified', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pesantren`
--

CREATE TABLE `pesantren` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` enum('Tahfidz','Riset','Salafi','Modern') NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `lokasi_map` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `jumlah_santri` int(11) DEFAULT NULL,
  `tahun_berdiri` int(11) DEFAULT NULL,
  `akreditasi` varchar(50) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `deskripsi_id` text DEFAULT NULL,
  `deskripsi_en` text DEFAULT NULL,
  `deskripsi_ms` text DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deskripsi_ar` text DEFAULT NULL,
  `deskripsi_ja` text DEFAULT NULL,
  `deskripsi_nl` text DEFAULT NULL,
  `deskripsi_de` text DEFAULT NULL,
  `deskripsi_fr` text DEFAULT NULL,
  `deskripsi_es` text DEFAULT NULL,
  `deskripsi_it` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesantren`
--

INSERT INTO `pesantren` (`id`, `nama`, `kategori`, `lokasi`, `lokasi_map`, `latitude`, `longitude`, `gambar`, `jumlah_santri`, `tahun_berdiri`, `akreditasi`, `telepon`, `email`, `website`, `status`, `catatan_admin`, `whatsapp`, `deskripsi`, `deskripsi_id`, `deskripsi_en`, `deskripsi_ms`, `fasilitas`, `created_at`, `deskripsi_ar`, `deskripsi_ja`, `deskripsi_nl`, `deskripsi_de`, `deskripsi_fr`, `deskripsi_es`, `deskripsi_it`) VALUES
(1, 'Pesantren Al-Hidayah', 'Tahfidz', 'Yogyakarta', '', NULL, NULL, 'alhidayah.png', 0, 2015, '', '+62 85175125171', 'ppmhs.al.hidayah@gmail.com', 'https://ppalhidayah.org/', 'pending', NULL, '+62 85175125171', 'Pondok Pesantren Mahasiswi Al-Hidayah merupakan lembaga pendidikan yang berupaya mengembangkan intelektual mahasiswi dan menerapkan kepribadian berakhlak mulia. Al-Hidayah menyelenggarakan pembelajaran berpaham Ahlussunnah wal jama&#39;ah dan berbasis pendekatan akhlak aplikatif dengan core value berakhlakul karimah, beramal ilmiah, berilmu amaliah.', 'Pesantren fokus tahfidz Al-Qur\'an.', 'Pesantren focusing on Qur\'an memorization.', 'Pondok pesantren yang memfokuskan hafazan Al-Qur\'an.', '1. Area parkir motor ,\r\n2. Musholla ,\r\n3. Ruang multiguna ,\r\n4. Dapur ;-Kulkas; -Air; -Mineral, \r\n5. Kamar (pilihan: 1 kamar 1 santri dan 1 kamar 2 santri) ,\r\n6. Kamar mandi ,\r\n7. Area mencuci & menjemur \r\n8. Wi-Fi', '2025-05-09 11:09:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Pesantren Darussalam', 'Modern', 'Bandung', NULL, NULL, NULL, 'darussalam.jpg', 150, 2000, 'A', '085220291556', 'info@darussalam.id', 'www.darussalam.id', 'pending', NULL, '081298765432', 'Pesantren modern dengan sistem Gontor.', 'Pesantren modern dengan sistem Gontor.', NULL, NULL, 'Masjid besar, Asrama nyaman, Laboratorium bahasa', '2025-05-09 11:09:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Pesantren Gontor', 'Modern', 'Ponorogo', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d16312384.86985935!2d95.74629225004901!3d-3.4670699908496956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e790ac20fe5c507%3A0x4f6418afa2533efd!2sPondok%20Modern%20Darussalam%20Gontor%20Putra%20Kampus%20Pusat!5e0!3m2!1sid!2smy!4v1747528476744!5m2!1sid!2smy\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '-7.90108000', '111.47914100', '68292b2e1dd07.jpg', 5000, 1926, 'A', '(0352) 311766', 'sekpim@gontor.ac.id', 'https://gontor.ac.id/', 'verified', NULL, '0851-7512-1926', 'Pesantren legendaris dengan sistem pendidikan modern dan disiplin tinggi.', 'Pesantren legendaris dengan sistem pendidikan modern dan disiplin tinggi.', 'Legendary pesantren with a modern education system and high discipline.', 'Pondok pesantren legenda dengan sistem pendidikan moden dan disiplin tinggi.', 'Masjid megah, asrama, laboratorium komputer, lapangan olahraga', '2025-05-10 08:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Pesantren Al-Mansyur', 'Riset', 'Jl. Raya Mauk, Kp. Gurudug, Ds. Mekarjaya, Kec. Sepatan Tangerang Banten', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3362.6478462105947!2d106.57683000000002!3d-6.131414!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a003691887ee1%3A0x5b2dbbc93d94c66!2sPondok%20Pesantren%20Al%20Mansyuriyah!5e1!3m2!1sen!2sus!4v1747568568677!5m2!1sen!2sus\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '-6.99414400', '110.35697200', '6829d53754a4a.jpg', 150, 1939, '', '(024) 7654322', 'info@almansyur.id', 'https://pp-almansyuriyah.com/profil/pendiri-pesantren.html', 'pending', NULL, '081234567892', 'Pondok Pesantren Al-Mansyuriyah merupakan suatu lembaga pendidikan yang siap untuk menciptakan generasi muda untuk berdedikasi kepada umat', 'Pesantren yang fokus pada riset dan pengembangan ilmu pengetahuan.', 'Pesantren focused on research and scientific development.', 'Pondok pesantren yang menumpukan pada penyelidikan dan pembangunan saintifik.', 'Lab komputer, perpustakaan, ruang riset', '2025-05-10 09:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Roudlotus Saidiyyah', 'Salafi', 'Gunungpati', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1251.4335052643003!2d110.380794858566!3d-7.025821841959257!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708bad063e7263%3A0xfee3a0e13ec3fd3b!2sPon%20Pes%20Roudlotus%20Saidiyyah%20Kalialang!5e1!3m2!1sid!2sid!4v1747405456716!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', NULL, NULL, '6829c29908533.jpeg', 3430, 1997, '', '', '', 'https://roudlotussaidiyyah.sch.id', 'pending', NULL, '', '', NULL, NULL, NULL, '', '2025-05-09 14:31:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Pesantren Darul Falah', 'Tahfidz', 'Semarang', '', '-6.98214400', '110.40997200', '6829292961ca6.jpg', 180, 2002, 'B', '(024) 7654324', 'info@darulfalah.id', 'www.darulfalah.id', 'pending', NULL, '081234567894', 'Pesantren tahfidz dengan program hafalan 30 juz.', 'Pesantren tahfidz dengan program hafalan 30 juz.', 'Tahfidz pesantren with a 30-juz memorization program.', 'Pondok pesantren tahfidz dengan program hafazan 30 juz.', 'Masjid, ruang tahfidz, asrama', '2025-05-10 10:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Pesantren Al-Anwar', 'Salafi', 'Sarang, Rembang', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15849.387867601698!2d111.64296315450628!3d-6.727451929283867!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e771ace2f3f0cd3%3A0x31ad5492fb83c4be!2sPONDOK%20PESANTREN%20MANHALUL%20FURQON%20AL-ANWAR%202%20(PROGRAM%20TAHFIDZ)!5e0!3m2!1sid!2sid!4v1747572209125!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '-6.71414400', '111.64597200', '6829d6218de8e.png', 4000, 1967, 'A', '(0295) 123456', 'info@alanwar.id', 'https://www.ppalanwar.com/', 'verified', NULL, '081234567895', 'Pesantren salafi terkenal dengan pengajaran ulama besar.', 'Pesantren salafi terkenal dengan pengajaran ulama besar.', 'Renowned salafi pesantren with teachings from great scholars.', 'Pondok pesantren salafi terkenal dengan pengajaran ulama besar.', 'Masjid besar, asrama, perpustakaan kitab kuning', '2025-05-10 11:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Pesantren Nurul Huda', 'Modern', 'Ngaliyan, Semarang', '', '-6.99314400', '110.35597200', '682929dc9db65.jpeg', 220, 2008, 'B', '(024) 7654325', 'info@nurulhuda.id', 'www.nurulhuda.id', 'verified', NULL, '081234567896', 'Pesantren modern dengan fokus pada teknologi dan kewirausahaan.', 'Pesantren modern dengan fokus pada teknologi dan kewirausahaan.', 'Modern pesantren focusing on technology and entrepreneurship.', 'Pondok pesantren moden dengan tumpuan pada teknologi dan keusahawanan.', 'Lab komputer, inkubator bisnis, masjid', '2025-05-10 12:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Pesantren Al-Munawwar', 'Tahfidz', 'Demak', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126731.03556501555!2d110.27069384663127!3d-6.968580748716969!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708be1f53663cb%3A0x3040b1c6818f2b82!2sAsrama%20Putri%20Pondok%20Pesantren%20al-munawwar%20ngaliyan!5e0!3m2!1sid!2sid!4v1747572319482!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '-6.89414400', '110.63997200', '6829d6947f71a.jpg', 300, 1998, 'A', '(024) 7654326', 'info@almunawwar.id', 'www.almunawwar.id', 'verified', NULL, '081234567897', 'Pesantren tahfidz dengan metode pengajaran interaktif.', 'Pesantren tahfidz dengan metode pengajaran interaktif.', 'Tahfidz pesantren with interactive teaching methods.', 'Pondok pesantren tahfidz dengan kaedah pengajaran interaktif.', 'Masjid, ruang multimedia, asrama', '2025-05-10 13:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Pesantren Darul Hikmah', 'Riset', 'Ngaliyan, Semarang', '', '-6.99514400', '110.35797200', '68271626daa54.jpg', 170, 2010, 'B', '(024) 7654327', 'info@darulhikmah.id', 'www.darulhikmah.id', 'pending', NULL, '081234567898', 'Pesantren riset dengan fokus pada sains dan teknologi Islam.', 'Pesantren riset dengan fokus pada sains dan teknologi Islam.', 'Research pesantren focusing on Islamic science and technology.', 'Pondok pesantren penyelidikan dengan tumpuan pada sains dan teknologi Islam.', 'Lab sains, perpustakaan digital, masjid', '2025-05-10 14:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Al Mubarokah', 'Riset', 'Tegal', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m10!1m8!1m3!1d366.06372524335546!2d110.35297021725808!3d-6.977743584173832!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sid!2sid!4v1747100267009!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', NULL, NULL, NULL, 100, 2004, 'A', '083128645918', 'Budi123@gmail.com', 'https://roudlotussaidiyyah.sch.id/homepage/index.php', 'pending', NULL, '083128645918', 'mnbvftyuj', NULL, NULL, NULL, 'Wifi,Masjid,Lapangan,Kolam Renang,Gedung Pencakar Langit,Perpustakaan,Lab Komputer dengan spesifikasi tercanggih,ddorone', '2025-05-12 19:17:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Pondok Pesantren Sunan Drajat', 'Salafi', 'Lamongan', '', NULL, NULL, '682715b3a2f61.jpg', 6000, 1977, 'A', '+62 322 3326799', '', 'https://ppsd.id/', 'pending', NULL, '', '', NULL, NULL, NULL, '', '2025-05-16 10:38:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Pondok Pesantren Sidogiri', 'Salafi', 'Sidogiri, Kecamatan Kraton, Jawa Timur', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3351.749108248678!2d112.8344368242046!3d-7.6683958258938505!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7cf8a55ba41ed%3A0x95a2e14c282bc918!2sPondok%20Pesantren%20Sidogiri!5e1!3m2!1sid!2sid!4v1747405502771!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', NULL, NULL, '68271a3b405a2.jpg', 11000, 1718, 'A', '(031) 8961234', '', 'https://sidogiri.net/', 'pending', NULL, '', 'Sidogiri adalah salah satu pesantren tertua di Indonesia, terkenal dengan pengajaran kitab kuning menggunakan metode al-Miftah. Pesantren ini menerapkan sistem salafi dengan fokus pada pendidikan agama Islam dan manajemen ekonomi syariah. Setiap tahun, Sidogiri mengirimkan ratusan guru dan daâ€™i ke berbagai daerah terpencil.', NULL, NULL, NULL, '', '2025-05-16 10:58:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Pondok Pesantren Lirboyo', 'Salafi', 'Lirboyo, Kec. Mojoroto, Kota Kediri, Jawa Timur', '', NULL, NULL, '68271b6e64be4.jpg', 47138, 1910, '', '+62 354 773608', '', 'https://lirboyo.net/', 'pending', NULL, '', ' Lirboyo adalah pesantren salafi terkemuka yang didirikan oleh Kyai Sholeh. Pesantren ini fokus pada kajian kitab kuning dan telah berkembang sebagai pusat penyebaran Islam di Indonesia, di bawah naungan Nahdlatul Ulama (NU).', NULL, NULL, NULL, '', '2025-05-16 11:03:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Pondok Pesantren Darunnajah', 'Modern', 'Kec. Pesanggrahan, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta', '', NULL, NULL, '68271cc00d7ba.jpeg', 11926, 1972, 'A', '+62 21 7350187', '', 'https://darunnajah.com/', 'pending', NULL, '', 'Darunnajah mengusung kurikulum terpadu dengan pengajaran intensif bahasa Arab dan Inggris. Pesantren ini bertujuan mencetak pemimpin umat yang berakhlak mulia dan berpikir kritis berdasarkan Al-Qurâ€™an dan Sunnah.', NULL, NULL, NULL, '', '2025-05-16 11:08:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Pondok Pesantren Tebuireng', 'Modern', 'Jombang, Jawa Timur', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3352.2271520449544!2d112.23589067420366!3d-7.6074629751949665!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e786a7603f1d77b%3A0x71029570607e579e!2sPondok%20Pesantren%20Tebuireng%20Jombang!5e1!3m2!1sid!2sid!4v1747404161691!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', NULL, NULL, '68271defe3bd9.jpeg', 0, 1899, '', ' +62 321 867866', '', 'tebuireng.online', 'pending', NULL, '', 'Didirikan oleh KH Hasyim Asyâ€™ari, Tebuireng adalah pesantren modern dengan berbagai unit pendidikan, termasuk universitas dan sekolah sains. Pesantren ini telah melahirkan banyak ulama besar, seperti pendiri NU dan Muhammadiyah.', NULL, NULL, NULL, '', '2025-05-16 11:13:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promosi`
--

CREATE TABLE `promosi` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `pengguna_id` int(11) DEFAULT NULL,
  `pesantren_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promosi`
--

INSERT INTO `promosi` (`id`, `judul`, `deskripsi`, `gambar`, `status`, `pengguna_id`, `pesantren_id`, `created_at`) VALUES
(2, 'Pendaftaran Santri Baru Gontor', 'Buka pendaftaran santri baru untuk tahun ajaran 2025-2026.', 'promo_gontor.jpg', 'published', 25, 3, '2025-05-11 08:00:00'),
(3, 'Program Unggulan Al-Mansyur', 'Kenali program riset dan teknologi kami.', 'promo_almansyur.jpg', 'draft', 26, 4, '2025-05-11 09:00:00'),
(4, 'Darul Falah: Hafal Al-Qur\'an', 'Program tahfidz intensif untuk semua usia.', 'promo_darulfalah.jpg', 'published', 27, 6, '2025-05-11 10:00:00'),
(5, 'Al-Anwar: Tradisi dan Keilmuan', 'Pesantren salafi dengan pengajaran mendalam.', 'promo_alanwar.jpg', 'published', 28, 7, '2025-05-12 08:00:00'),
(6, 'Nurul Huda: Teknologi dan Islam', 'Gabung dengan program teknologi kami.', 'promo_nurulhuda.jpg', 'draft', 29, 8, '2025-05-12 09:00:00'),
(7, 'Al-Munawwar: Hafalan Interaktif', 'Metode tahfidz modern dan interaktif.', 'promo_almunawwar.jpg', 'published', 30, 9, '2025-05-12 10:00:00'),
(8, 'Darul Hikmah: Sains dan Islam', 'Riset sains berbasis nilai-nilai Islam.', 'promo_darulhikmah.jpg', 'published', 31, 10, '2025-05-12 11:00:00'),
(9, 'Al-Hidayah: Pendidikan Berkualitas', 'Bergabunglah dengan program pendidikan kami.', 'promo_alhidayah.jpg', 'published', 32, 1, '2025-05-12 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `thread`
--

CREATE TABLE `thread` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `suka` int(11) DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thread`
--

INSERT INTO `thread` (`id`, `pengguna_id`, `judul`, `isi`, `created_at`, `suka`, `gambar`) VALUES
(1, 2, 'Tanpa Judul', 'Assalamualaikum Baraya,bagaimana kabarnya hari ini\r\nSemoga kita semua diberi kesehatan dan rejeki yang berlimpah aamiin', '2025-05-09 12:36:08', 0, NULL),
(3, 4, 'Salam sehat', 'hali semuanya', '2025-05-10 18:44:07', 4, NULL),
(4, 2, 'Tips Efektif Menghafal Al-Qur\'an untuk Santri', 'Assalamu\'alaikum, teman-teman santri! Saya ingin berbagi beberapa tips yang membantu saya menghafal Al-Qur\'an lebih cepat dan kuat. Pertama, selalu mulai dengan niat ikhlas dan doa. Kedua, gunakan metode tikrar (pengulangan) dengan membaca ayat 5-10 kali sebelum beralih ke ayat berikutnya. Ketiga, manfaatkan waktu subuh untuk menghafal karena pikiran masih segar. Terakhir, jangan lupa minta bimbingan dari ustadz untuk memperbaiki makhraj dan tajwid. Apa tips kalian dalam menghafal? Yuk, sharing di kolom balasan!', '2025-05-14 15:34:03', 3, 'thread_1747211643_6824557bbf6da.jpg'),
(5, 16, 'Pengalaman di Pesantren', 'Saya ingin berbagi pengalaman seru selama di pesantren. Apa momen favorit kalian?', '2025-05-11 08:00:00', 15, 'thread_pesantren.jpg'),
(6, 17, 'Tips Menghafal Al-Qur\'an', 'Saya suka menghafal di waktu subuh, kalian punya tips lain untuk hafalan?', '2025-05-11 09:00:00', 12, 'thread_tahfidz.jpg'),
(7, 18, 'Kisah Inspiratif Santri', 'Cerita tentang perjuangan temen santri yang jadi hafidz bikin saya termotivasi!', '2025-05-11 10:00:00', 20, NULL),
(8, 19, 'Belajar Tajwid dengan Mudah', 'Ada yang punya cara seru untuk belajar tajwid? Saya masih bingung dengan makhraj.', '2025-05-11 11:00:00', 10, NULL),
(9, 20, 'Teknologi di Pesantren', 'Menurut kalian, seberapa penting teknologi di pesantren jaman sekarang?', '2025-05-12 08:00:00', 15, 'thread_teknologi.jpg'),
(10, 21, 'Ceramah Favorit', 'Baru denger ceramah dari ustadz di pesantren, bikin hati adem. Kalian suka tema apa?', '2025-05-12 09:00:00', 18, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `thread_likes`
--

CREATE TABLE `thread_likes` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `pengguna_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_log`
--
ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kegiatan_pengguna_fk` (`pengguna_id`),
  ADD KEY `pesantren_id` (`pesantren_id`),
  ADD KEY `idx_kegiatan_judul` (`judul`);

--
-- Indexes for table `kegiatan_komentar`
--
ALTER TABLE `kegiatan_komentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kegiatan_id` (`kegiatan_id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indexes for table `kegiatan_likes`
--
ALTER TABLE `kegiatan_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`kegiatan_id`,`pengguna_id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indexes for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`),
  ADD KEY `pesantren_id` (`pesantren_id`);

--
-- Indexes for table `kolaborasi_feedback`
--
ALTER TABLE `kolaborasi_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kolaborasi_id` (`kolaborasi_id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indexes for table `pengelola_pesantren`
--
ALTER TABLE `pengelola_pesantren`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`),
  ADD KEY `pesantren_id` (`pesantren_id`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pesantren`
--
ALTER TABLE `pesantren`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pesantren_nama` (`nama`);

--
-- Indexes for table `promosi`
--
ALTER TABLE `promosi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`),
  ADD KEY `pesantren_id` (`pesantren_id`),
  ADD KEY `idx_promosi_judul` (`judul`);

--
-- Indexes for table `thread`
--
ALTER TABLE `thread`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- Indexes for table `thread_likes`
--
ALTER TABLE `thread_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`thread_id`,`pengguna_id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_log`
--
ALTER TABLE `admin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kegiatan_komentar`
--
ALTER TABLE `kegiatan_komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `kegiatan_likes`
--
ALTER TABLE `kegiatan_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kolaborasi_feedback`
--
ALTER TABLE `kolaborasi_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengelola_pesantren`
--
ALTER TABLE `pengelola_pesantren`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `pesantren`
--
ALTER TABLE `pesantren`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `promosi`
--
ALTER TABLE `promosi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `thread`
--
ALTER TABLE `thread`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `thread_likes`
--
ALTER TABLE `thread_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD CONSTRAINT `kegiatan_ibfk_1` FOREIGN KEY (`pesantren_id`) REFERENCES `pesantren` (`id`),
  ADD CONSTRAINT `kegiatan_pengguna_fk` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `kegiatan_komentar`
--
ALTER TABLE `kegiatan_komentar`
  ADD CONSTRAINT `kegiatan_komentar_ibfk_1` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`),
  ADD CONSTRAINT `kegiatan_komentar_ibfk_2` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `kolaborasi`
--
ALTER TABLE `kolaborasi`
  ADD CONSTRAINT `kolaborasi_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `kolaborasi_ibfk_2` FOREIGN KEY (`pesantren_id`) REFERENCES `pesantren` (`id`);

--
-- Constraints for table `kolaborasi_feedback`
--
ALTER TABLE `kolaborasi_feedback`
  ADD CONSTRAINT `kolaborasi_feedback_ibfk_1` FOREIGN KEY (`kolaborasi_id`) REFERENCES `kolaborasi` (`id`),
  ADD CONSTRAINT `kolaborasi_feedback_ibfk_2` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `thread` (`id`),
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);

--
-- Constraints for table `pengelola_pesantren`
--
ALTER TABLE `pengelola_pesantren`
  ADD CONSTRAINT `pengelola_pesantren_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `pengelola_pesantren_ibfk_2` FOREIGN KEY (`pesantren_id`) REFERENCES `pesantren` (`id`);

--
-- Constraints for table `promosi`
--
ALTER TABLE `promosi`
  ADD CONSTRAINT `promosi_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`),
  ADD CONSTRAINT `promosi_ibfk_2` FOREIGN KEY (`pesantren_id`) REFERENCES `pesantren` (`id`);

--
-- Constraints for table `thread`
--
ALTER TABLE `thread`
  ADD CONSTRAINT `thread_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
