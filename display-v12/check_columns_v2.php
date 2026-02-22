<?php
require 'base.php';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $stmt = $conn->query("SHOW COLUMNS FROM bookings");
    $columns = [];
    while ($row = $stmt->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    echo "Columns in bookings table:\n";
    print_r($columns);
    
    if (in_array('physicalRouteId', $columns)) {
        echo "\nFound 'physicalRouteId' column.\n";
    } else {
        echo "\n'physicalRouteId' column NOT FOUND.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
