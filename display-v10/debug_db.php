<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'sutanraya';

$conn = new mysqli($host, $user, $password, $dbname);
$res = $conn->query("SELECT id, schedules FROM routes");
while($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Raw Schedules: " . $row['schedules'] . "\n";
    echo "Decoded: " . print_r(json_decode($row['schedules']), true) . "\n";
    echo "-------------------\n";
}
?>
