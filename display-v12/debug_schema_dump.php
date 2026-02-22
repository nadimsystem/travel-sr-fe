<?php
include 'base.php'; // Defines $host, $user, $pass, $db

// Connect using mysqli
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = ['bookings', 'trips', 'packages', 'fleet', 'drivers', 'routes'];
$schema = [];

foreach ($tables as $table) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $schema[$table] = $rows;
    } else {
        $schema[$table] = "Error: " . $conn->error;
    }
}

echo json_encode($schema, JSON_PRETTY_PRINT);
$conn->close();
?>
