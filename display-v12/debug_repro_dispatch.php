<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);
$conn->begin_transaction();

try {
    echo "Attempting INSERT into trips (no ID)...<br>";
    $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, unitNumber) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    
    $dummy = "{}";
    $unit = 1;
    $date = date('Y-m-d');
    $time = "08:00";
    
    $stmt->bind_param("sssssssi", $dummy, $dummy, $dummy, $dummy, $dummy, $date, $time, $unit);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $newId = $stmt->insert_id;
    echo "Success! New ID: " . $newId . "<br>";
    
    $conn->rollback(); // Don't actually save
    echo "Rolled back.";
    
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage();
    $conn->rollback();
}
?>
