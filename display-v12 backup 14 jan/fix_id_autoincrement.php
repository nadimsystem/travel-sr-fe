<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Adding AUTO_INCREMENT to trips.id...\n";

// 1. Check if Primary Key exists
$res = $conn->query("SHOW KEYS FROM trips WHERE Key_name = 'PRIMARY'");
$isPk = ($res && $res->num_rows > 0);

try {
    if ($isPk) {
        // Just modify to add AUTO_INCREMENT
        $sql = "ALTER TABLE trips MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT";
    } else {
        // Add PK and AUTO_INCREMENT
        $sql = "ALTER TABLE trips MODIFY id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY";
    }
    
    if ($conn->query($sql)) {
        echo "SUCCESS: trips.id is now AUTO_INCREMENT.\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
?>
