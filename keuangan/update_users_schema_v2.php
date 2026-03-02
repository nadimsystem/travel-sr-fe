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

// Check if password_plain exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'password_plain'");
if ($check->num_rows == 0) {
    echo "Adding password_plain...\n";
    $conn->query("ALTER TABLE users ADD COLUMN password_plain VARCHAR(255) DEFAULT NULL");
} else {
    echo "password_plain already exists.\n";
}

echo "Done.\n";
?>
