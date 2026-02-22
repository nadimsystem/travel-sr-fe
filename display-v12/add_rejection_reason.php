<?php
require_once 'base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add rejectionReason column if not exists
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'rejectionReason'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE bookings ADD COLUMN rejectionReason TEXT NULL AFTER bookingNote";
    if ($conn->query($sql)) {
        echo "Column rejectionReason added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column rejectionReason already exists.\n";
}
?>
