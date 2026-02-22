<?php
include 'ops/db_config.php';

$sql = "SELECT * FROM bookings LIMIT 1";
$result = $conn->query($sql);

if ($result) {
    if ($row = $result->fetch_assoc()) {
        echo "Columns found:\n";
        foreach (array_keys($row) as $col) {
            echo "- " . $col . "\n";
        }
    } else {
        // Table might be empty, try DESCRIBE
        $sql = "SHOW COLUMNS FROM bookings";
        $result = $conn->query($sql);
        echo "Columns from SHOW COLUMNS:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . "\n";
        }
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
