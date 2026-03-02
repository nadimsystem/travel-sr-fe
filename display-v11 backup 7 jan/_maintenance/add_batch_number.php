<?php
// Migration script to add batchNumber column to bookings table
// Run this once to update the database schema

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'batchNumber'");
if ($result->num_rows > 0) {
    echo "Column 'batchNumber' already exists.\n";
} else {
    // Add the column
    $sql = "ALTER TABLE bookings ADD COLUMN batchNumber INT DEFAULT 1";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'batchNumber' added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migration complete.\n";
?>
