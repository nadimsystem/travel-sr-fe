<?php
require_once 'base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add bookingNote column if not exists
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'bookingNote'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE bookings ADD COLUMN bookingNote TEXT NULL AFTER destinationAccount";
    if ($conn->query($sql)) {
        echo "Column bookingNote added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column bookingNote already exists.\n";
}
?>
