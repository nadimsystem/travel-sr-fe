-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 01:26 PM
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
  `dropoffAddress` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `serviceType`, `routeId`, `date`, `time`, `passengerName`, `passengerPhone`, `passengerType`, `seatCount`, `selectedSeats`, `duration`, `totalPrice`, `paymentMethod`, `paymentStatus`, `validationStatus`, `paymentLocation`, `paymentReceiver`, `paymentProof`, `status`, `seatNumbers`, `ktmProof`, `downPaymentAmount`, `type`, `seatCapacity`, `priceType`, `packageType`, `routeName`, `pickupAddress`, `dropoffAddress`) VALUES
(1765429124185, 'Travel', 'BKT-PDG-2', '2025-12-11', '08:00', 'Sonia afrila', '081268588949', 'Umum', 1, '[\"7\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765429124185.jpeg', 'Pending', '7', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Ulak karang', 'Depan car wash glow'),
(1765429969970, 'Travel', 'BKT-PDG-2', '2025-12-01', '04:00', 'Junaidi s rustam', '081266769510', 'Umum', 1, '[\"2\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765429969970.jpeg', 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Pakan kurai ', 'BIM '),
(1765431830043, 'Travel', 'BKT-PDG-2', '2025-12-01', '04:00', 'Ojha', '081266212211', 'Umum', 2, '[\"4\",\"5\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '4, 5', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Hotel ambunsuri ', 'BIM '),
(1765432374356, 'Travel', 'BKT-PDG-2', '2025-12-01', '04:00', 'Hengky ', '082297483787', 'Umum', 3, '[\"2\",\"3\",\"4\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765432374356.jpeg', 'Pending', '2, 3, 4', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Garegeh', 'BIM '),
(1765432626815, 'Travel', 'BKT-PDG-2', '2025-12-01', '04:00', 'Khairunnisa ', '082384927675', 'Umum', 1, '[\"6\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765432626815.jpeg', 'Pending', '6', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Pakan kurai ', 'Pasar baru '),
(1765433115669, 'Travel', 'BKT-PDG-2', '2025-12-01', '09:00', 'Fauzan Hamdi 	', '0853-7479-4794', 'Umum', 1, '[\"2\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', '	Belakang Puskesmas aua	', 'Ulak karang'),
(1765433245874, 'Travel', 'BKT-PDG-2', '2025-12-01', '09:00', 'Oky Oktavian ', '082284307868', 'Umum', 1, '[\"3\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765433245874.jpeg', 'Pending', '3', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'By pass aur', 'Hotel Rocky '),
(1765433355554, 'Travel', 'BKT-PDG-2', '2025-12-01', '09:00', 'Bayu', '082285074089', 'Umum', 1, '[\"4\"]', 1, 250000, 'Transfer', 'Lunas', 'Valid', '', '', 'image/proofs/proof_1765433355554.jpeg', 'Pending', '4', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Birugo', 'Parak laweh'),
(1765433587725, 'Travel', 'BKT-PDG-2', '2025-12-11', '12:00', 'teo', '0821-7303-1454', 'Umum', 1, '[\"2\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'secata b pdg panjang ', 'asrama tni lapai'),
(1765433784408, 'Travel', 'BKT-PDG-2', '2025-12-01', '12:00', 'Teo', '0821-7303-1454', 'Umum', 1, '[\"2\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'secata b pdg panjang ', 'Lapai'),
(1765433866694, 'Travel', 'BKT-PDG-2', '2025-12-01', '12:00', 'Iqbal ', '081999395862', 'Umum', 1, '[\"3\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '3', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Bukik apik ', 'Tamsis jati'),
(1765433945969, 'Travel', 'BKT-PDG-2', '2025-12-01', '12:00', 'Gunung ', '08126747824', 'Umum', 1, '[\"5\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '5', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Padang panjang ', 'Red doors '),
(1765434032911, 'Travel', 'BKT-PDG-2', '2025-12-01', '12:00', 'Nurul Pratiwi ', '082330366612', 'Umum', 2, '[\"7\",\"8\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '7, 8', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Sungai tanang ', 'BIM '),
(1765434166257, 'Travel', 'BKT-PDG-2', '2025-12-01', '18:00', 'Ronald comeron ', '085271715048', 'Umum', 1, '[\"2\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Kamang hilia', 'BIM '),
(1765434255838, 'Travel', 'BKT-PDG-2', '2025-12-02', '04:00', 'Lisa dresia', '081374448424‬', 'Umum', 1, '[\"2\"]', 1, 250000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Salma', NULL, 'Pending', '2', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Belakang pertanian ', 'Bim'),
(1765434406766, 'Travel', 'BKT-PDG-2', '2025-12-02', '04:00', 'Syafni', '082172926240', 'Umum', 1, '[\"3\"]', 1, 250000, 'Transfer', 'Menunggu Validasi', 'Menunggu Validasi', '', '', 'image/proofs/proof_1765434406766.jpeg', 'Pending', '3', '', 0, 'Unit', 1, 'Kantor', 'Unit', 'Bukittinggi Via Sitinjau - Padang', 'Pandai sikek ', 'Poltekes Siteba ');

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

--
-- Dumping data for table `bus_routes`
--

INSERT INTO `bus_routes` (`id`, `name`, `big_bus_config`, `price_s33`, `price_s35`, `is_long_trip`, `minDays`) VALUES
('PDG-BKT', 'Padang - Bukittinggi', '{\"s45\":{\"kantor\":4000000,\"agen\":3800000},\"s32\":{\"kantor\":4500000,\"agen\":4300000}}', 2500000, 2600000, 0, 1),
('PDG-JKT', 'Padang - Jakarta', '{\"base\":4500000,\"allin\":5500000}', 0, 0, 1, 6),
('PDG-KNO', 'Padang - Medan', '{\"base\":4500000,\"allin\":5500000}', 3500000, 3600000, 1, 6),
('PDG-PYK', 'Padang - Payakumbuh', '{\"s45\":{\"kantor\":4300000,\"agen\":4000000},\"s32\":{\"kantor\":4300000,\"agen\":4000000}}', 2600000, 2700000, 0, 1);

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

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `status`, `licenseType`) VALUES
(1765182953107, 'RAFI', '085363660336', 'Standby', 'B1 Umum'),
(1765185000001, 'DEFRI ANDIKA', '-', 'Standby', 'B1 Umum'),
(1765185000002, 'AFRIANTO', '-', 'Jalan', 'B1 Umum'),
(1765185000003, 'RAFI RAIHAN MAULANA', '-', 'Standby', 'B1 Umum'),
(1765185000004, 'HERU GUNAWAN', '-', 'Standby', 'B1 Umum'),
(1765185000005, 'Muhammad eka', '-', 'Standby', 'B1 Umum'),
(1765185000006, 'SAFRIYANTO', '-', 'Standby', 'B1 Umum'),
(1765185000007, 'Harry Josa Putra', '-', 'Standby', 'B1 Umum'),
(1765185000008, 'AUDRIYAN DEFNOZA', '-', 'Standby', 'B1 Umum'),
(1765185000009, 'RIKI PUTRA', '-', 'Standby', 'B1 Umum'),
(1765185000010, 'ADITYA FRIANDIKA', '-', 'Standby', 'B1 Umum'),
(1765185000011, 'KHAIRUL FAHMI', '-', 'Standby', 'B1 Umum'),
(1765185000012, 'AHMAD ASRA', '-', 'Standby', 'B1 Umum'),
(1765423308327, 'AFRIZAL (cadangan)', '082387828826', 'Jalan', 'A Umum');

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

--
-- Dumping data for table `fleet`
--

INSERT INTO `fleet` (`id`, `name`, `plate`, `capacity`, `status`, `icon`) VALUES
(1765182765547, 'Hiace Premio SR1', 'BA 7083 AU', 8, 'On Trip', 'bi-bus-front-fill'),
(1765185000020, 'SR 006 TRAVEL', 'Z 7431 DA', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000021, 'SR 006 TRAVEL', 'BA 7056 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000022, 'SR 007 TRAVEL', 'BA 7053 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000023, 'SR 008 TRAVEL', 'BA 7063 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000024, 'SR 009 TRAVEL', 'BA 7054 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000025, 'SR 010 TRAVEL', 'BA 7049 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000026, 'SR 011 TRAVEL', 'BA 7050 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000027, 'SR 012 TRAVEL', 'BA 7067 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000028, 'SR 014 TRAVEL', 'BA 7078 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000029, 'SR 015 TRAVEL', 'BA 7079 DAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000030, 'SR 018 TRAVEL', 'BA 7083 OAU', 8, 'Tersedia', 'bi-car-front-fill'),
(1765185000031, 'SR 019 TRAVEL', 'BA 7084 OAU', 8, 'Tersedia', 'bi-car-front-fill');

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

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `origin`, `destination`, `price_umum`, `price_pelajar`, `price_dropping`, `price_carter`, `schedules`) VALUES
('BKT-PDG', 'Bukittinggi', 'Padang', 120000, 100000, 900000, 1500000, '[\"06:00\",\"08:00\",\"10:00\",\"13:00\",\"15:00\",\"17:00\",\"18:00\",\"19:00\"]'),
('BKT-PDG-2', 'Bukittinggi Via Sitinjau', 'Padang', 250000, 250000, 2000000, 2500000, '[\"04:00\",\"08:00\",\"09:00\",\"10:00\",\"12:00\",\"15:00\",\"18:00\"]'),
('PDG-BKT', 'Padang via sitinjau', 'Bukittinggi', 250000, 250000, 2000000, 2500000, '[\"08:00\",\"10:00\",\"12:00\",\"14:00\",\"16:00\",\"18:00\",\"20:00\"]'),
('PDG-BKT-2', 'Padang', 'Bukittinggi (normal)', 120000, 100000, 900000, 1500000, '[\"08:00\",\"10:00\",\"14:00\",\"18:00\"]'),
('PDG-PYK', 'Padang', 'Payakumbuh', 150000, 130000, 1100000, 1800000, '[\"08:00\",\"10:00\",\"14:00\",\"18:00\"]'),
('PDG-PYK-2', 'Padang via sitinjau', 'Payakumbuh', 250000, 250000, 2000000, 2000000, '[\"08:00\",\"10:00\",\"12:00\",\"14:00\",\"16:00\",\"18:00\",\"20:00\"]'),
('PYK-PDG', 'Payakumbuh', 'Padang', 150000, 130000, 1100000, 1800000, '[\"05:00\",\"07:00\",\"10:00\",\"14:00\",\"17:00\"]');

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

--
-- Dumping data for table `schedule_defaults`
--

INSERT INTO `schedule_defaults` (`id`, `routeId`, `time`, `fleetId`, `driverId`) VALUES
(1, 'BKT-PDG', '06:00', '1765185000022', '1765185000001'),
(5, 'PDG-BKT', '08:00', '1765185000026', '1765185000007'),
(9, 'PDG-BKT-2', '08:00', '1765185000026', '1765185000007'),
(10, 'PDG-BKT-2', '10:00', '1765185000028', '1765185000009'),
(11, 'PDG-BKT', '10:00', '1765185000028', '1765185000009'),
(12, 'BKT-PDG', '08:00', '1765185000025', '1765185000006'),
(13, 'PDG-PYK-2', '08:00', '1765185000020', '1765185000001'),
(14, 'PDG-PYK-2', '14:00', '1765185000030', '1765185000011'),
(16, 'PYK-PDG', '10:00', '1765185000023', '1765423308327'),
(21, 'PDG-PYK', '08:00', '1765185000020', '1765185000001'),
(22, 'BKT-PDG', '13:00', '1765185000021', '1765185000002'),
(23, 'PDG-PYK', '14:00', '1765185000030', '1765185000011'),
(24, 'BKT-PDG-2', '04:00', '1765185000029', '1765423308327');

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
  `note` text DEFAULT NULL
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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `schedule_defaults`
--
ALTER TABLE `schedule_defaults`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
