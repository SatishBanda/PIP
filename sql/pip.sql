-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2018 at 11:09 AM
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
-- Table structure for table `tbl_pip_users`
--

CREATE TABLE `tbl_pip_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `profile_image` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `user_type` int(11) NOT NULL COMMENT 'Super Admin =1;Candidate=2;Staff=3',
  `role` tinyint(4) DEFAULT NULL,
  `auth_key` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '0',
  `is_verified` tinyint(4) NOT NULL DEFAULT '0',
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  `last_logged_at` datetime NOT NULL,
  `access_token_expired_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for User details' ROW_FORMAT=COMPACT;

--
-- Dumping data for table `tbl_pip_users`
--

INSERT INTO `tbl_pip_users` (`user_id`, `username`, `first_name`, `last_name`, `mobile`, `profile_image`, `user_type`, `role`, `auth_key`, `password`, `password_reset_token`, `password_requested_at`, `is_active`, `is_verified`, `is_delete`, `last_logged_at`, `access_token_expired_at`, `last_login_ip`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'admin@pip.com', 'Admin', 'Admin', '123456789', NULL, 1, NULL, 'dVN8fzR_KzJ_lBrymfXI6qyH2QzyXYUU', '$2y$13$Tj4SraBdEg1ADYNTXghw1ecVjCnAdqHt4EgtbX2TSzrbqlP7./JPy', NULL, NULL, 1, 1, 0, '2018-04-10 14:35:38', '2018-04-11 05:05:38', '::1', '0000-00-00 00:00:00', 0, '2018-04-10 05:05:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pip_user_permissions`
--

CREATE TABLE `tbl_pip_user_permissions` (
  `id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for User Permissions' ROW_FORMAT=COMPACT;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_pip_users`
--
ALTER TABLE `tbl_pip_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tbl_pip_user_permissions`
--
ALTER TABLE `tbl_pip_user_permissions`
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_pip_users`
--
ALTER TABLE `tbl_pip_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_pip_user_permissions`
--
ALTER TABLE `tbl_pip_user_permissions`
  ADD CONSTRAINT `tbl_pip_user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_pip_users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
