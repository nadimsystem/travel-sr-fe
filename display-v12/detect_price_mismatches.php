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

// 2. Get All Active Bookings
$sql = "SELECT id, date, passengerName, routeId, passengerType, seatCount, totalPrice, priceType, serviceType, seatNumbers 
        FROM bookings 
        WHERE status != 'Cancelled' 
        AND serviceType = 'Travel'
        AND priceType != 'Manual'
        ORDER BY date DESC";

$res = $conn->query($sql);

$anomalies = [];
$totalChecked = 0;

while($row = $res->fetch_assoc()) {
    $totalChecked++;
    $rid = $row['routeId'];
    
    // Skip if route not found
    if (!isset($routes[$rid])) continue;

    $route = $routes[$rid];
    
    // Determine expected unit price
    $unitPrice = ($row['passengerType'] === 'Pelajar') ? $route['price_pelajar'] : $route['price_umum'];
    
    // Calculate expected total
    $seatCount = intval($row['seatCount']);
    if ($seatCount < 1) $seatCount = 1; // Safety
    
    $expectedTotal = $unitPrice * $seatCount;
    $storedTotal = floatval($row['totalPrice']);
    
    // Check for mismatch (allow small float diff)
    if (abs($storedTotal - $expectedTotal) > 1.0) {
        // Double check: maybe it's a legacy price? 
        // But the user describes a very specific "half price" logic (total staying same as seat count doubles)
        // implying storedTotal is exactly 1-seat price usually.
        
        $anomalies[] = [
            'id' => $row['id'],
            'date' => $row['date'],
            'name' => $row['passengerName'],
            'route' => $rid,
            'seats' => $row['seatCount'] . " (" . $row['seatNumbers'] . ")",
            'type' => $row['passengerType'],
            'stored_total' => $storedTotal,
            'expected_total' => $expectedTotal,
            'diff' => $storedTotal - $expectedTotal,
            'unit_price_should_be' => $unitPrice,
            'current_unit_implied' => ($seatCount > 0 ? $storedTotal / $seatCount : 0)
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    'total_checked' => $totalChecked,
    'anomaly_count' => count($anomalies),
    'anomalies' => $anomalies
], JSON_PRETTY_PRINT);
?>
