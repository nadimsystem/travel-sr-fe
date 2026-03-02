<?php
include 'base.php';
$conn = getDbConnection();

echo "Updating 'bus_fleet' schema...\n";

// Add pricePerDay
try {
    $conn->query("ALTER TABLE bus_fleet ADD COLUMN pricePerDay DECIMAL(15,2) DEFAULT 0 AFTER capacity");
    echo "Added pricePerDay.\n";
} catch (Exception $e) { echo "pricePerDay exists or error: " . $e->getMessage() . "\n"; }

echo "Schema update done.";
?>
