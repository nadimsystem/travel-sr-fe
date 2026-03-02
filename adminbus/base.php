<?php
$host = 'localhost';
$user = 'root';      
$pass = '';          
$db   = 'sutanraya_v11'; 

// Common Helper for DB Connection if needed
function getDbConnection() {
    global $host, $user, $pass, $db;
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
