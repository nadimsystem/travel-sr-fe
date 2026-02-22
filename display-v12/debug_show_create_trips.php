<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);
$result = $conn->query("SHOW CREATE TABLE trips");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>" . print_r($row, true) . "</pre>";
} else {
    echo "Error: " . $conn->error;
}
?>
