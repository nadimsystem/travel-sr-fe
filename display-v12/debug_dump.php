<?php
require 'base.php'; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

$data = [];

$res = $conn->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 50");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $data['bookings'][] = $row;
    }
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
