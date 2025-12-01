-- Database: sutanraya

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL,
  `serviceType` varchar(50) DEFAULT NULL,
  `routeId` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  `passengerName` varchar(100) DEFAULT NULL,
  `passengerPhone` varchar(20) DEFAULT NULL,
  `passengerType` varchar(20) DEFAULT 'Umum',
  `seatCount` int(11) DEFAULT 1,
  `selectedSeats` text DEFAULT NULL, -- Stored as JSON or comma-separated
  `duration` int(11) DEFAULT 1,
  `totalPrice` decimal(15,2) DEFAULT 0.00,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT NULL,
  `validationStatus` varchar(50) DEFAULT NULL,
  `paymentLocation` varchar(100) DEFAULT NULL,
  `paymentReceiver` varchar(100) DEFAULT NULL,
  `paymentProof` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `seatNumbers` varchar(100) DEFAULT NULL,
  `ktmProof` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL, -- Bus specific
  `seatCapacity` int(11) DEFAULT NULL, -- Bus specific
  `priceType` varchar(50) DEFAULT NULL, -- Bus specific
  `packageType` varchar(50) DEFAULT NULL, -- Bus specific
  `routeName` varchar(100) DEFAULT NULL, -- Bus specific
  `downPaymentAmount` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `serviceType`, `routeId`, `date`, `time`, `passengerName`, `passengerPhone`, `passengerType`, `seatCount`, `selectedSeats`, `duration`, `totalPrice`, `paymentMethod`, `paymentStatus`, `validationStatus`, `paymentLocation`, `paymentReceiver`, `paymentProof`, `status`, `seatNumbers`, `ktmProof`, `type`, `seatCapacity`, `priceType`, `packageType`, `routeName`, `downPaymentAmount`) VALUES
