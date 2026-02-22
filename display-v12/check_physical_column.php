<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);
$result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'physicalRouteId'");
if ($result && $result->num_rows > 0) {
    echo "EXISTS";
} else {
    echo "MISSING";
}
?>
