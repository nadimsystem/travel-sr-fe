<?php
// FILE: setup_booking_logs.php
include 'api.php';

$sql = "CREATE TABLE IF NOT EXISTS booking_logs (
    id BIGINT PRIMARY KEY,
    booking_id VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    admin_name VARCHAR(50) NOT NULL,
    prev_value TEXT,
    new_value TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table booking_logs created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
