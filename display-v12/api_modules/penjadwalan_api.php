<?php

if ($action === 'get_schedule_data') {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    $response = [
        'routes' => [],
        'drivers' => [],
        'fleet' => [],
        'trips' => []
    ];

    // 1. Routes
    $res = $conn->query("SELECT * FROM routes ORDER BY id ASC");
    while($row = $res->fetch_assoc()) {
        $row['schedules'] = json_decode($row['schedules']);
        $response['routes'][] = $row;
    }

    // 2. Drivers
    $res = $conn->query("SELECT * FROM drivers WHERE status != 'Non-Active' ORDER BY name ASC");
    while($row = $res->fetch_assoc()) {
        $response['drivers'][] = $row;
    }

    // 3. Fleet
    $res = $conn->query("SELECT * FROM fleet WHERE status != 'Non-Active' ORDER BY name ASC");
    while($row = $res->fetch_assoc()) {
        $response['fleet'][] = $row;
    }

    // 4. Trips (Filtered by Date)
    $stmt = $conn->prepare("SELECT * FROM trips WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['routeConfig'] = json_decode($row['routeConfig']);
        $row['fleet'] = json_decode($row['fleet']);
        $row['driver'] = json_decode($row['driver']);
        $row['passengers'] = json_decode($row['passengers']);
        $response['trips'][] = $row;
    }

    echo json_encode($response);
    exit;
}

if ($action === 'save_schedule_assignment') {
    $data = $input['data'];
    
    $id = isset($data['id']) ? $data['id'] : null;
    $date = $data['date'];
    $time = $data['time'];
    $routeConfig = json_encode($data['routeConfig']);
    $driver = json_encode($data['driver']);
    $fleet = json_encode($data['fleet']);
    // Preserve passengers if existing, else empty
    $passengers = isset($data['passengers']) ? json_encode($data['passengers']) : '[]';
    $status = isset($data['status']) ? $data['status'] : 'Scheduled';
    $unitNumber = 1;
    $currDate = date('Y-m-d H:i:s');

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=? WHERE id=?");
        $stmt->bind_param("sssssssi", $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $id);
    } else {
        // Insert
        // Using NULL for ID to trigger Auto Increment
        $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, unitNumber, createdAt) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssis", $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $unitNumber, $currDate);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}
if ($action === 'duplicate_schedule') {
    $date = $input['date'];
    if (!$date) {
        echo json_encode(['status' => 'error', 'message' => 'Date is required']);
        exit;
    }

    $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
    $currDate = date('Y-m-d H:i:s');

    $conn->begin_transaction();

    try {
        // 1. Clear existing schedule for nextDate (Optional, but safer to prevent duplicates)
        $stmtDelete = $conn->prepare("DELETE FROM trips WHERE date = ?");
        $stmtDelete->bind_param("s", $nextDate);
        $stmtDelete->execute();

        // 2. Fetch today's schedule
        $stmtGet = $conn->prepare("SELECT routeConfig, fleet, driver, passengers, status, time, unitNumber FROM trips WHERE date = ?");
        $stmtGet->bind_param("s", $date);
        $stmtGet->execute();
        $result = $stmtGet->get_result();

        // 3. Insert into next day
        $stmtInsert = $conn->prepare("INSERT INTO trips (routeConfig, fleet, driver, passengers, status, date, time, unitNumber, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        while ($row = $result->fetch_assoc()) {
            // We duplicate the row exactly, but change the date
            $stmtInsert->bind_param("sssssssis", 
                $row['routeConfig'], 
                $row['fleet'], 
                $row['driver'], 
                $row['passengers'], // Copy passengers too? Maybe empty array? User said "Duplicate Schedule", usually implies assignments. Keeping passengers might be wrong for a new day. Let's EMPTY passengers.
                $row['status'], 
                $nextDate, 
                $row['time'], 
                $row['unitNumber'],
                $currDate
            );
            
            // Override passengers to empty array for new day
            $emptyPassengers = '[]';
            // Re-bind with empty passengers
             $stmtInsert->bind_param("sssssssis", 
                $row['routeConfig'], 
                $row['fleet'], 
                $row['driver'], 
                $emptyPassengers, 
                $row['status'], 
                $nextDate, 
                $row['time'], 
                $row['unitNumber'],
                $currDate
            );

            $stmtInsert->execute();
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Schedule duplicated to ' . $nextDate]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
