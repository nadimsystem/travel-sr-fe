<?php
include 'base.php';

// Check if column exists
$checkCol = $conn->query("SHOW COLUMNS FROM bookings LIKE 'input_date'");
if ($checkCol->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE bookings ADD COLUMN input_date DATETIME DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'input_date' added successfully to 'bookings' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'input_date' already exists.\n";
}
?>
