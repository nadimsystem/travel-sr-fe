<?php
include 'base.php';
$conn = getDbConnection();

$tables = ['bus_fleet', 'bus_drivers', 'bus_bookings'];
foreach ($tables as $t) {
    echo "Checking $t:\n";
    $res = $conn->query("SHOW COLUMNS FROM $t");
    if ($res) {
        while($row = $res->fetch_assoc()) echo " - " . $row['Field'] . "\n";
    } else {
        echo " - Table NOT FOUND\n";
    }
    echo "\n";
}
?>
