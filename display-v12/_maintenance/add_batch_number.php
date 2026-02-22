<?php
include dirname(__DIR__) . '/base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 1. Add batchNumber to trips
$sql = "SHOW COLUMNS FROM trips LIKE 'batchNumber'";
$res = $conn->query($sql);
if ($res->num_rows == 0) {
    if ($conn->query("ALTER TABLE trips ADD COLUMN batchNumber INT DEFAULT 1")) {
        echo "Added batchNumber to trips table.\n";
    } else {
        echo "Error altering trips: " . $conn->error . "\n";
    }
} else {
    echo "batchNumber already exists in trips.\n";
}

// 2. Add batchNumber to schedule_defaults
$sql = "SHOW COLUMNS FROM schedule_defaults LIKE 'batchNumber'";
$res = $conn->query($sql);
if ($res->num_rows == 0) {
    // We need to drop the unique key first because we need to include batchNumber in uniqueness
    $conn->query("ALTER TABLE schedule_defaults DROP INDEX unique_schedule");
    
    if ($conn->query("ALTER TABLE schedule_defaults ADD COLUMN batchNumber INT DEFAULT 1")) {
        echo "Added batchNumber to schedule_defaults table.\n";
        // Re-add unique key with batchNumber
        if ($conn->query("ALTER TABLE schedule_defaults ADD UNIQUE KEY unique_schedule (routeId, time, batchNumber)")) {
            echo "Updated unique key on schedule_defaults.\n";
        } else {
            echo "Error adding unique key: " . $conn->error . "\n";
        }
    } else {
        echo "Error altering schedule_defaults: " . $conn->error . "\n";
    }
} else {
    echo "batchNumber already exists in schedule_defaults.\n";
}

$conn->close();
?>
