<?php
include '../base.php'; // Adjust path if needed
ini_set('display_errors', 1); error_reporting(E_ALL);

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Koneksi Gagal: " . $conn->connect_error);

echo "<h2>Checking Database for 'refund_status'...</h2>";

// Check if column exists
$checkSql = "SHOW COLUMNS FROM cancelled_bookings LIKE 'refund_status'";
$result = $conn->query($checkSql);

if ($result && $result->num_rows > 0) {
    echo "Column 'refund_status' already exists.<br>";
} else {
    echo "Column 'refund_status' NOT found. Adding...<br>";
    $sql = "ALTER TABLE cancelled_bookings ADD COLUMN refund_status ENUM('Pending', 'Refunded') DEFAULT 'Pending'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'refund_status' added SUCCESSFULLY.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
}

// Optional: Add updated_at for logs if not exists?
// For now, just refund_status is enough.

echo "Done.";
?>
