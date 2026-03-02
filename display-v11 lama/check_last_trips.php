<?php
// check_last_trips.php
include 'base.php';

header('Content-Type: text/html');

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Last 10 Trips in Database</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%'>";
echo "<tr style='background:#eee'><th>ID</th><th>Date</th><th>Time</th><th>Route ID</th><th>Fleet/Driver</th><th>Status</th><th>Raw RouteConfig</th></tr>";

$sql = "SELECT * FROM trips ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $route = json_decode($row['routeConfig'], true);
        $fleet = json_decode($row['fleet'], true);
        $driver = json_decode($row['driver'], true);
        
        $routeId = isset($route['id']) ? $route['id'] : '-';
        $fleetName = isset($fleet['name']) ? $fleet['name'] : '-';
        $driverName = isset($driver['name']) ? $driver['name'] : '-';
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['time'] . "</td>";
        echo "<td>" . $routeId . "</td>";
        echo "<td>$fleetName / $driverName</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td><small>" . htmlspecialchars(substr($row['routeConfig'], 0, 50)) . "...</small></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No trips found</td></tr>";
}
echo "</table>";
?>
