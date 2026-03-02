<?php
include 'base.php';
header('Content-Type: application/json');

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => $conn->connect_error]));
}

$res = $conn->query("SELECT id, date, time, status, routeConfig, fleet, driver FROM trips ORDER BY createdAt DESC LIMIT 5");
$trips = [];
while ($row = $res->fetch_assoc()) {
    $trips[] = $row;
}
echo json_encode($trips, JSON_PRETTY_PRINT);
?>
