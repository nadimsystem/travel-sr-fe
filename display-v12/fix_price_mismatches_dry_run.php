<?php
require 'base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Get Routes Prices
$routes = [];
$res = $conn->query("SELECT * FROM routes");
while($row = $res->fetch_assoc()) {
    $routes[$row['id']] = $row;
}

// 2. Get Anomalies
$sql = "SELECT id, date, passengerName, routeId, passengerType, seatCount, totalPrice, priceType, serviceType 
        FROM bookings 
        WHERE status != 'Cancelled' 
        AND serviceType = 'Travel'
        AND priceType != 'Manual'
        ORDER BY date DESC";

$res = $conn->query($sql);

$updates = [];

while($row = $res->fetch_assoc()) {
    $rid = $row['routeId'];
    if (!isset($routes[$rid])) continue;
    $route = $routes[$rid];
    
    $unitPrice = ($row['passengerType'] === 'Pelajar') ? $route['price_pelajar'] : $route['price_umum'];
    $seatCount = intval($row['seatCount']);
    if ($seatCount < 1) $seatCount = 1;
    
    $expectedTotal = $unitPrice * $seatCount;
    $storedTotal = floatval($row['totalPrice']);
    
    if (abs($storedTotal - $expectedTotal) > 1.0) {
        $updates[] = [
            'id' => $row['id'],
            'old_price' => $storedTotal,
            'new_price' => $expectedTotal,
            'reason' => "Mismatch: $seatCount seats x $unitPrice = $expectedTotal (Stored: $storedTotal)"
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    'count' => count($updates),
    'updates' => $updates
], JSON_PRETTY_PRINT);
?>
