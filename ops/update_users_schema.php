<?php
$basePath = __DIR__ . '/base.php';
if (file_exists($basePath)) {
    include $basePath;
} else {
    $altPath = __DIR__ . '/../display-v11/base.php';
    if (file_exists($altPath)) {
        include $altPath;
    }
}

if (!isset($conn) && isset($host)) {
    $conn = new mysqli($host, $user, $pass, $db);
}

if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . (isset($conn) ? $conn->connect_error : "conn variable not set"));
}

echo "Altering table users...\n";

// Check if placement exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'placement'");
if ($check->num_rows == 0) {
    echo "Adding placement...\n";
    $conn->query("ALTER TABLE users ADD COLUMN placement VARCHAR(50) DEFAULT 'Padang'");
}

// Check if created_at exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($check->num_rows == 0) {
    echo "Adding created_at...\n";
    $conn->query("ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
}

echo "Done.\n";
?>
