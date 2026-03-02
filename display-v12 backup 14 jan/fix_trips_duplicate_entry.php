<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h3>Fixing trips table schema...</h3>";

// 1. Check for ID 0 and DELETE it (it's likely a bad record formed by previous failed insert attempts)
$check0 = $conn->query("SELECT id FROM trips WHERE id = 0");
if ($check0 && $check0->num_rows > 0) {
    echo "Found row with ID 0. Deleting...<br>";
    $conn->query("DELETE FROM trips WHERE id = 0");
    echo "Deleted row with ID 0.<br>";
} else {
    echo "No row with ID 0 found.<br>";
}

// 2. ALTER TABLE to ensure AUTO_INCREMENT
try {
    $sql = "ALTER TABLE trips MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT";
    if ($conn->query($sql)) {
        echo "SUCCESS: trips.id is now AUTO_INCREMENT.<br>";
    } else {
        echo "ERROR modifying table: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "EXCEPTION modifying table: " . $e->getMessage() . "<br>";
}

// 3. Reset AUTO_INCREMENT to max(id) + 1
$res = $conn->query("SELECT MAX(id) as max_id FROM trips");
$row = $res->fetch_assoc();
$maxId = $row['max_id'];
$nextId = $maxId + 1;

if ($maxId) {
    echo "Max ID is: " . $maxId . ". Setting AUTO_INCREMENT to " . $nextId . "...<br>";
    $conn->query("ALTER TABLE trips AUTO_INCREMENT = $nextId");
}

echo "<h3>Done.</h3>";
?>
