-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 26, 2026 at 07:33 PM
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
-- Database: `ems_health_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `address` varchar(255) NOT NULL,
  `emergency_name` varchar(100) NOT NULL,
  `emergency_phone` varchar(30) NOT NULL,
  `medications` text NOT NULL,
  `allergies` text NOT NULL,
  `surgeries` text NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `fullname`, `birthday`, `address`, `emergency_name`, `emergency_phone`, `medications`, `allergies`, `surgeries`, `pin_hash`, `created_at`) VALUES
(10, 'Demo Patient 1: Jane Doe', '1945-07-23', '123 main street, city, state', 'John Doe', ' 235 555 0199', 'Lisinopril 10mg', 'Penicillin, Latex ', 'none known', '$2y$10$3TvgqndBuNa8vgux7.3txeCqAmdlo/R8scNeggtsrtAHeTDwvFFia', '2026-11-26 18:28:37'),
(11, 'Demo Patient: Alex Rivera', '1991-02-09', '123 Main Street, City, State', 'Maria Rivera', '111 555 0142', 'Albuterol Inhaler ', 'None Known', 'Asthma', '$2y$10$decDyA4UJtqUQswjNZTI/eTKDzcQ0uHEE.QwP9rVwUm7JsEV4pAgu', '2026-11-26 18:32:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
