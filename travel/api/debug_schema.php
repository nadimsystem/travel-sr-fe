<?php
// FILE: api/debug_schema.php
// Check column exists for bookings table

$_SERVER['HTTP_HOST'] = 'localhost'; // Mock for CLI
require_once 'db_config.php';

echo "Checking bookings schema...\n";

$sql = "DESCRIBE bookings";
$result = $conn->query($sql);

if ($result) {
    echo "Columns in `bookings`:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ") - " . $row['Extra'] . "\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
