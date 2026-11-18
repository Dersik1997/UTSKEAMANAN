-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 18, 2026 at 03:20 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_keamanan_warung`
--

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_asimetris`
--

CREATE TABLE `pesanan_asimetris` (
  `id` int NOT NULL,
  `nama_pembeli` text NOT NULL,
  `menu_pesanan` text NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `no_hp` text NOT NULL,
  `waktu_pesan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_simetris`
--

CREATE TABLE `pesanan_simetris` (
  `id` int NOT NULL,
  `nama_pembeli` text NOT NULL,
  `menu_pesanan` text NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `no_hp` text NOT NULL,
  `iv` varchar(64) NOT NULL,
  `waktu_pesan` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pesanan_asimetris`
--
ALTER TABLE `pesanan_asimetris`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan_simetris`
--
ALTER TABLE `pesanan_simetris`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pesanan_asimetris`
--
ALTER TABLE `pesanan_asimetris`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan_simetris`
--
ALTER TABLE `pesanan_simetris`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
