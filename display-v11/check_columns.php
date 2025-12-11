<?php
require 'api.php';
$result = $conn->query("SHOW COLUMNS FROM bookings");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
