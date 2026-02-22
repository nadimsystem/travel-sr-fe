<?php
// FILE: api_modules/trip_history.php

// --- GET TRIPS HISTORY (Dispatched / Completed) ---
if ($action === 'get_trip_history') {
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');
    
    // Fetch trips that are 'On Trip', 'Tiba', 'Selesai' (Dispatched)
    // Exclude 'Pending' or 'Scheduled' or 'Cancelled'
    $sql = "SELECT * FROM trips WHERE date BETWEEN ? AND ? AND status IN ('On Trip', 'Tiba', 'Selesai') ORDER BY date DESC, time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trips = [];
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['routeConfig'] = json_decode($row['routeConfig'], true);
        $row['fleet'] = json_decode($row['fleet'], true);
        $row['driver'] = json_decode($row['driver'], true);
        $row['passengers'] = json_decode($row['passengers'], true);
        
        $route = $row['routeConfig']; // Fix: Define $route variable
        
        // Filter: Passenger must not be 0
        $paxCount = is_array($row['passengers']) ? count($row['passengers']) : 0;
        if ($paxCount === 0) {
            continue;
        }
        
        $payroll = 0;
        $origin = isset($route['origin']) ? strtolower($route['origin']) : '';
        $destination = isset($route['destination']) ? strtolower($route['destination']) : '';
        
        // --- SIMPLIFIED PAYROLL LOGIC ---
        // 1. Get Route ID from the snapshot
        $routeId = isset($route['id']) ? $route['id'] : '';
        
        $payroll1_6 = 0;
        $payrollFull = 0;

        // 2. Always try to fetch FRESH rates from the routes table first
        if (!empty($routeId)) {
            // We use a separate query or cache this? For 50 trips, 50 queries is okay for now.
            // Optimization: Could fetch all routes into an array matching keys, but let's keep it simple as requested.
            $stmtR = $conn->prepare("SELECT payroll_1_6, payroll_full FROM routes WHERE id = ?");
            $stmtR->bind_param("s", $routeId);
            $stmtR->execute();
            $resR = $stmtR->get_result();
            if ($rowR = $resR->fetch_assoc()) {
                $payroll1_6 = (int)$rowR['payroll_1_6'];
                $payrollFull = (int)$rowR['payroll_full'];
            }
            $stmtR->close();
        }

        // 3. Fallback: If DB didn't have it (or ID missing), try the snapshot values
        if ($payroll1_6 == 0 && $payrollFull == 0) {
            $payroll1_6 = isset($route['payroll_1_6']) ? (int)$route['payroll_1_6'] : 0;
            $payrollFull = isset($route['payroll_full']) ? (int)$route['payroll_full'] : 0;
        }

        // 4. Last Resort: Hardcoded Rules (Padang - Bukittinggi / Payakumbuh)
        if ($payroll1_6 == 0 && $payrollFull == 0) {
             $origin = isset($route['origin']) ? strtolower($route['origin']) : '';
             $destination = isset($route['destination']) ? strtolower($route['destination']) : '';
             
             $isBukittinggi = (strpos($origin, 'padang') !== false && strpos($destination, 'bukittinggi') !== false) || 
                              (strpos($origin, 'bukittinggi') !== false && strpos($destination, 'padang') !== false);
                              
             $isPayakumbuh = (strpos($origin, 'padang') !== false && strpos($destination, 'payakumbuh') !== false) || 
                             (strpos($origin, 'payakumbuh') !== false && strpos($destination, 'padang') !== false);
 
             if ($isBukittinggi) {
                 $payroll1_6 = 75000;
                 $payrollFull = 100000;
             } elseif ($isPayakumbuh) {
                 $payroll1_6 = 100000;
                 $payrollFull = 125000;
             }
        }
        
        // 5. Calculate Final Payroll based on Pax Count
        if ($paxCount >= 7) {
            $payroll = $payrollFull;
        } else {
            $payroll = $payroll1_6;
        }
        
        $row['payrollCalculated'] = $payroll;
        $row['passengerCount'] = $paxCount;
        
        // If status is not 'Selesai' (Paid), we might want to flag it?
        // Let's assume 'Selesai' in table status means paid/done for payroll context too? 
        // Or strictly 'Done' logic.
        
        $trips[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $trips]);
    exit;
}

