<?php
include 'base.php';
// Explicitly create connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add 'batchNumber' column to 'bookings' table
try {
    // Check if column exists first (optional, but good practice)
    $check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'batchNumber'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE bookings ADD COLUMN batchNumber INT DEFAULT 1";
        if ($conn->query($sql) === TRUE) {
            echo "Column 'batchNumber' added successfully to 'bookings'.\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "Column 'batchNumber' already exists.\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}

echo "Database update script finished.";
?>
