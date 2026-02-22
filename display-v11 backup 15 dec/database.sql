CREATE DATABASE IF NOT EXISTS sutanraya_v11;
USE sutanraya_v11;

-- Database: sutanraya_v11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `bookings`
--

CREATE TABLE IF NOT EXISTS `bookings` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `fleet`
--

CREATE TABLE IF NOT EXISTS `fleet` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `plate` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `drivers`
--

CREATE TABLE IF NOT EXISTS `drivers` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `licenseType` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `trips`
--

CREATE TABLE IF NOT EXISTS `trips` (
  `id` bigint(20) NOT NULL,
  `routeConfig` text DEFAULT NULL,
  `fleet` text DEFAULT NULL,
  `driver` text DEFAULT NULL,
  `passengers` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `departureTime` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
  `id` varchar(50) NOT NULL,
  `origin` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `price_umum` double DEFAULT 0,
  `price_pelajar` double DEFAULT 0,
  `price_dropping` double DEFAULT 0,
  `price_carter` double DEFAULT 0,
  `schedules` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `bus_routes`
--

CREATE TABLE IF NOT EXISTS `bus_routes` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `big_bus_config` text DEFAULT NULL,
  `price_s33` double DEFAULT 0,
  `price_s35` double DEFAULT 0,
  `is_long_trip` tinyint(1) DEFAULT 0,
  `minDays` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
