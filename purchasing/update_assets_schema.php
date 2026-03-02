<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add 'usage' column to purchasing_assets if it doesn't exist
$sql = "ALTER TABLE purchasing_assets ADD COLUMN IF NOT EXISTS `usage` VARCHAR(100) DEFAULT 'Universal'";
$conn->query($sql);

echo "Database updated: Added 'usage' column to purchasing_assets table.<br>";

$conn->close();
?>
