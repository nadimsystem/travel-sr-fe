<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP
$db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW COLUMNS FROM trips");
if ($result) {
    echo "Columns in trips table:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . $row['Extra'] . "\n";
    }
} else {
    echo "Error showing columns: " . $conn->error;
}
?>
