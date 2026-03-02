<?php
// Script to Force Close ALL "On Trip" trips
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Starting Force Close for ALL 'On Trip' records...\n";

// 1. Get ALL trips with status 'On Trip' (Ignoring Date)
$sql = "SELECT * FROM trips WHERE status = 'On Trip'";
$result = $conn->query($sql);

$count = 0;
if ($result->num_rows > 0) {
    while($trip = $result->fetch_assoc()) {
        $tripId = $trip['id'];
        echo "Processing Trip ID: $tripId... ";
        
        // A. Update Trip Status
        $conn->query("UPDATE trips SET status='Tiba' WHERE id='$tripId'");
        
        // B. Release Fleet
        $fleetData = json_decode($trip['fleet'], true);
        if($fleetData && isset($fleetData['id'])) {
            $fid = $fleetData['id'];
            $conn->query("UPDATE fleet SET status='Tersedia' WHERE id='$fid'");
        }
        
        // C. Release Driver
        $driverData = json_decode($trip['driver'], true);
        if($driverData && isset($driverData['id'])) {
            $did = $driverData['id'];
            $conn->query("UPDATE drivers SET status='Standby' WHERE id='$did'");
        }
        
        // D. Update Passengers/Bookings
        $passData = json_decode($trip['passengers'], true);
        if($passData) {
            foreach($passData as $p) {
                 if(isset($p['id'])) {
                     $pid = $p['id'];
                     $conn->query("UPDATE bookings SET status='Tiba' WHERE id='$pid'");
                 }
            }
        }
        
        echo "Done.\n";
        $count++;
    }
} else {
    echo "No active trips found.\n";
}

echo "Finished. Total trips closed: $count\n";
?>
