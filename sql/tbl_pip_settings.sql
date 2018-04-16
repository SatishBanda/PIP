-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2018 at 08:34 AM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pip`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pip_settings`
--

CREATE TABLE `tbl_pip_settings` (
  `setting_id` int(11) NOT NULL,
  `twilio_phone_number` varchar(55) NOT NULL,
  `account_id` varchar(55) NOT NULL,
  `account_auth_token` varchar(255) NOT NULL,
  `send_grid_api` varchar(255) NOT NULL,
  `default_email_for_outgoing` varchar(255) NOT NULL,
  `alerts_notification_days` varchar(55) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_pip_settings`
--

INSERT INTO `tbl_pip_settings` (`setting_id`, `twilio_phone_number`, `account_id`, `account_auth_token`, `send_grid_api`, `default_email_for_outgoing`, `alerts_notification_days`, `updated_by`, `updated_at`) VALUES
(1, '(321) 321-3131', 'ACff3e601d4b1d472297c1d40b1ef15906', '1da17a0f6598f9812ff4a30bf6288ff4', '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', 'Support@phonepro.com', '1', 0, '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_pip_settings`
--
ALTER TABLE `tbl_pip_settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_pip_settings`
--
ALTER TABLE `tbl_pip_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
