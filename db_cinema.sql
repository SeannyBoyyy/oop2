-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 09:14 PM
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
-- Database: `db_cinema`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `admin_id` int(11) NOT NULL,
  `admin_firstname` varchar(50) NOT NULL,
  `admin_lastname` varchar(50) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`admin_id`, `admin_firstname`, `admin_lastname`, `admin_email`, `admin_password`, `created_at`) VALUES
(1, 'cyrus', 'ballon', 'cyrus@gmail.com', '$2y$10$DETsKca1kwNRkfr7VLn0kuydHGUsRpRJfdpZiT5d/huMYh9I9rhAW', '2025-03-21 13:04:32'),
(2, 'sean', 'sean', 'sean@gmail.com', '$2y$10$s24II3s5z4Z5zdYYobjCIee6Ct8NVetRbxaja4Ss9Z/CjphALgDSO', '2025-03-21 13:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cinema`
--

CREATE TABLE `tbl_cinema` (
  `cinema_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` text NOT NULL,
  `total_screens` int(11) NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cinema_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cinema`
--

INSERT INTO `tbl_cinema` (`cinema_id`, `owner_id`, `name`, `location`, `total_screens`, `status`, `created_at`, `cinema_image`) VALUES
(1, 1, 'sean seans', 'seans', 50, 'open', '2025-03-22 06:48:11', '../cinema/uploads/profile/1742626190_Blackberries.jpg'),
(2, 2, 'Test VEGE', 'Vege', 3, 'open', '2025-03-22 20:07:38', '../cinema/uploads/profile/1742674058_405003613_122103692432123064_6837169345888278592_n.jpg'),
(3, 4, 'aaa', 'aaa', 5, 'closed', '2025-03-22 20:14:02', '../cinema/uploads/profile/1742674442_Cherries.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cinema_owner`
--

CREATE TABLE `tbl_cinema_owner` (
  `owner_id` int(11) NOT NULL,
  `owner_firstname` varchar(100) NOT NULL,
  `owner_lastname` varchar(100) NOT NULL,
  `owner_email` varchar(100) NOT NULL,
  `owner_address` text NOT NULL,
  `owner_password` varchar(255) NOT NULL,
  `cinema_name` varchar(100) NOT NULL,
  `dti_permit` varchar(50) NOT NULL,
  `mayor_permit` varchar(50) NOT NULL,
  `sanitary_permit` varchar(50) NOT NULL,
  `verification_status` enum('verified','unverified') DEFAULT 'unverified',
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cinema_owner`
--

INSERT INTO `tbl_cinema_owner` (`owner_id`, `owner_firstname`, `owner_lastname`, `owner_email`, `owner_address`, `owner_password`, `cinema_name`, `dti_permit`, `mayor_permit`, `sanitary_permit`, `verification_status`, `status`, `created_at`) VALUES
(1, 'sean', 'sean', 'sean@gmail.com', 'test test', '$2y$10$ib7frZAbpFxJaplEVuITi.gMg1ggVd2Du5us8pdt2mdai8JdFTV0S', 'sean', '../cinema/uploads/permits/dti_1742621780_AdminIcon', '../cinema/uploads/permits/mayor_1742621780_Blackbe', '../cinema/uploads/permits/sanitary_1742621780_BANA', 'verified', 'active', '2025-03-22 05:36:20'),
(2, 'Test', 'tst', 'testonly@gmail.com', 'test test', '$2y$10$75dMJ.PXObKdGr6xZKNWvuMHS0c8u0RfX7ndqFv0G5WPcmOzUFy8m', 'test', '../cinema/uploads/permits/dti_1742627571_10897_z_2', '../cinema/uploads/permits/mayor_1742627571_1-More-', '../cinema/uploads/permits/sanitary_1742627571_farm', 'verified', 'active', '2025-03-22 07:12:51'),
(4, 'aaa', 'aaa', 'aaa@gmail.com', 'aaa', '$2y$10$PeRJi4UGNp8iFLGkgLO5S.GpZBTP.N0H4ZCD3b87T.6klqOvAjezm', 'aaa', '../cinema/uploads/permits/dti_1742674420_Cherries.', '../cinema/uploads/permits/mayor_1742674420_blueber', '../cinema/uploads/permits/sanitary_1742674420_Blac', 'verified', 'active', '2025-03-22 20:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_foodpartner`
--

CREATE TABLE `tbl_foodpartner` (
  `partner_id` int(11) NOT NULL,
  `partner_firstname` varchar(100) NOT NULL,
  `partner_lastname` varchar(100) NOT NULL,
  `partner_email` varchar(100) NOT NULL,
  `partner_address` text NOT NULL,
  `partner_password` varchar(255) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `dti_permit` varchar(50) NOT NULL,
  `mayor_permit` varchar(50) NOT NULL,
  `sanitary_permit` varchar(50) NOT NULL,
  `verification_status` enum('verified','unverified') DEFAULT 'unverified',
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_foodpartner`
--

INSERT INTO `tbl_foodpartner` (`partner_id`, `partner_firstname`, `partner_lastname`, `partner_email`, `partner_address`, `partner_password`, `business_name`, `dti_permit`, `mayor_permit`, `sanitary_permit`, `verification_status`, `status`, `created_at`) VALUES
(1, 'cyrus', 'ballon', 'cyrus@gmail.com', 'St Donar 27', '$2y$10$EYIZIitDUgPcZELdFoELUuf3AdZPOZwBmY.bzdPw8wvokJ18m03Wm', 'Potato Shop', '../foodpartner/uploads/permits/dti_1742560787_imag', '../foodpartner/uploads/permits/mayor_1742560787_so', '../foodpartner/uploads/permits/sanitary_1742560787', 'verified', 'active', '2025-03-21 12:39:47'),
(2, 'sean', 'sean', 'sean@gmail.com', 'test test', '$2y$10$/Gq9fobUENHP.FoP.PiUhuhuIvB9c.M8lDD5Z0QsdHj7duZ5aGiZ.', 'Food Business Test', '../foodpartner/uploads/permits/dti_1742563774_blue', '../foodpartner/uploads/permits/mayor_1742563774_Bl', '../foodpartner/uploads/permits/sanitary_1742563774', 'verified', 'active', '2025-03-21 13:29:34');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_movies`
--

CREATE TABLE `tbl_movies` (
  `movie_id` int(11) NOT NULL,
  `cinema_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `genre` varchar(100) NOT NULL,
  `rating` varchar(10) NOT NULL,
  `duration` int(11) NOT NULL,
  `poster_url` varchar(255) NOT NULL,
  `release_date` date NOT NULL,
  `status` enum('now showing','coming soon') DEFAULT 'now showing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_movies`
--

INSERT INTO `tbl_movies` (`movie_id`, `cinema_id`, `title`, `description`, `genre`, `rating`, `duration`, `poster_url`, `release_date`, `status`, `created_at`) VALUES
(2, 1, 'Test', 'test', 'test', 'PG-13', 109, 'uploads/1742630178_432388957_1555647095216535_3313182111771792286_n.jpg', '2025-03-29', 'coming soon', '2025-03-22 07:56:18');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_showtimes`
--

CREATE TABLE `tbl_showtimes` (
  `showtime_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `cinema_id` int(11) NOT NULL,
  `screen_number` int(11) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `show_date` date NOT NULL,
  `show_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_showtimes`
--

INSERT INTO `tbl_showtimes` (`showtime_id`, `movie_id`, `cinema_id`, `screen_number`, `total_seats`, `price`, `show_date`, `show_time`) VALUES
(3, 2, 1, 3, 70, 280.00, '2025-03-27', '03:56:00'),
(4, 2, 1, 1, 290, 300.00, '2025-03-24', '06:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_firstname` varchar(50) NOT NULL,
  `user_lastname` varchar(50) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_contact_number` varchar(20) DEFAULT NULL,
  `user_address` text DEFAULT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_firstname`, `user_lastname`, `user_email`, `user_password`, `user_contact_number`, `user_address`, `status`, `created_at`) VALUES
(1, 'cyrus', 'ballon', 'cyrus@gmail.com', '$2y$10$uMFp6AIR2VDh5/.mzZm2/uLAoOLKaBqTwXkoe5NwoiLao2McOMfKi', '09493006003', '283 G Donar St.', 'active', '2025-03-21 13:09:04'),
(2, 'sean', 'sean', 'sean@gmail.com', '$2y$10$/csPn5m/IGWd1rbMe5XiXupFw2PDIaUYLHb.Rm0Oo9lPnrsncp5mm', 'aa', 'test test', 'active', '2025-03-21 13:25:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `tbl_cinema`
--
ALTER TABLE `tbl_cinema`
  ADD PRIMARY KEY (`cinema_id`),
  ADD UNIQUE KEY `owner_id` (`owner_id`);

--
-- Indexes for table `tbl_cinema_owner`
--
ALTER TABLE `tbl_cinema_owner`
  ADD PRIMARY KEY (`owner_id`),
  ADD UNIQUE KEY `owner_email` (`owner_email`);

--
-- Indexes for table `tbl_foodpartner`
--
ALTER TABLE `tbl_foodpartner`
  ADD PRIMARY KEY (`partner_id`),
  ADD UNIQUE KEY `partner_email` (`partner_email`);

--
-- Indexes for table `tbl_movies`
--
ALTER TABLE `tbl_movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  ADD PRIMARY KEY (`showtime_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `cinema_id` (`cinema_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_cinema`
--
ALTER TABLE `tbl_cinema`
  MODIFY `cinema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_cinema_owner`
--
ALTER TABLE `tbl_cinema_owner`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_foodpartner`
--
ALTER TABLE `tbl_foodpartner`
  MODIFY `partner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_movies`
--
ALTER TABLE `tbl_movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  MODIFY `showtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cinema`
--
ALTER TABLE `tbl_cinema`
  ADD CONSTRAINT `tbl_cinema_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `tbl_cinema_owner` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  ADD CONSTRAINT `tbl_showtimes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `tbl_movies` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_showtimes_ibfk_2` FOREIGN KEY (`cinema_id`) REFERENCES `tbl_cinema` (`cinema_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
