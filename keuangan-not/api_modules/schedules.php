<?php

// --- D. DISPATCH TRIP BARU ---
if ($action === 'dispatch_trip') {
    $t = $input['data'];
    $conn->begin_transaction();
    try {
        $tripId = isset($t['id']) && $t['id'] ? $t['id'] : null;
        
        $routeJson = json_encode($t['routeConfig']);
        $fleetJson = json_encode($t['fleet']);
        $driverJson = json_encode($t['driver']);
        $passJson = json_encode($t['passengers']);
        $status = 'On Trip'; 
        
        $date = $t['date'];
        $time = $t['time'];
        $unitNumber = isset($t['batchNumber']) ? (int)$t['batchNumber'] : 1;

        if ($tripId) {
             // Check if ID exists (for UPDATE)
             $check = $conn->query("SELECT id FROM trips WHERE id='$tripId'");
             if ($check && $check->num_rows > 0) {
                 // UPDATE
                 $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=?, unitNumber=? WHERE id=?");
                 $stmt->bind_param("sssssssis", $routeJson, $fleetJson, $driverJson, $passJson, $status, $date, $time, $unitNumber, $tripId);
             } else {
                 // INSERT specific ID (Rare, but maybe recovering?) OR just treat as new if not found?
                 // Safer to insert as new if ID not found but it was passed? 
                 // Actually, if ID is passed, but not found, it might be a logic error. 
                 // But let's assume if ID is passed, we try to UPDATE. If not found, we INSERT (but let DB gen ID).
                 $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, unitNumber) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
                 $stmt->bind_param("sssssssi", $routeJson, $fleetJson, $driverJson, $passJson, $status, $date, $time, $unitNumber);
             }
        } else {
             // INSERT NEW (Auto Increment)
             $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, unitNumber) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)");
             $stmt->bind_param("sssssssi", $routeJson, $fleetJson, $driverJson, $passJson, $status, $date, $time, $unitNumber);
        }
        
        if (!$stmt->execute()) throw new Exception("Gagal Save Trip: " . $stmt->error);

        // 2. Update Fleet Status -> On Trip
        if (isset($t['fleet']['id'])) {
            $fid = $t['fleet']['id'];
            $conn->query("UPDATE fleet SET status='On Trip' WHERE id='$fid'");
        }

        // 3. Update Driver Status -> Jalan
        if (isset($t['driver']['id'])) {
            $did = $t['driver']['id'];
            $conn->query("UPDATE drivers SET status='Jalan' WHERE id='$did'");
        }

        // 4. Update Status Booking Penumpang -> On Trip
        $stmtPsg = $conn->prepare("UPDATE bookings SET status='On Trip' WHERE id=?");
        if (!empty($t['passengers'])) {
            foreach ($t['passengers'] as $p) {
                $stmtPsg->bind_param("s", $p['id']);
                $stmtPsg->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'create_trip') {
    $t = $input['data'];
    
    $conn->begin_transaction();
    try {
        // 1. Insert Trip (LANGSUNG TIBA / SELESAI)
        $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, departureTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $routeJson = json_encode($t['routeConfig']);
        $fleetJson = json_encode($t['fleet']);
        $driverJson = json_encode($t['driver']);
        $passJson = json_encode($t['passengers']);
        $now = date('Y-m-d H:i:s');
        $status = 'Tiba'; // Langsung selesai
        
        $stmt->bind_param("sssssss", $t['id'], $routeJson, $fleetJson, $driverJson, $passJson, $status, $now); 
        $stmt->execute();

        // 3. Update Status Booking Penumpang -> LANGSUNG TIBA
        $stmtPsg = $conn->prepare("UPDATE bookings SET status='Tiba' WHERE id=?");
        if (count($t['passengers']) > 0) {
                foreach ($t['passengers'] as $p) {
                $stmtPsg->bind_param("s", $p['id']);
                $stmtPsg->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- D. MANAJEMEN TRIP (PENUGASAN HARIAN) ---
if ($action === 'save_trip') {
    $data = $input['data'];
    $id = $data['id'];
    $routeConfig = json_encode($data['routeConfig']);
    $fleet = json_encode($data['fleet']);
    $driver = json_encode($data['driver']);
    $passengers = json_encode($data['passengers']);
    $status = $data['status'];
    $date = $data['date'];
    $time = $data['time'];
    $note = isset($data['note']) ? $data['note'] : '';
    $batchNumber = isset($data['batchNumber']) ? (int)$data['batchNumber'] : 1;
    $createdAt = date('Y-m-d H:i:s');

    $stmtCheck = $conn->prepare("SELECT id FROM trips WHERE id=?");
    $stmtCheck->bind_param("s", $id);
    $stmtCheck->execute();
    
    if ($stmtCheck->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=?, note=?, unitNumber=? WHERE id=?");
        if(!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Update Prepare Failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("ssssssssis", $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $batchNumber, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, note, unitNumber, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if(!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Insert Prepare Failed: ' . $conn->error]);
                exit;
        }
        $stmt->bind_param("ssssssssiss", $id, $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $batchNumber, $createdAt);
    }

    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    exit;
}

// --- E. UPDATE TRIP STATUS (Tiba/Kendala) ---
// ... (unchanged)

// --- SCHEDULE DEFAULTS ---
if ($action === 'save_schedule_default') {
    $routeId = $input['routeId'];
    $time = $input['time'];
    $fleetId = $input['fleetId'];
    $driverId = $input['driverId'];
    $batchNumber = isset($input['batchNumber']) ? (int)$input['batchNumber'] : 1;
    
    // Fix: Delete existing default first (scoped by batchNumber)
    $del = $conn->prepare("DELETE FROM schedule_defaults WHERE routeId=? AND time=? AND unitNumber=?");
    $del->bind_param("ssi", $routeId, $time, $batchNumber);
    $del->execute();
    
    $stmt = $conn->prepare("INSERT INTO schedule_defaults (routeId, time, fleetId, driverId, unitNumber) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Prepare Failed: ' . $conn->error]);
            exit;
    }
    $stmt->bind_param("ssssi", $routeId, $time, $fleetId, $driverId, $batchNumber);
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

?>
