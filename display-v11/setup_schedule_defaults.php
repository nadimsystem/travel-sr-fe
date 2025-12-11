<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create schedule_defaults table
$sql = "CREATE TABLE IF NOT EXISTS schedule_defaults (
    id INT AUTO_INCREMENT PRIMARY KEY,
    routeId VARCHAR(50),
    time VARCHAR(10),
    fleetId VARCHAR(50),
    driverId VARCHAR(50),
    UNIQUE KEY unique_schedule (routeId, time)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'schedule_defaults' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
