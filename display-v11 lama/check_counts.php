<?php
include 'base.php';
header('Content-Type: application/json');

$conn = new mysqli($host, $user, $pass, $db);
$data = [];

$res = $conn->query("SELECT COUNT(*) as c FROM fleet");
$data['fleet_count'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) as c FROM drivers");
$data['drivers_count'] = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) as c FROM schedule_defaults");
$data['schedule_defaults_count'] = $res->fetch_assoc()['c'];

echo json_encode($data);
?>
