<?php
// Setup Broadcast Database
include '../display-v11/base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS broadcast_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20),
    name VARCHAR(100),
    message TEXT,
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table broadcast_queue created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
