<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

echo "<h3>Triggers on trips:</h3>";
$triggers = $conn->query("SHOW TRIGGERS LIKE 'trips'");
if ($triggers) {
    echo "<pre>";
    while ($row = $triggers->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error showing triggers: " . $conn->error;
}

echo "<h3>Check ID 0 in trips:</h3>";
$check0 = $conn->query("SELECT * FROM trips WHERE id = 0");
if ($check0->num_rows > 0) {
    echo "Found row with ID 0.<br>";
    print_r($check0->fetch_assoc());
} else {
    echo "No row with ID 0 found.";
}

echo "<h3>Check ID 0 in bookings:</h3>";
$checkB0 = $conn->query("SELECT * FROM bookings WHERE id = '0' OR id = 0");
if ($checkB0->num_rows > 0) {
    echo "Found row with ID 0 in bookings.<br>";
} else {
    echo "No row with ID 0 in bookings.";
}
?>
