<?php
// test_api_trips.php
include 'base.php';

header('Content-Type: application/json');

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

$data = [];
$res = $conn->query("SELECT * FROM trips ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    // Mimic api.php logic
    $row['routeConfig_Raw'] = $row['routeConfig']; // Keep raw for debug
    $row['routeConfig'] = json_decode($row['routeConfig']);
    $row['json_error'] = json_last_error_msg(); // Check for decode errors
    
    $row['fleet'] = json_decode($row['fleet']);
    $row['driver'] = json_decode($row['driver']);
    $row['passengers'] = json_decode($row['passengers']);
    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
