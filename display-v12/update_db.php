<?php
// Script to Update Database Schema for V11
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Checking Database Schema...\n";

// 1. Check TRIPS table columns
$columns = [];
$res = $conn->query("SHOW COLUMNS FROM trips");
while($row = $res->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Add 'date' if missing
if (!in_array('date', $columns)) {
    echo "Adding 'date' column to trips...\n";
    $conn->query("ALTER TABLE trips ADD COLUMN date DATE DEFAULT NULL AFTER status");
}

// Add 'time' if missing
if (!in_array('time', $columns)) {
    echo "Adding 'time' column to trips...\n";
    $conn->query("ALTER TABLE trips ADD COLUMN time VARCHAR(10) DEFAULT NULL AFTER date");
}

// Add 'note' if missing
if (!in_array('note', $columns)) {
    echo "Adding 'note' column to trips...\n";
    $conn->query("ALTER TABLE trips ADD COLUMN note TEXT DEFAULT NULL AFTER time");
}

// 2. Check SCHEDULE_DEFAULTS table
$tableExists = $conn->query("SHOW TABLES LIKE 'schedule_defaults'");
if ($tableExists->num_rows == 0) {
    echo "Creating 'schedule_defaults' table...\n";
    $sql = "CREATE TABLE `schedule_defaults` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `routeId` varchar(50) NOT NULL,
      `time` varchar(10) NOT NULL,
      `fleetId` int(11) DEFAULT NULL,
      `driverId` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `route_time_unique` (`routeId`,`time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($sql);
} else {
    echo "'schedule_defaults' table exists.\n";
}

echo "Database Update Completed.\n";
?>
