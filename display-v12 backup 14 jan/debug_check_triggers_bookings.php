<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

echo "<h3>Triggers on bookings:</h3>";
$triggers = $conn->query("SHOW TRIGGERS LIKE 'bookings'");
if ($triggers) {
    echo "<pre>";
    while ($row = $triggers->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error showing triggers: " . $conn->error;
}
?>
