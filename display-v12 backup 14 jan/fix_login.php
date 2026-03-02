<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Delete 'admin' if exists
$conn->query("DELETE FROM users WHERE username = 'admin'");

// 2. Create 'admin'
$passHash = password_hash('admin123', PASSWORD_DEFAULT);
$id = time(); // Unique ID

$sql = "INSERT INTO users (id, username, password, name, role) VALUES (?, 'admin', ?, 'Administrator', 'Admin')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $id, $passHash);

if ($stmt->execute()) {
    echo "SUCCESS: User 'admin' created with password 'admin123'.";
} else {
    echo "ERROR: " . $stmt->error;
}
?>
