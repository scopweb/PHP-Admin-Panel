-- PHP Admin Panel - Database Schema
-- Updated for PHP 8+ with secure password hashing
-- Version: 2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `your_db_name`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `sr_Id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adm_Id` varchar(55) NOT NULL,
  `adm_name` varchar(100) NOT NULL,
  `adm_username` varchar(255) NOT NULL,
  `adm_email` varchar(255) DEFAULT NULL,
  `adm_password` varchar(255) NOT NULL COMMENT 'Stores bcrypt hash (60+ chars)',
  `adm_status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `adm_type` enum('Super','Normal') NOT NULL DEFAULT 'Normal',
  `reset_token` varchar(64) DEFAULT NULL COMMENT 'Password reset token',
  `reset_expires` datetime DEFAULT NULL COMMENT 'Token expiration time',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sr_Id`),
  UNIQUE KEY `idx_username` (`adm_username`),
  UNIQUE KEY `idx_adm_id` (`adm_Id`),
  KEY `idx_reset_token` (`reset_token`),
  KEY `idx_status` (`adm_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Default admin user
-- Username: admin@google.com
-- Password: Pt123456789 (will be upgraded to bcrypt on first login)
--

INSERT INTO `tb_admin` (`sr_Id`, `adm_Id`, `adm_name`, `adm_username`, `adm_email`, `adm_password`, `adm_status`, `adm_type`) VALUES
(1, 'adm_1', 'Admin', 'admin@google.com', 'admin@google.com', 'c3fb37909d398f646387bef207be49b4', 'Active', 'Super');

-- --------------------------------------------------------

--
-- Migration script for existing databases
-- Run this if upgrading from previous version
--

-- ALTER TABLE `tb_admin`
--   ADD COLUMN `adm_email` varchar(255) DEFAULT NULL AFTER `adm_username`,
--   ADD COLUMN `reset_token` varchar(64) DEFAULT NULL AFTER `adm_type`,
--   ADD COLUMN `reset_expires` datetime DEFAULT NULL AFTER `reset_token`,
--   ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `reset_expires`,
--   ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
--   MODIFY `adm_password` varchar(255) NOT NULL,
--   ADD INDEX `idx_reset_token` (`reset_token`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