(1701430001, 'Travel', 'PDG-BKT', CURDATE(), '08:00', 'Siti Aminah', '081345678901', 'Umum', 1, '["3"]', 1, 120000.00, 'Cash', 'Lunas', 'Valid', 'Loket Padang', 'Admin', NULL, 'Pending', '3', NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(1701430002, 'Travel', 'PDG-BKT', CURDATE(), '08:00', 'Rina Wati', '081345678902', 'Pelajar', 1, '["4"]', 1, 100000.00, 'Transfer', 'Lunas', 'Valid', NULL, NULL, 'proof_1.jpg', 'Pending', '4', 'ktm_1.jpg', NULL, NULL, NULL, NULL, NULL, 0.00),
(1701430003, 'Travel', 'PDG-PYK', CURDATE(), '10:00', 'Doni Pratama', '081345678903', 'Umum', 2, '["1","2"]', 1, 300000.00, 'DP', 'DP', 'Valid', 'Loket Padang', 'Admin', NULL, 'Pending', '1, 2', NULL, NULL, NULL, NULL, NULL, NULL, 100000.00),
(1701430004, 'Carter', 'PDG-BKT', CURDATE(), NULL, 'PT Semen Padang', '081345678904', 'Umum', 1, NULL, 2, 2400000.00, 'Transfer', 'Lunas', 'Valid', NULL, NULL, 'proof_2.jpg', 'On Trip', 'Full Unit', NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(1701430005, 'Bus Pariwisata', 'PDG-JKT', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, 'SMA 1 Padang', '081345678905', 'Umum', 1, NULL, 7, 31500000.00, 'DP', 'DP', 'Valid', NULL, NULL, 'proof_3.jpg', 'Pending', 'Full Unit', NULL, 'Big', 45, 'Kantor', 'AllIn', 'Padang - Jakarta', 5000000.00),
(1701430006, 'Dropping', 'PDG-BKT', CURDATE(), NULL, 'Keluarga Pak Budi', '081345678906', 'Umum', 1, NULL, 1, 850000.00, 'Cash', 'Lunas', 'Valid', 'Supir', 'Budi', NULL, 'Tiba', 'Full Unit', NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(1701430007, 'Travel', 'BKT-PDG', CURDATE(), '13:00', 'Andi Saputra', '081345678907', 'Umum', 1, '["1"]', 1, 120000.00, 'Cash', 'Menunggu Validasi', 'Menunggu Validasi', 'Loket Bukittinggi', 'Staf BKT', NULL, 'Pending', '1', NULL, NULL, NULL, NULL, NULL, NULL, 0.00),
(1701430008, 'Travel', 'PYK-PDG', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '07:00', 'Maya Sari', '081345678908', 'Pelajar', 1, '["2"]', 1, 130000.00, 'Transfer', 'Menunggu Validasi', 'Menunggu Validasi', NULL, NULL, 'proof_4.jpg', 'Pending', '2', 'ktm_2.jpg', NULL, NULL, NULL, NULL, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Standby',
  `licenseType` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `phone`, `status`, `licenseType`) VALUES
(1701410001, 'Budi Santoso', '081234567890', 'Standby', 'B1 Umum'),
(1701410002, 'Ahmad Hidayat', '081234567891', 'Jalan', 'B1 Umum'),
(1701410003, 'Rudi Hartono', '081234567892', 'Standby', 'B1 Umum'),
(1701410004, 'Joko Susilo', '081234567893', 'Standby', 'B2 Umum'),
(1701410005, 'Eko Prasetyo', '081234567894', 'Jalan', 'B2 Umum'),
(1701410006, 'Dedi Kurniawan', '081234567895', 'Standby', 'A Umum'),
(1701410007, 'Hendra Wijaya', '081234567896', 'Standby', 'B1 Umum'),
(1701410008, 'Fajar Nugraha', '081234567897', 'Standby', 'B1 Umum'),
(1701410009, 'Bayu Saputra', '081234567898', 'Jalan', 'B1 Umum'),
(1701410010, 'Agus Salim', '081234567899', 'Standby', 'B2 Umum');

-- --------------------------------------------------------

--
-- Table structure for table `fleet`
--

DROP TABLE IF EXISTS `fleet`;
CREATE TABLE `fleet` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `plate` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT 7,
  `status` varchar(50) DEFAULT 'Tersedia',
  `icon` varchar(50) DEFAULT 'bi-truck-front-fill',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `fleet`
--

INSERT INTO `fleet` (`id`, `name`, `plate`, `capacity`, `status`, `icon`) VALUES
(1701420001, 'Hiace Commuter 01', 'BA 7001 SR', 14, 'Tersedia', 'bi-van-fill'),
(1701420002, 'Hiace Commuter 02', 'BA 7002 SR', 14, 'On Trip', 'bi-van-fill'),
(1701420003, 'Hiace Premio 01', 'BA 7003 SR', 14, 'Tersedia', 'bi-van-fill'),
(1701420004, 'Hiace Premio 02', 'BA 7004 SR', 14, 'Perbaikan', 'bi-van-fill'),
(1701420005, 'Hiace Luxury 01', 'BA 7005 SR', 10, 'On Trip', 'bi-van-fill'),
(1701420006, 'Medium Bus 01', 'BA 7006 SR', 33, 'Tersedia', 'bi-bus-front-fill'),
(1701420007, 'Medium Bus 02', 'BA 7007 SR', 35, 'Tersedia', 'bi-bus-front-fill'),
(1701420008, 'Big Bus HDD 01', 'BA 7008 SR', 45, 'On Trip', 'bi-bus-front-fill'),
(1701420009, 'Big Bus SHD 01', 'BA 7009 SR', 32, 'Tersedia', 'bi-bus-front-fill');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

DROP TABLE IF EXISTS `trips`;
CREATE TABLE `trips` (
  `id` bigint(20) NOT NULL,
  `routeConfig` text DEFAULT NULL, -- Stored as JSON
  `fleet` text DEFAULT NULL, -- Stored as JSON
  `driver` text DEFAULT NULL, -- Stored as JSON
  `passengers` text DEFAULT NULL, -- Stored as JSON
  `status` varchar(50) DEFAULT 'On Trip',
  `departureTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `routeConfig`, `fleet`, `driver`, `passengers`, `status`, `departureTime`) VALUES
(1701440001, '{"key":"PDG-BKT|2023-12-01|08:00","routeId":"PDG-BKT","date":"2023-12-01","time":"08:00"}', '{"id":1701420002,"name":"Hiace Commuter 02","plate":"BA 7002 SR"}', '{"id":1701410002,"name":"Ahmad Hidayat"}', '[{"id":1701430001,"passengerName":"Siti Aminah"}]', 'On Trip', NOW()),
(1701440002, '{"key":"PDG-JKT|2023-12-03|Bus","routeId":"PDG-JKT","date":"2023-12-03","time":"Bus"}', '{"id":1701420008,"name":"Big Bus HDD 01","plate":"BA 7008 SR"}', '{"id":1701410005,"name":"Eko Prasetyo"}', '[{"id":1701430005,"passengerName":"SMA 1 Padang"}]', 'On Trip', NOW());

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `id` varchar(20) NOT NULL,
  `origin` varchar(50) NOT NULL,
  `destination` varchar(50) NOT NULL,
  `price_umum` decimal(15,2) NOT NULL,
  `price_pelajar` decimal(15,2) NOT NULL,
  `price_dropping` decimal(15,2) NOT NULL,
  `price_carter` decimal(15,2) NOT NULL,
  `schedules` text NOT NULL, -- JSON array of times
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `origin`, `destination`, `price_umum`, `price_pelajar`, `price_dropping`, `price_carter`, `schedules`) VALUES
('PDG-BKT', 'Padang', 'Bukittinggi', 120000.00, 100000.00, 850000.00, 1200000.00, '["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"]'),
('BKT-PDG', 'Bukittinggi', 'Padang', 120000.00, 100000.00, 850000.00, 1200000.00, '["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"]'),
('PDG-PYK', 'Padang', 'Payakumbuh', 150000.00, 130000.00, 1000000.00, 1500000.00, '["08:00", "10:00", "14:00", "18:00"]'),
('PYK-PDG', 'Payakumbuh', 'Padang', 150000.00, 130000.00, 1000000.00, 1500000.00, '["05:00", "07:00", "10:00", "14:00", "17:00"]');

-- --------------------------------------------------------

--
-- Table structure for table `bus_routes`
--

DROP TABLE IF EXISTS `bus_routes`;
CREATE TABLE `bus_routes` (
  `id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `min_days` int(11) NOT NULL DEFAULT 1,
  `price_s33` decimal(15,2) NOT NULL,
  `price_s35` decimal(15,2) NOT NULL,
  `is_long_trip` tinyint(1) NOT NULL DEFAULT 0,
  `big_bus_config` text DEFAULT NULL, -- JSON for complex pricing
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bus_routes`
--

INSERT INTO `bus_routes` (`id`, `name`, `min_days`, `price_s33`, `price_s35`, `is_long_trip`, `big_bus_config`) VALUES
('PDG-BKT', 'Padang - Bukittinggi', 1, 2500000.00, 2600000.00, 0, '{"s45": {"kantor": 4000000, "agen": 3800000}, "s32": {"kantor": 4500000, "agen": 4300000}}'),
('PDG-JKT', 'Padang - Jakarta', 6, 0.00, 0.00, 1, '{"base": 4500000, "allin": 5500000}');
COMMIT;
