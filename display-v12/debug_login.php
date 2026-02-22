<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'base.php'; // or whatever connects to DB

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Debug Login</h1>";

echo "<h2>Users Table</h2>";
$result = $conn->query("SELECT id, username, password, role FROM users");
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Role</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
        
        // Test admin/admin123
        if ($row['username'] == 'admin') {
            echo "<tr><td colspan='4'>Test 'admin123': " . (password_verify('admin123', $row['password']) ? 'MATCH' : 'FAIL') . "</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "No users found.";
}
?>
