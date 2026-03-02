-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 08 Jan 2026 pada 05.01
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sutanraya_v11`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
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
  `batchNumber` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_logs`
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
-- Struktur dari tabel `broadcast_queue`
--

CREATE TABLE `broadcast_queue` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','processing','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bus_routes`
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
-- Struktur dari tabel `drivers`
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
-- Struktur dari tabel `fleet`
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
-- Struktur dari tabel `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) NOT NULL,
  `receiptNumber` varchar(50) DEFAULT NULL,
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
-- Struktur dari tabel `package_logs`
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
-- Struktur dari tabel `purchasing_assets`
--

CREATE TABLE `purchasing_assets` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `value` decimal(15,2) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `pic` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `purchase_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `usage` varchar(100) DEFAULT 'Universal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_cabinets`
--

CREATE TABLE `purchasing_cabinets` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_deployments`
--

CREATE TABLE `purchasing_deployments` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty_deployed` int(11) NOT NULL,
  `deployed_to_fleet_id` int(11) DEFAULT NULL,
  `deployed_to_name` varchar(255) DEFAULT NULL,
  `deployed_by` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `deployment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Deployed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_items`
--

CREATE TABLE `purchasing_items` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 5,
  `unit` varchar(50) DEFAULT NULL,
  `last_price` decimal(15,2) DEFAULT NULL,
  `compatibility` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `condition_status` varchar(50) DEFAULT 'Baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rack_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_orders`
--

CREATE TABLE `purchasing_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Draft',
  `order_date` date DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_order_items`
--

CREATE TABLE `purchasing_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(15,2) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_racks`
--

CREATE TABLE `purchasing_racks` (
  `id` int(11) NOT NULL,
  `cabinet_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_receiving`
--

CREATE TABLE `purchasing_receiving` (
  `id` int(11) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `qty_received` int(11) NOT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `received_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `receipt_photo` varchar(255) DEFAULT NULL,
  `supplier_invoice` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `validated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_requests`
--

CREATE TABLE `purchasing_requests` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_request_items`
--

CREATE TABLE `purchasing_request_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `urgency` varchar(50) DEFAULT NULL,
  `bus_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchasing_rooms`
--

CREATE TABLE `purchasing_rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `routes`
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
-- Struktur dari tabel `schedule_defaults`
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
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `payment_terms` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) NOT NULL,
  `routeConfig` text DEFAULT NULL,
  `fleet` text DEFAULT NULL,
  `driver` text DEFAULT NULL,
  `passengers` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` varchar(10) DEFAULT NULL,
  `departureTime` varchar(50) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
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
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `booking_logs`
--
ALTER TABLE `booking_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `broadcast_queue`
--
ALTER TABLE `broadcast_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `fleet`
--
ALTER TABLE `fleet`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `package_logs`
--
ALTER TABLE `package_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indeks untuk tabel `purchasing_assets`
--
ALTER TABLE `purchasing_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `purchasing_cabinets`
--
ALTER TABLE `purchasing_cabinets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indeks untuk tabel `purchasing_deployments`
--
ALTER TABLE `purchasing_deployments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `purchasing_items`
--
ALTER TABLE `purchasing_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `rack_id` (`rack_id`);

--
-- Indeks untuk tabel `purchasing_orders`
--
ALTER TABLE `purchasing_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `purchasing_order_items`
--
ALTER TABLE `purchasing_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `purchasing_racks`
--
ALTER TABLE `purchasing_racks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cabinet_id` (`cabinet_id`);

--
-- Indeks untuk tabel `purchasing_receiving`
--
ALTER TABLE `purchasing_receiving`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `purchasing_requests`
--
ALTER TABLE `purchasing_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `purchasing_request_items`
--
ALTER TABLE `purchasing_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indeks untuk tabel `purchasing_rooms`
--
ALTER TABLE `purchasing_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `schedule_defaults`
--
ALTER TABLE `schedule_defaults`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`routeId`,`time`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `broadcast_queue`
--
ALTER TABLE `broadcast_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `package_logs`
--
ALTER TABLE `package_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_assets`
--
ALTER TABLE `purchasing_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_cabinets`
--
ALTER TABLE `purchasing_cabinets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_deployments`
--
ALTER TABLE `purchasing_deployments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_items`
--
ALTER TABLE `purchasing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_orders`
--
ALTER TABLE `purchasing_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_order_items`
--
ALTER TABLE `purchasing_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_racks`
--
ALTER TABLE `purchasing_racks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_receiving`
--
ALTER TABLE `purchasing_receiving`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_requests`
--
ALTER TABLE `purchasing_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_request_items`
--
ALTER TABLE `purchasing_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchasing_rooms`
--
ALTER TABLE `purchasing_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `schedule_defaults`
--
ALTER TABLE `schedule_defaults`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `package_logs`
--
ALTER TABLE `package_logs`
  ADD CONSTRAINT `package_logs_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchasing_cabinets`
--
ALTER TABLE `purchasing_cabinets`
  ADD CONSTRAINT `purchasing_cabinets_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `purchasing_rooms` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchasing_deployments`
--
ALTER TABLE `purchasing_deployments`
  ADD CONSTRAINT `purchasing_deployments_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `purchasing_items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchasing_items`
--
ALTER TABLE `purchasing_items`
  ADD CONSTRAINT `purchasing_items_ibfk_1` FOREIGN KEY (`rack_id`) REFERENCES `purchasing_racks` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `purchasing_orders`
--
ALTER TABLE `purchasing_orders`
  ADD CONSTRAINT `purchasing_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `purchasing_order_items`
--
ALTER TABLE `purchasing_order_items`
  ADD CONSTRAINT `purchasing_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchasing_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchasing_order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `purchasing_items` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `purchasing_racks`
--
ALTER TABLE `purchasing_racks`
  ADD CONSTRAINT `purchasing_racks_ibfk_1` FOREIGN KEY (`cabinet_id`) REFERENCES `purchasing_cabinets` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchasing_receiving`
--
ALTER TABLE `purchasing_receiving`
  ADD CONSTRAINT `purchasing_receiving_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `purchasing_items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchasing_request_items`
--
ALTER TABLE `purchasing_request_items`
  ADD CONSTRAINT `purchasing_request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchasing_requests` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
