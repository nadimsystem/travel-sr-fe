<?php
// Include configuration
require_once 'base.php';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add columns if they don't exist
$alterQueries = [
    "ALTER TABLE bookings ADD COLUMN paymentReceivedDate DATE NULL",
    "ALTER TABLE bookings ADD COLUMN transferSentDate DATE NULL",
    "ALTER TABLE bookings ADD COLUMN destinationAccount VARCHAR(50) NULL"
];

foreach ($alterQueries as $query) {
    try {
        if ($conn->query($query) === TRUE) {
            echo "Successfully executed: $query\n";
        } else {
            // Ignore if column already exists
            if (strpos($conn->error, "Duplicate column name") !== false) {
                 echo "Column already exists, skipping: $query\n";
            } else {
                echo "Error executing query: " . $conn->error . "\n";
            }
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
             echo "Column already exists, skipping: $query\n";
        } else {
             echo "Exception: " . $e->getMessage() . "\n";
        }
    }
}

echo "Database schema update completed.\n";
?>
