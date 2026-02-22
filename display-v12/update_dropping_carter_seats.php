<?php
// FILE: update_dropping_carter_seats.php
// Script to update all 'Dropping' and 'Carter' bookings to have 8 seats
// and set seatNumbers to 'Full Unit'

header("Content-Type: text/plain");
include 'base.php'; // DB Connection

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    echo "Connected to Database: $db\n\n";

    $sql = "UPDATE bookings 
            SET 
                seatCount = 8, 
                seatCapacity = 8,
                seatNumbers = 'Full Unit',
                passengerType = 'Umum'
            WHERE 
                (serviceType = 'Dropping' OR serviceType = 'Carter') 
                AND (seatCount IS NULL OR seatCount < 8 OR passengerType != 'Umum')";

    echo "Executing SQL:\n$sql\n\n";

    $start = microtime(true);
    if ($conn->query($sql) === TRUE) {
        $affected = $conn->affected_rows;
        echo "SUCCESS: Updated $affected rows.\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
    
    // Optional: Verify
    $result = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE (serviceType = 'Dropping' OR serviceType = 'Carter') AND seatCount = 8");
    $row = $result->fetch_assoc();
    echo "Current Dropping/Carter bookings with 8 seats: " . $row['cnt'] . "\n";

    echo "\nTime: " . number_format(microtime(true) - $start, 4) . "s\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
?>
