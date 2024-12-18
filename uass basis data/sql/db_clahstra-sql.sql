-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_clahstra
CREATE DATABASE IF NOT EXISTS `db_clahstra` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_clahstra`;

-- Dumping structure for table db_clahstra.bandara
CREATE TABLE IF NOT EXISTS `bandara` (
  `id_bandara` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `kota` varchar(100) NOT NULL,
  `negara` varchar(100) NOT NULL,
  PRIMARY KEY (`id_bandara`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.bandara: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.customer
CREATE TABLE IF NOT EXISTS `customer` (
  `id_customer` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_pasport` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.customer: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.level
CREATE TABLE IF NOT EXISTS `level` (
  `level_id` int NOT NULL AUTO_INCREMENT,
  `level_nama` int DEFAULT NULL,
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.level: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.pembayaran
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` int NOT NULL AUTO_INCREMENT,
  `id_pemesanan` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `jenis_pembayaran` enum('Kartu Kredit','Transfer Bank','E-Wallet') NOT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_pemesanan` (`id_pemesanan`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.pembayaran: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.pemesanan
CREATE TABLE IF NOT EXISTS `pemesanan` (
  `id_pemesanan` int NOT NULL AUTO_INCREMENT,
  `tanggal_pesan` date NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') NOT NULL,
  `id_customer` int NOT NULL,
  `id_penerbangan` int NOT NULL,
  PRIMARY KEY (`id_pemesanan`),
  KEY `id_customer` (`id_customer`),
  KEY `id_penerbangan` (`id_penerbangan`),
  CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`id_customer`) REFERENCES `customer` (`id_customer`),
  CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`id_penerbangan`) REFERENCES `penerbangan` (`id_penerbangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.pemesanan: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.penerbangan
CREATE TABLE IF NOT EXISTS `penerbangan` (
  `id_penerbangan` int NOT NULL AUTO_INCREMENT,
  `nomor_penerbangan` varchar(20) NOT NULL,
  `jam_berangkat` time NOT NULL,
  `jam_kedatangan` time NOT NULL,
  `asal_penerbangan` int NOT NULL,
  `tujuan_penerbangan` int NOT NULL,
  PRIMARY KEY (`id_penerbangan`),
  KEY `asal_penerbangan` (`asal_penerbangan`),
  KEY `tujuan_penerbangan` (`tujuan_penerbangan`),
  CONSTRAINT `penerbangan_ibfk_1` FOREIGN KEY (`asal_penerbangan`) REFERENCES `bandara` (`id_bandara`),
  CONSTRAINT `penerbangan_ibfk_2` FOREIGN KEY (`tujuan_penerbangan`) REFERENCES `bandara` (`id_bandara`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.penerbangan: ~0 rows (approximately)

-- Dumping structure for table db_clahstra.user
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` int DEFAULT NULL,
  `password` int DEFAULT NULL,
  `level_id` int DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `Index 2` (`level_id`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `level` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_clahstra.user: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
