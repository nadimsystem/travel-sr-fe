<?php
require 'base.php'; // Contains $host, $user, $pass, $db

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

// 2. Scan and Fix
$sql = "SELECT id, date, passengerName, routeId, passengerType, seatCount, totalPrice, priceType, serviceType 
        FROM bookings 
        WHERE status != 'Cancelled' 
        AND serviceType = 'Travel'
        AND priceType != 'Manual'
        ORDER BY date DESC";

$res = $conn->query($sql);

$updatedCount = 0;
$log = [];

while($row = $res->fetch_assoc()) {
    $rid = $row['routeId'];
    if (!isset($routes[$rid])) continue;
    $route = $routes[$rid];
    
    $unitPrice = ($row['passengerType'] === 'Pelajar') ? $route['price_pelajar'] : $route['price_umum'];
    $seatCount = intval($row['seatCount']);
    if ($seatCount < 1) $seatCount = 1;
    
    $expectedTotal = $unitPrice * $seatCount;
    $storedTotal = floatval($row['totalPrice']);
    
    // Check for mismatch
    if (abs($storedTotal - $expectedTotal) > 1.0) {
        $id = $row['id'];
        $updateSql = "UPDATE bookings SET totalPrice = $expectedTotal WHERE id = '$id'";
        if ($conn->query($updateSql) === TRUE) {
            $updatedCount++;
            $log[] = "Fixed booking $id ({$row['passengerName']}): $storedTotal -> $expectedTotal";
        } else {
            $log[] = "Failed to update $id: " . $conn->error;
        }
    }
}

echo json_encode([
    'status' => 'success',
    'updated_count' => $updatedCount,
    'log' => $log
], JSON_PRETTY_PRINT);
?>
