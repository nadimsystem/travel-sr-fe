<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'Staff',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully.\n";
    
    // Check if default admin exists, if not create one
    $check = $conn->query("SELECT * FROM users WHERE username = 'admin'");
    if ($check->num_rows == 0) {
        $id = time() . rand(100, 999);
        $passHash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = "INSERT INTO users (id, username, password, name, role) VALUES ('$id', 'admin', '$passHash', 'Super Admin', 'Admin')";
        if ($conn->query($insert) === TRUE) {
            echo "Default admin user created (User: admin, Pass: admin123).\n";
        } else {
            echo "Error creating default admin: " . $conn->error . "\n";
        }
    } else {
        echo "Default admin already exists.\n";
    }

} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
