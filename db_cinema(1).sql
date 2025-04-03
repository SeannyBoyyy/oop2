-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 12:34 PM
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
(3, 4, 'aaa', 'aaa', 5, 'closed', '2025-03-22 20:14:02', '../cinema/uploads/profile/1742674442_Cherries.jpg'),
(4, 5, 'Cyrus', '28 G DONAR ST.', 3, 'open', '2025-03-23 06:10:00', '../cinema/uploads/profile/1742710200_photoshop.PNG');

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
  `dti_permit` varchar(255) DEFAULT NULL,
  `mayor_permit` varchar(255) DEFAULT NULL,
  `sanitary_permit` varchar(255) DEFAULT NULL,
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
(4, 'aaa', 'aaa', 'aaa@gmail.com', 'aaa', '$2y$10$PeRJi4UGNp8iFLGkgLO5S.GpZBTP.N0H4ZCD3b87T.6klqOvAjezm', 'aaa', '../cinema/uploads/permits/dti_1742674420_Cherries.', '../cinema/uploads/permits/mayor_1742674420_blueber', '../cinema/uploads/permits/sanitary_1742674420_Blac', 'verified', 'active', '2025-03-22 20:13:40'),
(5, 'cyrus', 'ballon', 'cyrus@gmail.com', 'Cinema 127 St.', '$2y$10$ByAX16FhCCx6uBROVl.GFe9mXJJV7wBSqOZrNxX.kaoQaqlMH138m', 'Ayala', '../cinema/uploads/permits/dti_1742709903_images.jp', '../cinema/uploads/permits/mayor_1742709903_Ubuntu-', '../cinema/uploads/permits/sanitary_1742709903_indi', 'verified', 'active', '2025-03-23 06:05:03'),
(6, 'efren', 'Elomina', 'efren@gmail.com', '123', '$2y$10$sB7vQMRt3M0/L1OrUtshWOOzrhaufNmFiH2Pf2yJfCtTF.C3POzFS', '123', '../cinema/uploads/permits/dti_1742737060_individua', '../cinema/uploads/permits/mayor_1742737060_headway', '../cinema/uploads/permits/sanitary_1742737060_1692', 'verified', 'active', '2025-03-23 13:37:40'),
(7, 'dan', 'yasuo', 'dan@gmail.com', '123', '$2y$10$NVzFNzVGflRGOdUp3NLBBuRs6NZtWHbFEXNGvhx6gljl6Fl6z6m86', '123', '../cinema/uploads/permits/dti_1742737646_sony-playstation-netwo.jpg', '../cinema/uploads/permits/mayor_1742737646_photoshop.PNG', '../cinema/uploads/permits/sanitary_1742737646_482736912_1181477930253511_6962346382548741370_n.png', 'verified', 'active', '2025-03-23 13:47:26');

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
  `dti_permit` varchar(255) DEFAULT NULL,
  `mayor_permit` varchar(255) DEFAULT NULL,
  `sanitary_permit` varchar(255) DEFAULT NULL,
  `verification_status` enum('verified','unverified') DEFAULT 'unverified',
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_status` enum('active','expired') DEFAULT 'expired',
  `subscription_expiry` datetime DEFAULT NULL,
  `cinema_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_foodpartner`
--

INSERT INTO `tbl_foodpartner` (`partner_id`, `partner_firstname`, `partner_lastname`, `partner_email`, `partner_address`, `partner_password`, `business_name`, `dti_permit`, `mayor_permit`, `sanitary_permit`, `verification_status`, `status`, `created_at`, `subscription_status`, `subscription_expiry`, `cinema_id`) VALUES
(1, 'cyrus', 'ballon', 'cyrus@gmail.com', 'St Donar 27', '$2y$10$EYIZIitDUgPcZELdFoELUuf3AdZPOZwBmY.bzdPw8wvokJ18m03Wm', 'Potato Shop', '../foodpartner/uploads/permits/dti_1742560787_imag', '../foodpartner/uploads/permits/mayor_1742560787_so', '../foodpartner/uploads/permits/sanitary_1742560787', 'verified', 'active', '2025-03-21 12:39:47', 'expired', NULL, 0),
(2, 'sean', 'sean', 'sean@gmail.com', 'test test', '$2y$10$/Gq9fobUENHP.FoP.PiUhuhuIvB9c.M8lDD5Z0QsdHj7duZ5aGiZ.', 'Food Business Test', '../foodpartner/uploads/permits/dti_1742563774_blue', '../foodpartner/uploads/permits/mayor_1742563774_Bl', '../foodpartner/uploads/permits/sanitary_1742563774', 'verified', 'active', '2025-03-28 15:47:07', 'active', '2026-03-28 16:47:07', 3),
(3, 'efren', 'elomina', 'efren@gmail.com', '123', '$2y$10$blpY/0C/z7nqa1MfEsdhQ.QcVDU8qgT3cL1IA16buuUhmgKjIDeAC', 'PATATAS Shop', '../foodpartner/uploads/permits/dti_1742735738_Ubuntu-linux-cloud-os-2.jpg', '../foodpartner/uploads/permits/mayor_1742735738_sony-playstation-netwo.jpg', '../foodpartner/uploads/permits/sanitary_1742735738_photoshop.PNG', 'verified', 'active', '2025-03-23 13:15:38', 'expired', NULL, 0),
(4, 'trial', 'trial', 'trial3@gmail.com', 'trial', '$2y$10$DfenZ1vFEU3mALFffj3Rt.wKx.2Qre5J5Ug2MiKL.5yYJjjLKcThe', 'trial', '../foodpartner/uploads/permits/dti_1742821685_archi_pix_grid_002.jpg', '../foodpartner/uploads/permits/mayor_1742821685_college_grad.jpg', '../foodpartner/uploads/permits/sanitary_1742821685_comlab3.jpg', 'verified', 'active', '2025-03-27 07:37:18', 'active', '2026-03-27 08:37:18', 2),
(5, 'trial', 'trial', 'trial4@gmail.com', 'trial', '$2y$10$rREEKlfpC45tfiM.mu7co.R74FZzxehN6rjCZ2O4gVZaJ6D4BNtAO', 'trial', '../foodpartner/uploads/permits/dti_1742918981_archi_pix_grid_002.jpg', '../foodpartner/uploads/permits/mayor_1742918981_college_grad.jpg', '../foodpartner/uploads/permits/sanitary_1742918981_comlab3.jpg', 'verified', 'active', '2025-03-25 17:01:44', 'active', '2026-03-25 18:01:43', 4),
(6, 'trial', 'trial', 'trial5@gmail.com', 'test', '$2y$10$cXjxNtT3K5ud6lc0FrlLKuYGy.f6BL.eoXNzVooTnK7dgJeeIqxE6', 'trial', '../foodpartner/uploads/permits/dti_1742922205_college_grad.jpg', '../foodpartner/uploads/permits/mayor_1742922205_archi_pix_grid_002.jpg', '../foodpartner/uploads/permits/sanitary_1742922205_comlab3.jpg', 'verified', 'active', '2025-03-25 17:04:01', 'active', '2026-03-25 18:04:01', 2),
(7, 'trial', 'trial', 'trial0@gmail.com', 'trial', '$2y$10$73hj07NGzmziZnC/YeMdbe.Wvnejf37Ju7t/fUWBgjVgF3SZiT2W.', 'trial', '../foodpartner/uploads/permits/dti_1743056112_Blackberries.jpg', '../foodpartner/uploads/permits/mayor_1743056112_blueberry.jpg', '../foodpartner/uploads/permits/sanitary_1743056112_Cherries.jpg', 'verified', 'active', '2025-03-27 06:15:12', 'expired', NULL, 0),
(8, 'sean', 'sean', 's1@gmail.com', 'test test', '$2y$10$aEziVzgygwgwVhMfBc/1oekWeoezD4ONiDrxFWbPFqhiSgqvTQeSa', 'Food Business Test', '../foodpartner/uploads/permits/dti_1743187354_Blackberries.jpg', '../foodpartner/uploads/permits/mayor_1743187354_blueberry.jpg', '../foodpartner/uploads/permits/sanitary_1743187354_Cherries.jpg', 'verified', 'active', '2025-03-28 18:44:03', 'active', '2026-03-28 19:44:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_foodproducts`
--

