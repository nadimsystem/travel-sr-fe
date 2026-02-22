<?php
include 'display-v12/base.php';
$conn = new mysqli($host, $user, $pass, $db);

// Fetch the specific booking created in the screenshot (Nebeng mas)
// Or just last 5 bookings
$res = $conn->query("SELECT id, passengerName, routeId, physicalRouteId FROM bookings ORDER BY id DESC LIMIT 5");
$data = [];
while($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data, JSON_PRETTY_PRINT);
?>
