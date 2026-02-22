<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add position column
try {
    $conn->query("ALTER TABLE users ADD COLUMN position VARCHAR(100) DEFAULT 'Staff'");
    echo "Added 'position' column.\n";
} catch (Exception $e) { echo "Column 'position' error: " . $e->getMessage() . "\n"; }

// Add placement column
try {
    $conn->query("ALTER TABLE users ADD COLUMN placement VARCHAR(50) DEFAULT 'Padang'");
    echo "Added 'placement' column.\n";
} catch (Exception $e) { echo "Column 'placement' error: " . $e->getMessage() . "\n"; }

echo "User schema updated.";
$conn->close();
?>
