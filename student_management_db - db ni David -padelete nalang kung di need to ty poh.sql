-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2026 at 02:54 AM
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
-- Database: `student_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_table`
--

CREATE TABLE `admin_table` (
  `admin_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_table`
--

INSERT INTO `admin_table` (`admin_id`, `fullname`, `username`, `email`, `password_hash`) VALUES
(3, 'TestAdmin', 'TestAdmin', 'testadmin123@gmail.com', '$2y$10$32PDyDACzB05Hz.2mPauv.9d31phDFcKbakkpKIPGiwUM6AFh0cNW'),
(4, 'David Santillan', 'Gay Kairo', 'kairobading123@gmail.com', '$2y$10$yujINTXQETWQFHlw8RfZXuT/bdcmhODhNQ/EarlekRWwqLnCgVNjC'),
(5, 'IShowSpeed', 'Speed', 'speedy123@gmail.com', '$2y$10$rmuHtYpJHZWe7vmkS.olyuHE31ir4FE..1Qf9k478PGaCQe/QVd9K');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uploaded_files`
--

INSERT INTO `uploaded_files` (`file_id`, `user_id`, `filename`, `file_path`, `upload_date`) VALUES
(1, 7, 'egames logo.jpg', 'uploads/464f37dc8a814a939ec8301887322103.jpg', '2026-10-11 21:20:39'),
(2, 7, 'Python Basic 1.pdf', 'uploads/16580891c10ef348b8001aa2b5eff110.pdf', '2026-10-11 21:21:11'),
(3, 8, 'doctor.jpg', 'uploads/6ce45ae2902b26382ae81b9c74ab3295.jpg', '2026-10-11 23:13:46'),
(4, 10, 'era_personal_body_graal2018452-253.png', 'uploads/2cb3d0031bdffd9120668b4a1f8d22e3.png', '2026-10-12 23:23:57'),
(5, 7, 'classic_personal_body_graal4065824-1561-1_7.png', 'uploads/8a8814660e6dc515812f8002d85374d5.png', '2026-10-13 08:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`user_id`, `fullname`, `username`, `email`, `password_hash`, `created_date`) VALUES
(7, 'testUser', 'testuser', 'testuser123@gmail.com', '$2y$10$nLgtPHJs7q0peBh6Wgp8Fu4aTzWREAzPsdOajGuS9T9og4F.XANga', '2026-10-10 11:44:53'),
(8, 'kairo santillan', 'hino', 'tikolmaster@gmail.com', '$2y$10$D2GUdQfhWphs86R0rPSSkeCvzXsl/PTpTbu51Lp5RPzfMznsysxfK', '2026-10-11 23:06:41'),
(10, 'David Dela Pena', 'Cryptic', 'davidemail123@gmail.com', '$2y$10$Q7SV0HUGx.aoSojPdc1FX.q2AswAK/m59.18/cgq/8EYHWcYbTo92', '2026-10-12 23:06:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `uploaded_files_ibfk_1` (`user_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_table`
--
ALTER TABLE `admin_table`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_table` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
