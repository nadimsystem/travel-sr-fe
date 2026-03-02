<?php
include 'base.php';
$conn = getDbConnection();

echo "Updating 'bus_bookings' schema...\n";

// Add paymentLocation and paymentReceiver
try {
    $conn->query("ALTER TABLE bus_bookings ADD COLUMN paymentLocation VARCHAR(100) DEFAULT NULL AFTER paymentMethod");
    echo "Added paymentLocation.\n";
} catch (Exception $e) { echo "paymentLocation exists or error.\n"; }

try {
    $conn->query("ALTER TABLE bus_bookings ADD COLUMN paymentReceiver VARCHAR(100) DEFAULT NULL AFTER paymentLocation");
    echo "Added paymentReceiver.\n";
} catch (Exception $e) { echo "paymentReceiver exists or error.\n"; }

echo "Schema update done.";
?>
