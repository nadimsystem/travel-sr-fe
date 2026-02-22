<?php
include 'base.php';
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';
$conn = new mysqli($host, $user, $pass, $db);

echo "<h1>Debug Database Schema</h1>";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>schedule_defaults Columns</h2>";
$res = $conn->query("DESCRIBE schedule_defaults");
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Key</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

echo "<h2>schedule_defaults Indices</h2>";
$res = $conn->query("SHOW INDEX FROM schedule_defaults");
echo "<table border='1'><tr><th>Key_name</th><th>Column_name</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['Key_name']}</td><td>{$row['Column_name']}</td></tr>";
}
echo "</table>";
?>
