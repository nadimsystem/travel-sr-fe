<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "Tables in $database:\n";
    while ($row = $result->fetch_array()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error listing tables: " . $conn->error;
}
?>
