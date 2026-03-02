<?php
// Default GET Logic (Fetch All Data)

// --- AUTO-CLOSE OLD TRIPS (Maintenance) ---
try {
    // Only target trips that are ALREADY DISPATCHED ('On Trip') AND from previous days.
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $sqlMaintenance = "SELECT * FROM trips WHERE status = 'On Trip' AND date <= '$yesterday'";
    $resMaint = $conn->query($sqlMaintenance);
    
    $tripsToClose = [];
    if ($resMaint && $resMaint->num_rows > 0) {
        while($trip = $resMaint->fetch_assoc()) {
            $tripsToClose[] = $trip;
        }
        $resMaint->free(); 
    }

    if (!empty($tripsToClose)) {
        $conn->begin_transaction();
        try {
            foreach ($tripsToClose as $trip) {
                $tripId = $trip['id'];
                
                // 1. Update Trip Status
                $conn->query("UPDATE trips SET status='Tiba' WHERE id='$tripId'");
                
                // 2. Release Fleet
                $fleetData = json_decode($trip['fleet'], true);
                if($fleetData && isset($fleetData['id'])) {
                    $fid = $fleetData['id'];
                    $conn->query("UPDATE fleet SET status='Tersedia' WHERE id='$fid'");
                }
                
                // 3. Release Driver
                $driverData = json_decode($trip['driver'], true);
                if($driverData && isset($driverData['id'])) {
                    $did = $driverData['id'];
                    $conn->query("UPDATE drivers SET status='Standby' WHERE id='$did'");
                }
                
                // 4. Update Passengers Status
                $passData = json_decode($trip['passengers'], true);
                if($passData) {
                    foreach($passData as $p) {
                            if(isset($p['id'])) {
                                $pid = $p['id'];
                                $conn->query("UPDATE bookings SET status='Tiba' WHERE id='$pid'");
                            }
                    }
                }
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e; 
        }
    }
} catch (Exception $e) {
    file_put_contents('php_error.log', "Auto-Close Error: " . $e->getMessage() . "\n", FILE_APPEND);
}


$data = [
    'bookings' => [],
    'fleet' => [],
    'drivers' => [],
    'trips' => [],
    'routes' => [],
    'busRoutes' => []
];

// Ambil Bookings
$result = $conn->query("SELECT * FROM bookings WHERE status NOT IN ('Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $row['id'] = $row['id']; 
    $row['seatCount'] = (int)$row['seatCount'];
    $row['totalPrice'] = (float)$row['totalPrice'];
    $row['selectedSeats'] = $row['selectedSeats'] ? json_decode($row['selectedSeats']) : [];
    $data['bookings'][] = $row;
}

// Ambil Fleet
$res = $conn->query("SELECT * FROM fleet");
while ($row = $res->fetch_assoc()) {
    $row['id'] = $row['id']; 
    $data['fleet'][] = $row;
}

// Ambil Drivers
$res = $conn->query("SELECT * FROM drivers");
while ($row = $res->fetch_assoc()) {
    $row['id'] = $row['id']; 
    $data['drivers'][] = $row;
}

// Ambil Trips
$res = $conn->query("SELECT * FROM trips");
while ($row = $res->fetch_assoc()) {
    $row['id'] = $row['id']; 
    $row['routeConfig'] = json_decode($row['routeConfig']);
    $row['fleet'] = json_decode($row['fleet']);
    $row['driver'] = json_decode($row['driver']);
    $row['passengers'] = json_decode($row['passengers']);
    $row['batchNumber'] = isset($row['batchNumber']) ? (int)$row['batchNumber'] : 1;
    $data['trips'][] = $row;
}

    // Ambil Routes (Travel)
    $res = $conn->query("SELECT * FROM routes ORDER BY id ASC");
    while($row = $res->fetch_assoc()) {
        $row['schedules'] = json_decode($row['schedules']);
        $row['prices'] = [
            'umum' => (int)$row['price_umum'],
            'pelajar' => (int)$row['price_pelajar'],
            'dropping' => (int)$row['price_dropping'],
            'carter' => (int)$row['price_carter'],
            'payroll_1_6' => (int)$row['payroll_1_6'],
            'payroll_full' => (int)$row['payroll_full']
        ];
        unset($row['price_umum'], $row['price_pelajar'], $row['price_dropping'], $row['price_carter'], $row['payroll_1_6'], $row['payroll_full']);
        $data['routes'][] = $row;
    }

    $data['scheduleDefaults'] = [];
    $res = $conn->query("SELECT * FROM schedule_defaults");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row['fleetId'] = $row['fleetId']; 
            $row['driverId'] = $row['driverId']; 
            $row['batchNumber'] = isset($row['batchNumber']) ? (int)$row['batchNumber'] : 1;
            $data['scheduleDefaults'][] = $row;
        }
    }

    // Ambil Bus Routes
    $res = $conn->query("SELECT * FROM bus_routes");
    while($row = $res->fetch_assoc()) {
        $bigConfig = json_decode($row['big_bus_config'], true);
        $row['big'] = $bigConfig;
        $row['prices'] = [
            's33' => (int)$row['price_s33'],
            's35' => (int)$row['price_s35']
        ];
        $row['isLongTrip'] = (bool)$row['is_long_trip'];
        unset($row['big_bus_config'], $row['price_s33'], $row['price_s35'], $row['is_long_trip']);
        $data['busRoutes'][] = $row;
    }

echo json_encode($data);
exit;
?>
