<?php
// fix_trips_db.php
include 'base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$commands = [
    "ALTER TABLE trips ADD COLUMN date DATE NULL",
    "ALTER TABLE trips ADD COLUMN time VARCHAR(10) NULL",
    "ALTER TABLE trips ADD COLUMN note TEXT NULL",
    "ALTER TABLE trips ADD COLUMN createdAt DATETIME DEFAULT CURRENT_TIMESTAMP"
];

foreach ($commands as $sql) {
    echo "Executing: $sql ... ";
    try {
        if ($conn->query($sql) === TRUE) {
            echo "<b style='color:green'>SUCCESS</b><br>";
        } else {
            // Check if error is "Duplicate column name" (Error 1060)
            if ($conn->errno == 1060) {
                echo "<span style='color:orange'>Skipped (Column exists)</span><br>";
            } else {
                echo "<b style='color:red'>ERROR: " . $conn->error . "</b><br>";
            }
        }
    } catch (Exception $e) {
        echo "<b style='color:red'>EXCEPTION: " . $e->getMessage() . "</b><br>";
    }
}

echo "<br>Done! Silakan coba Dispatcher lagi.";
?>
