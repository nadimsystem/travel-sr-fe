<?php
// create_refund_table.php
// Script to create the refunds table if it doesn't exist
// Run this once

header('Content-Type: text/plain');

include 'base.php';

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 1);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
    
    echo "Connected to database: $db\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `refunds` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `booking_id` BIGINT(20) NOT NULL,
      `amount` DECIMAL(10,2) NOT NULL,
      `reason` VARCHAR(255),
      `status` ENUM('pending','approved','rejected','processed') DEFAULT 'pending',
      `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `processed_at` DATETIME NULL,
      FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'refunds' checked/created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
