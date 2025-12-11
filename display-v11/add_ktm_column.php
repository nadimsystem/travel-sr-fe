<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add ktmProof column if not exists
try {
    $conn->query("ALTER TABLE bookings ADD COLUMN ktmProof TEXT");
    echo "Added 'ktmProof' column.\n";
} catch (Exception $e) {
    echo "Column 'ktmProof' might already exist or error: " . $e->getMessage() . "\n";
}

echo "Database update completed.";
$conn->close();
?>
