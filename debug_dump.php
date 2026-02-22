<?php
require 'display-v12/config.php'; // Adjust path as needed

header('Content-Type: application/json');

$data = [];

// Get Routes
$res = $conn->query("SELECT * FROM routes");
while($row = $res->fetch_assoc()) {
    $data['routes'][] = $row;
}

// Get Recent Bookings
$res = $conn->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 10");
while($row = $res->fetch_assoc()) {
    $data['bookings'][] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