// --- FINISH TRIP / MARK AS PAID ---
// --- BATCH FINISH PAYROLL ---
if ($action === 'batch_finish_payroll') {
    // FIX: Frontend sends 'trips' (JSON string via FormData), not 'ids'
    $tripsInput = $_POST['trips'] ?? [];
    
    // Decode if it's a string (FormData sends arrays as stringified JSON)
    if (is_string($tripsInput)) {
        $ids = json_decode($tripsInput, true);
    } else {
        $ids = $tripsInput;
    }

    if (empty($ids)) {
        // Fallback check for raw JSON body
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        if (isset($jsonInput['trips'])) {
            $ids = $jsonInput['trips'];
        }
    }

    if (empty($ids)) {
        echo json_encode(['status' => 'error', 'message' => 'No trips selected.']);
        exit;
    }

    $notes = $_POST['notes'] ?? '';
    // Method & Date are shared for the batch
    $method = $_POST['method'] ?? 'cash'; 
    $paymentDate = $_POST['paymentDate'] ?? date('Y-m-d');
    
    $proofImage = null;
    
    // Handle File Upload 
    if ($method === 'transfer' && isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/payroll_proofs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($fileExt, $allowed)) {
            $fileName = 'batch_payroll_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
                $proofImage = 'uploads/payroll_proofs/' . $fileName;
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
                 exit;
            }
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
             exit;
        }
    }

    $conn->begin_transaction();
    try {
        $stmtUpdate = $conn->prepare("UPDATE trips SET status='Selesai', note=CONCAT(COALESCE(note, ''), ?), payroll_method=?, payroll_date=?, payroll_proof_image=? WHERE id=?");
        
        foreach ($ids as $tripId) {
             // We need to fetch specific payroll amount for each trip to log it? 
             // Ideally yes, but for batch performance let's just mark them paid.
             // OR: We iterate and update one by one.
             
             // Let's do iterate to be safe with notes.
             $methodLabel = strtoupper($method);
             $payrollNote = " [BATCH PAYROLL: PAID via $methodLabel on $paymentDate]";
             if (!empty($notes)) {
                 $payrollNote .= " (Batch Note: $notes)";
             }
             
             $stmtUpdate->bind_param("sssss", $payrollNote, $method, $paymentDate, $proofImage, $tripId);
             if (!$stmtUpdate->execute()) {
                 throw new Exception("Failed to update trip $tripId: " . $stmtUpdate->error);
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

// --- FINISH TRIP (SINGLE) ---
if ($action === 'finish_trip_payroll') {
    // Check if it's a POST request with FormData
    $id = $_POST['id'] ?? '';
    $payrollAmount = $_POST['amount'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    $method = $_POST['method'] ?? 'cash'; // cash, transfer
    $paymentDate = $_POST['paymentDate'] ?? date('Y-m-d');
    
    $proofImage = null;
    
    // Handle File Upload if Transfer
    if ($method === 'transfer' && isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/payroll_proofs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($fileExt, $allowed)) {
            $fileName = 'payroll_' . $id . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['proof']['tmp_name'], $targetPath)) {
                // Store relative path for frontend access 
                // Assuming api is in /ops/api_modules, and uploads in /ops/uploads
                // But frontend likely needs full URL or relative to public root.
                // Let's store "uploads/payroll_proofs/filename"
                $proofImage = 'uploads/payroll_proofs/' . $fileName;
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
                 exit;
            }
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Allowed: jpg, png, pdf']);
             exit;
        }
    }
    
    $conn->begin_transaction();
    try {
        // Fetch current note
        $stmtGet = $conn->prepare("SELECT note FROM trips WHERE id=?");
        $stmtGet->bind_param("s", $id);
        $stmtGet->execute();
        $res = $stmtGet->get_result();
        $curr = $res->fetch_assoc();
        $currNote = $curr['note'] ?? '';
        
        // Append payroll info to note (Legacy/Human readable)
        $methodLabel = strtoupper($method);
        $payrollNote = " [PAYROLL: PAID Rp " . number_format($payrollAmount) . " via $methodLabel on $paymentDate]";
        $newNote = $currNote . $payrollNote;
        if (!empty($notes)) {
             $newNote .= " (Note: $notes)";
        }
        
        // Update Query with new columns
        $sql = "UPDATE trips SET status='Selesai', note=?, payroll_method=?, payroll_date=?, payroll_proof_image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
             throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssss", $newNote, $method, $paymentDate, $proofImage, $id);
        
        if ($stmt->execute()) {
             $conn->commit();
             echo json_encode(['status' => 'success']);
        } else {
             throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>
