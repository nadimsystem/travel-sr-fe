<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli($host, $user, $pass, $db);
    echo "Connected to $db<br>";

    // Check if column exists
    $result = $mysqli->query("SHOW COLUMNS FROM `cancelled_bookings` LIKE 'refund_status'");
    if ($result->num_rows == 0) {
        $mysqli->query("ALTER TABLE `cancelled_bookings` ADD `refund_status` ENUM('Pending', 'Refunded') NOT NULL DEFAULT 'Pending'");
        echo "Column 'refund_status' added successfully.<br>";
    } else {
        echo "Column 'refund_status' already exists.<br>";
    }
    
    // Explicitly update existing records to Pending if needed (optional)
    // $mysqli->query("UPDATE cancelled_bookings SET refund_status = 'Pending' WHERE refund_status IS NULL");

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
