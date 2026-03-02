<?php
include 'base.php';

$conn = getDbConnection();

echo "Setting up 'adminbus' Tables...\n";

// 1. bus_fleet
$sqlFleet = "CREATE TABLE IF NOT EXISTS `bus_fleet` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `plateNumber` VARCHAR(20) NOT NULL,
  `capacity` INT DEFAULT 0,
  `photo` TEXT,
  `status` VARCHAR(50) DEFAULT 'Tersedia', -- Tersedia, Jalan, Perbaikan
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sqlFleet) === TRUE) {
    echo "Table 'bus_fleet' created/checked.\n";
} else {
    echo "Error 'bus_fleet': " . $conn->error . "\n";
}

// 2. bus_drivers
$sqlDrivers = "CREATE TABLE IF NOT EXISTS `bus_drivers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `photo` TEXT,
  `status` VARCHAR(50) DEFAULT 'Standby', -- Standby, Jalan, Cuti
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sqlDrivers) === TRUE) {
    echo "Table 'bus_drivers' created/checked.\n";
} else {
    echo "Error 'bus_drivers': " . $conn->error . "\n";
}

// 3. bus_bookings
$sqlBookings = "CREATE TABLE IF NOT EXISTS `bus_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `bookingCode` VARCHAR(50) NOT NULL, -- BUS-ORD-RAND
  `customerName` VARCHAR(100) NOT NULL,
  `customerPhone` VARCHAR(50) DEFAULT NULL,
  
  `tripDateStart` DATE NOT NULL,
  `tripDateEnd` DATE NOT NULL,
  `durationDays` INT DEFAULT 1,
  
  `pickupLocation` TEXT DEFAULT NULL,
  `dropoffLocation` TEXT DEFAULT NULL, 
  `routeDescription` TEXT DEFAULT NULL, -- Destination/Route details
  
  `totalPrice` DECIMAL(15,2) DEFAULT 0,
  `dpAmount` DECIMAL(15,2) DEFAULT 0,
  `paymentStatus` VARCHAR(50) DEFAULT 'Belum Lunas', -- Lunas, Belum Lunas
  `paymentMethod` VARCHAR(50) DEFAULT 'Cash',
  
  `fleetId` INT DEFAULT NULL,
  `driverId` INT DEFAULT NULL,
  
  `status` VARCHAR(50) DEFAULT 'Pending', -- Pending, Confirmed, On Trip, Completed, Cancelled
  `notes` TEXT DEFAULT NULL,
  
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (fleetId) REFERENCES bus_fleet(id) ON DELETE SET NULL,
  FOREIGN KEY (driverId) REFERENCES bus_drivers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sqlBookings) === TRUE) {
    echo "Table 'bus_bookings' created/checked.\n";
} else {
    echo "Error 'bus_bookings': " . $conn->error . "\n";
}

$conn->close();
echo "Done.";
?>
