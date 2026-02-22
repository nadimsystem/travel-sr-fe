<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'physicalRouteId'");
if ($result->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE bookings ADD COLUMN physicalRouteId VARCHAR(255) NULL AFTER routeId";
    if ($conn->query($sql) === TRUE) {
        echo "Column physicalRouteId added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column physicalRouteId already exists";
}

$conn->close();
?>
