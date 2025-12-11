<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create trips table
$sql = "CREATE TABLE IF NOT EXISTS trips (
    id VARCHAR(50) PRIMARY KEY,
    routeConfig TEXT,
    fleet TEXT,
    driver TEXT,
    passengers TEXT,
    status VARCHAR(50),
    createdAt DATETIME,
    note TEXT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'trips' created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
