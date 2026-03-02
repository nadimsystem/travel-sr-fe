<?php
$conn = new mysqli("localhost", "root", "", "sutanraya_v11");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW COLUMNS FROM bookings");
echo "Column | Type | Null | Key | Default | Extra\n";
echo "---|---|---|---|---|---\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . "\n";
}
?>
