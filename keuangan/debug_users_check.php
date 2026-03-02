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

echo "Table: users\n";
$result = $conn->query("SHOW COLUMNS FROM users");
if (!$result) die("Query failed: " . $conn->error);

while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nTotal Users: ";
$count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
echo $count;

echo "\n\nFirst 5 users:\n";
$users = $conn->query("SELECT * FROM users LIMIT 5");
while($u = $users->fetch_assoc()) {
    print_r($u);
}
?>
