<?php
include 'base.php';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Turn off error reporting that halts execution on warning in strict mode
mysqli_report(MYSQLI_REPORT_OFF);

echo "Running migration...\n";

$sql1 = "ALTER TABLE bookings ADD COLUMN pickupAddress TEXT DEFAULT NULL";
if($conn->query($sql1)) echo "Success: Added pickupAddress\n";
else echo "Note (pickupAddress): " . $conn->error . "\n";

$sql2 = "ALTER TABLE bookings ADD COLUMN dropoffAddress TEXT DEFAULT NULL";
if($conn->query($sql2)) echo "Success: Added dropoffAddress\n";
else echo "Note (dropoffAddress): " . $conn->error . "\n";

echo "Done.\n";