CREATE TABLE `tbl_foodproducts` (
  `product_id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_foodproducts`
--

INSERT INTO `tbl_foodproducts` (`product_id`, `partner_id`, `product_name`, `description`, `price`, `category`, `image_url`, `status`, `created_at`) VALUES
(3, 2, 'Banana', 'Fresh Bananas', 20.00, 'Fruits', 'BANANA.jpg', 'available', '2025-03-28 15:48:53'),
(4, 2, 'Berries', 'Fresh Berries', 30.00, 'Fruits', 'Blackberries.jpg', 'available', '2025-03-28 15:49:47'),
(5, 4, 'BlueBerries', 'Fresh BlueBerries', 40.00, 'Fruits', 'blueberry.jpg', 'available', '2025-03-28 15:55:51');

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
(2, 1, 'Test', 'test', 'test', 'PG-13', 109, 'uploads/1742630178_432388957_1555647095216535_3313182111771792286_n.jpg', '2025-03-29', 'coming soon', '2025-03-22 07:56:18'),
(3, 1, 'Captain America: Brave New World', 'Five months after Thaddeus Ross is elected president of the United States, he sends Sam Wilson and Joaquin Torres—the new Captain America and Falcon, respectively—to Oaxaca, Mexico, to stop the illegal sale of classified items stolen by Sidewinder and his mercenary group Serpent. Wilson and Torres recover the items, but Sidewinder escapes. Torres is excited to be taking on Wilson\'s former mantle of Falcon, but Wilson is hesitant to involve Torres in dangerous missions due to the pair not having superpowers like former Captain America Steve Rogers. After the mission, Wilson and Torres train with Isaiah Bradley, a super soldier who was imprisoned and experimented on by the U.S. government.', 'Action', 'PG-13', 118, 'uploads/1743501416_captain_america.jpg', '2025-04-01', 'now showing', '2025-04-01 09:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reservations`
--

CREATE TABLE `tbl_reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `showtime_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_reservations`
--

INSERT INTO `tbl_reservations` (`reservation_id`, `user_id`, `showtime_id`, `total_price`, `status`, `created_at`) VALUES
(1, 2, 4, 300.00, 'pending', '2025-03-30 15:57:56'),
(2, 2, 4, 600.00, 'pending', '2025-03-30 15:58:00'),
(3, 2, 4, 300.00, 'pending', '2025-03-30 16:06:26'),
(4, 2, 4, 600.00, 'pending', '2025-03-30 16:06:31'),
(5, 2, 4, 300.00, 'pending', '2025-03-30 17:53:38'),
(6, 2, 4, 300.00, 'pending', '2025-03-30 18:20:58'),
(7, 2, 4, 300.00, 'pending', '2025-03-30 19:09:51'),
(8, 2, 4, 300.00, 'pending', '2025-03-30 19:10:02'),
(9, 2, 4, 300.00, 'pending', '2025-03-30 19:10:57'),
(10, 2, 4, 300.00, 'pending', '2025-03-30 19:11:00'),
(11, 2, 4, 300.00, 'pending', '2025-03-30 19:17:39'),
(12, 2, 4, 300.00, 'pending', '2025-03-30 19:19:41'),
(13, 2, 4, 300.00, 'pending', '2025-03-30 19:20:09'),
(14, 2, 4, 300.00, 'pending', '2025-03-30 19:20:13'),
(15, 2, 4, 300.00, 'pending', '2025-03-30 19:20:31'),
(16, 2, 4, 300.00, 'pending', '2025-03-30 19:21:16'),
(17, 2, 4, 300.00, 'pending', '2025-03-30 19:21:52'),
(18, 2, 4, 300.00, 'pending', '2025-03-30 19:23:25'),
(19, 2, 4, 300.00, 'pending', '2025-03-30 19:24:13'),
(20, 2, 4, 300.00, 'pending', '2025-03-30 19:29:42'),
(21, 2, 4, 300.00, 'pending', '2025-03-30 19:30:36');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reserved_seats`
--

CREATE TABLE `tbl_reserved_seats` (
  `seat_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `row_label` varchar(5) NOT NULL,
  `seat_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_reserved_seats`
--

INSERT INTO `tbl_reserved_seats` (`seat_id`, `reservation_id`, `row_label`, `seat_number`) VALUES
(1, 1, 'B', 3),
(2, 2, 'A', 3),
(3, 2, 'A', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_seats`
--

CREATE TABLE `tbl_seats` (
  `seat_id` int(11) NOT NULL,
  `showtime_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `row_label` char(1) DEFAULT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `status` enum('available','reserved','selected') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_seats`
--

INSERT INTO `tbl_seats` (`seat_id`, `showtime_id`, `user_id`, `row_label`, `seat_number`, `status`) VALUES
(941, 4, 2, 'A', 1, 'reserved'),
(942, 4, 2, 'A', 2, 'reserved'),
(943, 4, NULL, 'A', 3, 'available'),
(944, 4, 2, 'A', 4, 'reserved'),
(945, 4, NULL, 'B', 1, 'reserved'),
(946, 4, 2, 'B', 2, 'available'),
(947, 4, 2, 'B', 3, 'available'),
(948, 4, 2, 'B', 4, 'reserved'),
(949, 5, NULL, 'A', 1, 'reserved'),
(950, 5, NULL, 'A', 2, 'available'),
(951, 5, NULL, 'A', 3, 'available'),
(952, 5, NULL, 'A', 4, 'available'),
(953, 5, NULL, 'A', 5, 'available'),
(954, 5, NULL, 'B', 1, 'available'),
(955, 5, NULL, 'B', 2, 'available'),
(956, 5, NULL, 'B', 3, 'available'),
(957, 5, NULL, 'B', 4, 'available'),
(958, 5, NULL, 'B', 5, 'available'),
(959, 5, NULL, 'C', 1, 'available'),
(960, 5, NULL, 'C', 2, 'reserved'),
(961, 5, NULL, 'C', 3, 'available'),
(962, 5, NULL, 'C', 4, 'available'),
(963, 5, NULL, 'C', 5, 'available'),
(964, 5, NULL, 'D', 1, 'available'),
(965, 5, NULL, 'D', 2, 'available'),
(966, 5, NULL, 'D', 3, 'available'),
(967, 5, NULL, 'D', 4, 'available'),
(968, 5, NULL, 'D', 5, 'available');

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
(1, 2, 1, 3, 70, 280.00, '2025-03-27', '03:56:00'),
(3, 2, 2, 3, 70, 280.00, '2025-03-27', '03:56:00'),
(4, 2, 1, 1, 267, 300.00, '2025-03-24', '06:55:00'),
(5, 3, 1, 2, 20, 420.00, '2025-04-01', '18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transactions`
--

CREATE TABLE `tbl_transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_url` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `showtime_id` int(11) NOT NULL,
  `seats` text DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transactions`
--

INSERT INTO `tbl_transactions` (`transaction_id`, `user_id`, `amount`, `status`, `payment_url`, `created_at`, `showtime_id`, `seats`, `total_price`, `payment_status`, `transaction_date`) VALUES
(1, 2, 0.00, 'pending', '', '2025-03-31 20:43:44', 4, 'B4', 300.00, 'paid', '2025-03-31 20:43:44'),
(2, 2, 0.00, 'pending', '', '2025-03-31 20:44:29', 4, 'B4', 300.00, 'paid', '2025-03-31 20:44:29'),
(3, 2, 0.00, 'pending', '', '2025-04-01 09:19:26', 4, 'B3', 300.00, 'paid', '2025-04-01 09:19:26'),
(4, 2, 0.00, 'pending', '', '2025-04-01 09:19:54', 4, 'B2, B3, B4', 900.00, 'paid', '2025-04-01 09:19:54'),
(5, 2, 0.00, 'pending', '', '2025-04-01 09:49:47', 4, 'A4, B4', 600.00, 'paid', '2025-04-01 09:49:47'),
(6, 2, 0.00, 'pending', '', '2025-04-01 09:52:12', 4, 'A4, B4', 600.00, 'paid', '2025-04-01 09:52:12'),
(7, 2, 0.00, 'pending', '', '2025-04-01 09:54:29', 4, 'A4, B4', 600.00, 'paid', '2025-04-01 09:54:29');

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
-- Indexes for table `tbl_foodproducts`
--
ALTER TABLE `tbl_foodproducts`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Indexes for table `tbl_movies`
--
ALTER TABLE `tbl_movies`
  ADD PRIMARY KEY (`movie_id`);

--
-- Indexes for table `tbl_reservations`
--
ALTER TABLE `tbl_reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `showtime_id` (`showtime_id`);

--
-- Indexes for table `tbl_reserved_seats`
--
ALTER TABLE `tbl_reserved_seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indexes for table `tbl_seats`
--
ALTER TABLE `tbl_seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD KEY `showtime_id` (`showtime_id`);

--
-- Indexes for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  ADD PRIMARY KEY (`showtime_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `cinema_id` (`cinema_id`);

--
-- Indexes for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `cinema_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_cinema_owner`
--
ALTER TABLE `tbl_cinema_owner`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_foodpartner`
--
ALTER TABLE `tbl_foodpartner`
  MODIFY `partner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_foodproducts`
--
ALTER TABLE `tbl_foodproducts`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_movies`
--
ALTER TABLE `tbl_movies`
  MODIFY `movie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_reservations`
--
ALTER TABLE `tbl_reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_reserved_seats`
--
ALTER TABLE `tbl_reserved_seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_seats`
--
ALTER TABLE `tbl_seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=969;

--
-- AUTO_INCREMENT for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  MODIFY `showtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Constraints for table `tbl_foodproducts`
--
ALTER TABLE `tbl_foodproducts`
  ADD CONSTRAINT `tbl_foodproducts_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `tbl_foodpartner` (`partner_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_reservations`
--
ALTER TABLE `tbl_reservations`
  ADD CONSTRAINT `tbl_reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_reservations_ibfk_2` FOREIGN KEY (`showtime_id`) REFERENCES `tbl_showtimes` (`showtime_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_reserved_seats`
--
ALTER TABLE `tbl_reserved_seats`
  ADD CONSTRAINT `tbl_reserved_seats_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `tbl_reservations` (`reservation_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_seats`
--
ALTER TABLE `tbl_seats`
  ADD CONSTRAINT `tbl_seats_ibfk_1` FOREIGN KEY (`showtime_id`) REFERENCES `tbl_showtimes` (`showtime_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_showtimes`
--
ALTER TABLE `tbl_showtimes`
  ADD CONSTRAINT `tbl_showtimes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `tbl_movies` (`movie_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_showtimes_ibfk_2` FOREIGN KEY (`cinema_id`) REFERENCES `tbl_cinema` (`cinema_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_transactions`
--
ALTER TABLE `tbl_transactions`
  ADD CONSTRAINT `tbl_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
