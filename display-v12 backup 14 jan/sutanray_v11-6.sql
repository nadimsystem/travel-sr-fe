-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 08, 2026 at 10:59 AM
-- Server version: 11.4.8-MariaDB-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sutanray_v11`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL,
  `serviceType` varchar(50) DEFAULT NULL,
  `routeId` varchar(50) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `passengerName` varchar(100) DEFAULT NULL,
  `passengerPhone` varchar(50) DEFAULT NULL,
  `passengerType` varchar(50) DEFAULT NULL,
  `seatCount` int(11) DEFAULT 1,
  `selectedSeats` text DEFAULT NULL,
  `duration` int(11) DEFAULT 1,
  `totalPrice` double DEFAULT 0,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT NULL,
  `validationStatus` varchar(50) DEFAULT NULL,
  `paymentLocation` varchar(100) DEFAULT NULL,
  `paymentReceiver` varchar(100) DEFAULT NULL,
  `paymentProof` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `seatNumbers` varchar(100) DEFAULT NULL,
  `ktmProof` varchar(255) DEFAULT NULL,
  `downPaymentAmount` double DEFAULT 0,
  `type` varchar(50) DEFAULT NULL,
  `seatCapacity` int(11) DEFAULT NULL,
  `priceType` varchar(50) DEFAULT NULL,
  `packageType` varchar(50) DEFAULT NULL,
  `routeName` varchar(100) DEFAULT NULL,
  `pickupAddress` text DEFAULT NULL,
  `dropoffAddress` text DEFAULT NULL,
  `batchNumber` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_logs`
--

CREATE TABLE `booking_logs` (
  `id` bigint(20) NOT NULL,
  `booking_id` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `prev_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bus_routes`
--

CREATE TABLE `bus_routes` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `big_bus_config` text DEFAULT NULL,
  `price_s33` double DEFAULT 0,
  `price_s35` double DEFAULT 0,
  `is_long_trip` tinyint(1) DEFAULT 0,
  `minDays` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `licenseType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fleet`
--

CREATE TABLE `fleet` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `plate` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) NOT NULL,
  `senderName` varchar(100) DEFAULT NULL,
  `senderPhone` varchar(50) DEFAULT NULL,
  `receiverName` varchar(100) DEFAULT NULL,
  `receiverPhone` varchar(50) DEFAULT NULL,
  `itemDescription` varchar(255) DEFAULT NULL,
  `itemType` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `route` varchar(100) DEFAULT NULL,
  `price` double DEFAULT 0,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT 'Menunggu Pembayaran',
  `status` varchar(50) DEFAULT 'Pending',
  `pickupAddress` text DEFAULT NULL,
  `dropoffAddress` text DEFAULT NULL,
  `mapLink` text DEFAULT NULL,
  `bookingDate` date DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_logs`
--

CREATE TABLE `package_logs` (
  `id` int(11) NOT NULL,
  `package_id` bigint(20) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` varchar(50) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `price_umum` double DEFAULT 0,
  `price_pelajar` double DEFAULT 0,
  `price_dropping` double DEFAULT 0,
  `price_carter` double DEFAULT 0,
  `schedules` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_defaults`
--

CREATE TABLE `schedule_defaults` (
  `id` int(11) NOT NULL,
  `routeId` varchar(50) DEFAULT NULL,
  `time` varchar(10) DEFAULT NULL,
  `fleetId` varchar(50) DEFAULT NULL,
  `driverId` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) NOT NULL,
  `routeConfig` text DEFAULT NULL,
  `fleet` text DEFAULT NULL,
  `driver` text DEFAULT NULL,
  `passengers` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `departureTime` varchar(50) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `note` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_logs`
--
ALTER TABLE `booking_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fleet`
--
ALTER TABLE `fleet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_logs`
--
ALTER TABLE `package_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_defaults`
--
ALTER TABLE `schedule_defaults`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`routeId`,`time`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_logs`
--
ALTER TABLE `package_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_defaults`
--
ALTER TABLE `schedule_defaults`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `package_logs`
--
ALTER TABLE `package_logs`
  ADD CONSTRAINT `package_logs_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
