<?php
include 'base.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = "Nadima";
$password = "10101010";
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$name = "Nadima Owner";
$role = "Owner";
$id = time();

// Create Table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL
)");

$stmt = $conn->prepare("INSERT INTO users (id, username, password, name, role) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("sssss", $id, $username, $passwordHash, $name, $role);

if ($stmt->execute()) {
    echo "User created successfully.\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?>
