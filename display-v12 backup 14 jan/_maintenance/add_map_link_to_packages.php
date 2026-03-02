<?php
// Migration script to add mapLink column to packages table
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists first
$check = $conn->query("SHOW COLUMNS FROM `packages` LIKE 'mapLink'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE `packages` ADD COLUMN `mapLink` TEXT DEFAULT NULL AFTER `dropoffAddress`";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'mapLink' added successfully to 'packages' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'mapLink' already exists in 'packages' table.\n";
}

$conn->close();
echo "Migration complete.\n";
?>
