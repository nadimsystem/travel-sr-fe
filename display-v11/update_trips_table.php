<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add createdAt column if not exists
try {
    $conn->query("ALTER TABLE trips ADD COLUMN createdAt DATETIME");
    echo "Added 'createdAt' column.\n";
} catch (Exception $e) {
    echo "Column 'createdAt' might already exist or error: " . $e->getMessage() . "\n";
}

// Add note column if not exists
try {
    $conn->query("ALTER TABLE trips ADD COLUMN note TEXT");
    echo "Added 'note' column.\n";
} catch (Exception $e) {
    echo "Column 'note' might already exist or error: " . $e->getMessage() . "\n";
}

echo "Database update completed.";
$conn->close();
?>
