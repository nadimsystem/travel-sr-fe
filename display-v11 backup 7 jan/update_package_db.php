<?php
include 'base.php';
// Explicitly create connection since base.php only defines vars
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create 'packages' table
$sqlPackages = "CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `receiptNumber` varchar(50) DEFAULT NULL, -- No Resi Manual/Auto
  `senderName` varchar(100) DEFAULT NULL,
  `senderPhone` varchar(50) DEFAULT NULL,
  `receiverName` varchar(100) DEFAULT NULL,
  `receiverPhone` varchar(50) DEFAULT NULL,
  `itemDescription` text DEFAULT NULL,
  `itemType` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL, -- Pool to Pool, Door to Door
  `route` varchar(100) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT 0,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `pickupAddress` text DEFAULT NULL,
  `dropoffAddress` text DEFAULT NULL,
  `mapLink` text DEFAULT NULL,
  `bookingDate` varchar(20) DEFAULT NULL,
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sqlPackages) === TRUE) {
    echo "Table 'packages' created successfully.\n";
} else {
    echo "Error creating table 'packages': " . $conn->error . "\n";
}

// 2. Create 'package_logs' table (Tracking History)
$sqlLogs = "CREATE TABLE IF NOT EXISTS `package_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_id` BIGINT(20) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sqlLogs) === TRUE) {
    echo "Table 'package_logs' created successfully.\n";
} else {
    echo "Error creating table 'package_logs': " . $conn->error . "\n";
}

// 3. Add 'receiptNumber' column if not exists (for existing packages table)
try {
    $conn->query("ALTER TABLE packages ADD COLUMN receiptNumber varchar(50) DEFAULT NULL AFTER id");
    echo "Column 'receiptNumber' added/checked.\n";
} catch (Exception $e) {
    // Ignore if exists
}

echo "Database updated successfully.";
?>
