-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 06, 2026 at 08:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `updated_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `egc_acc_tiderc_drac`
--

CREATE TABLE `egc_acc_tiderc_drac` (
  `sno` int(11) NOT NULL,
  `tiderc_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `b_name` text DEFAULT NULL,
  `rd_prsn_name` text DEFAULT NULL,
  `rd_name` text DEFAULT NULL,
  `rd_prt1` int(4) DEFAULT NULL,
  `rd_prt2` int(4) DEFAULT NULL,
  `rd_prt3` int(4) DEFAULT NULL,
  `rd_prt4` int(4) DEFAULT NULL,
  `rd_prt5` int(4) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `due` date DEFAULT NULL,
  `vv_no` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` int(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `egc_acc_tiderc_drac`
--
ALTER TABLE `egc_acc_tiderc_drac`
  ADD PRIMARY KEY (`sno`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `egc_acc_tiderc_drac`
--
ALTER TABLE `egc_acc_tiderc_drac`
  MODIFY `sno` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
