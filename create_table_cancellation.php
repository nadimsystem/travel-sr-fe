<?php
include 'display-v12/base.php';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create Cancelled Bookings Table (Mirroring Bookings + Extra Fields)
    $sql = "CREATE TABLE IF NOT EXISTS `cancelled_bookings` (
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
      `status` varchar(50) DEFAULT 'Cancelled', -- Default to Cancelled
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
      
      -- Cancellation Specific Fields
      `cancelled_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `cancelled_by` varchar(100) DEFAULT 'System',
      `refund_amount` double DEFAULT 0,
      `refund_account` varchar(255) DEFAULT NULL,
      `cancellation_reason` text DEFAULT NULL,

      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table cancelled_bookings created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
