<?php

// --- C. GET OCCUPIED SEATS ---
if ($action === 'get_occupied_seats') {
    $routeId = $_GET['routeId'];
    $date = $_GET['date'];
    $time = $_GET['time'];
    $excludeId = isset($_GET['excludeId']) ? $_GET['excludeId'] : null;

    // Modified for Transfer (Numpang) Logic: Check Native bookings OR Incoming Transfers
    $sql = "SELECT seatNumbers, seatCount, batchNumber FROM bookings 
            WHERE ((routeId=? AND physicalRouteId IS NULL) OR physicalRouteId=?) 
            AND date=? AND time=? AND status != 'Cancelled'";
    
    // Default 4 params
    $params = "ssss";
    $args = [$routeId, $routeId, $date, $time];

    if ($excludeId) {
        $sql .= " AND id != ?";
        $params .= "s";
        $args[] = $excludeId;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($params, ...$args);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo json_encode($bookings);
    exit;
}

// --- C2. GET DAILY BOOKED SEATS (For View Booking) ---
if ($action === 'get_daily_booked_seats') {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Fetch all active bookings for the date
    $sql = "SELECT id, passengerName, routeId, time, batchNumber, seatNumbers, status, validationStatus 
            FROM bookings 
            WHERE date = ? AND status != 'Cancelled'
            ORDER BY routeId, time, batchNumber ASC, id ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $bookings]);
    exit;
}

// --- GET BOOKING DETAILS (For Cancellation/View) ---
if ($action === 'get_booking_details') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) { echo json_encode(null); exit; }

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if ($booking) {
        $booking['selectedSeats'] = json_decode($booking['selectedSeats']);
        $booking['totalPrice'] = (float)$booking['totalPrice'];
    }
    echo json_encode(['status' => 'success', 'booking' => $booking]);
    exit;
}

// --- A. SAVE BOOKING BARU (Single Insert) ---
if ($action === 'create_booking') {
    $b = $input['data'];
    
    $bookingNote = isset($b['bookingNote']) ? $b['bookingNote'] : '';

    $sql = "INSERT INTO bookings (
        id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, 
        seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, 
        validationStatus, paymentLocation, paymentReceiver, paymentProof, status, 
        seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName,
        pickupAddress, dropoffAddress, batchNumber, physicalRouteId,
        paymentReceivedDate, transferSentDate, destinationAccount, bookingNote, input_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // DEBUG LOG
    $proofLen = strlen($b['paymentProof']);
    @file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Create Booking: ID={$b['id']} ProofLen={$proofLen} Substr=" . substr($b['paymentProof'], 0, 30) . "\n", FILE_APPEND);

    $stmt = $conn->prepare($sql);
    
    $selectedSeats = json_encode($b['selectedSeats']);
    $seatCapacity = isset($b['seatCapacity']) ? $b['seatCapacity'] : null;
    $downPaymentAmount = isset($b['downPaymentAmount']) ? $b['downPaymentAmount'] : 0;
    
    // Status Logic: Admin -> Pending (Accepted/Valid), User -> Antrian (Waiting)
    // User defined: "Antrian" = Booking Mandiri (Waiting), "Pending" = Diterima (Accepted)
    $isAdmin = isset($b['adminName']) && !empty($b['adminName']);
    $status = $isAdmin ? 'Pending' : 'Antrian';
    
    $pickupAddress = isset($b['pickupAddress']) ? $b['pickupAddress'] : '';
    $dropoffAddress = isset($b['dropoffAddress']) ? $b['dropoffAddress'] : '';

    // New Fields
    $paymentReceivedDate = isset($b['paymentReceivedDate']) ? $b['paymentReceivedDate'] : null;
    $transferSentDate = isset($b['transferSentDate']) ? $b['transferSentDate'] : null;
    $destinationAccount = isset($b['destinationAccount']) ? $b['destinationAccount'] : null;

    // Handle Payment Proof
    $paymentProof = $b['paymentProof'];
    if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
        $paymentProof = saveBase64Image($paymentProof, 'proof_' . $b['id']);
    }

    // Handle KTM
    $ktmProof = isset($b['ktmProof']) ? $b['ktmProof'] : (isset($b['ktmImage']) ? $b['ktmImage'] : '');
    if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
        $ktmProof = saveBase64Image($ktmProof, 'ktm_' . $b['id']);
    }

    $batchNumber = isset($b['batchNumber']) ? intval($b['batchNumber']) : 1;
    $physicalRouteId = isset($b['physicalRouteId']) && !empty($b['physicalRouteId']) ? $b['physicalRouteId'] : null;
    
    $inputDate = date('Y-m-d H:i:s');
    
    $stmt->bind_param("ssssssssisidsssssssssdsisssssissssss", 
        $b['id'], $b['serviceType'], $b['routeId'], $b['date'], $b['time'], 
        $b['passengerName'], $b['passengerPhone'], $b['passengerType'], $b['seatCount'], 
        $selectedSeats, $b['duration'], $b['totalPrice'], $b['paymentMethod'], 
        $b['paymentStatus'], $b['validationStatus'], $b['paymentLocation'], $b['paymentReceiver'], 
        $paymentProof, $status, $b['seatNumbers'], $ktmProof, 
        $downPaymentAmount, $b['type'], $seatCapacity, $b['priceType'], 
        $b['packageType'], $b['routeName'], $pickupAddress, $dropoffAddress, $batchNumber, $physicalRouteId,
        $paymentReceivedDate, $transferSentDate, $destinationAccount, $bookingNote, $inputDate
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Booking berhasil disimpan']);
    } else {
        @file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Create Booking Error: " . $stmt->error . "\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $stmt->error]);
    }
    exit;
}

// --- A2. UPDATE BOOKING STATUS (For Approval) ---
if ($action === 'update_booking_status') {
    $id = $input['id'];
    $status = $input['status'];
    $valStatus = $input['validationStatus'];
    
    // Optional: update payment status if provided
    $payStatus = isset($input['paymentStatus']) ? $input['paymentStatus'] : null;
    
    $sql = "UPDATE bookings SET status=?, validationStatus=?";
    $types = "ss";
    $params = [$status, $valStatus];
    
    if ($payStatus) {
        $sql .= ", paymentStatus=?";
        $types .= "s";
        $params[] = $payStatus;
    }
    
    $sql .= " WHERE id=?";
    $types .= "s";
    $params[] = $id;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// --- B. UPDATE STATUS PEMBAYARAN (Validasi) ---
if ($action === 'update_payment_status') {
    $id = $input['id'];
    $pStatus = $input['paymentStatus'];
    $vStatus = $input['validationStatus'];
    
    $stmt = $conn->prepare("UPDATE bookings SET paymentStatus=?, validationStatus=? WHERE id=?");
    $stmt->bind_param("sss", $pStatus, $vStatus, $id);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// --- C. HAPUS BOOKING ---
if ($action === 'delete_booking') {
    $id = $input['id'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->bind_param("s", $id);
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// --- D. VALIDATE BOOKING (Direct) ---
if ($action === 'validate_booking') {
    $id = $input['id'];
    $stmt = $conn->prepare("UPDATE bookings SET paymentStatus = 'Lunas', validationStatus = 'Valid', status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// --- D2. REJECT BOOKING (Soft Cancel) ---
if ($action === 'reject_booking') {
    $id = $input['id'];
    
    // Mark as Cancelled so it doesn't show up in active seats, but keeps record
    // Set validationStatus to 'Ditolak'
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled', validationStatus = 'Ditolak' WHERE id = ?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

// --- I. UPDATE BOOKING FULL (Edit) ---
if ($action === 'update_booking_full') {
    $id = $input['id'];
    $adminName = $input['adminName'];
    
    $date = $input['date'];
    $time = $input['time'];
    $routeId = isset($input['routeId']) ? $input['routeId'] : null;
    $passengerName = $input['passengerName'];
    $passengerPhone = $input['passengerPhone'];
    $passengerType = $input['passengerType'];
    $seatNumbers = $input['seatNumbers'];
    $seatCount = $input['seatCount'];
    $selectedSeats = json_encode($input['selectedSeats']);
    $totalPrice = $input['totalPrice'];
    $pickupAddress = $input['pickupAddress'];
    $dropoffAddress = $input['dropoffAddress'];
    
    // Handle KTM Proof
    $ktmProof = isset($input['ktmProof']) ? $input['ktmProof'] : '';
    if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
        $ktmProof = saveBase64Image($ktmProof, 'ktm_edit_' . $id . '_' . time());
    }

    // Handle Payment Proof
    $paymentProof = isset($input['paymentProof']) ? $input['paymentProof'] : '';
    if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
        $paymentProof = saveBase64Image($paymentProof, 'proof_' . $id . '_' . time());
    }
    
    $conn->begin_transaction();
    try {
        $prev = $conn->query("SELECT * FROM bookings WHERE id='$id'")->fetch_assoc();
        
        // If no new image uploaded, keep the old one (if not empty locally but maybe we want to keep it if key exists)
        // Actually, if frontend sends the URL of existing image, saveBase64Image won't run, so $ktmProof will be the URL.
        // But if frontend sends empty, it might mean delete? 
        // In this case, if $ktmProof is the URL, we just save it back (or don't change it).
        // Let's assume input['ktmProof'] has the current value (URL or Base64).
        
        $prevJson = json_encode($prev);
        
        $sql = "UPDATE bookings SET 
                date=?, time=?, routeId=?, 
                passengerName=?, passengerPhone=?, passengerType=?,
                seatNumbers=?, seatCount=?, selectedSeats=?,
                totalPrice=?, pickupAddress=?, dropoffAddress=?,
                ktmProof=?, paymentProof=?
                WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssisssssss", 
            $date, $time, $routeId, 
            $passengerName, $passengerPhone, $passengerType,
            $seatNumbers, $seatCount, $selectedSeats,
            $totalPrice, $pickupAddress, $dropoffAddress,
            $ktmProof, $paymentProof,
            $id
        );
        $stmt->execute();
        
        // Log
        $logId = time() . rand(100,999);
        $actionLog = 'Edit Full Data';
        $newVal = $conn->query("SELECT * FROM bookings WHERE id='$id'")->fetch_assoc();
        $newJson = json_encode($newVal);
        
        $stmtLog = $conn->prepare("INSERT INTO booking_logs (id, booking_id, action, admin_name, prev_value, new_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtLog->bind_param("ssssss", $logId, $id, $actionLog, $adminName, $prevJson, $newJson);
        $stmtLog->execute();
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- J. GET BOOKING LOGS ---
if ($action === 'get_booking_logs') {
    $id = $input['id'];
    $stmt = $conn->prepare("SELECT * FROM booking_logs WHERE booking_id=? ORDER BY timestamp DESC");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    echo json_encode(['status' => 'success', 'logs' => $logs]);
    exit;
}

// --- K. MOVE BOOKING SCHEDULE ---
if ($action === 'move_booking_schedule') {
    $id = $input['id'];
    $date = $input['date'];
    $time = $input['time'];
    $clearSeat = isset($input['clear_seat']) && $input['clear_seat'];
    
    $newSeatNumbers = isset($input['seatNumbers']) ? $input['seatNumbers'] : null;
    $batchNumber = isset($input['batchNumber']) ? intval($input['batchNumber']) : null;
    
    $conn->begin_transaction();
    try {
        $fields = ["date=?", "time=?"];
        $types = "ss";
        $params = [$date, $time];

        if ($newSeatNumbers !== null) {
                $seatsArr = array_map('trim', explode(',', $newSeatNumbers));
                $seatCount = count($seatsArr);
                $selectedSeatsStr = json_encode($seatsArr);

                $fields[] = "seatNumbers=?";
                $fields[] = "selectedSeats=?";
                $fields[] = "seatCount=?";
                $types .= "ssi";
                $params[] = $newSeatNumbers;
                $params[] = $selectedSeatsStr;
                $params[] = $seatCount;

                // FIX: Recalculate Price if seats changed
                $curr = $conn->query("SELECT routeId, serviceType, passengerType, priceType FROM bookings WHERE id='$id'")->fetch_assoc();
                if ($curr && $curr['serviceType'] === 'Travel' && $curr['priceType'] !== 'Manual') { 
                    $rId = $curr['routeId'];
                    $route = $conn->query("SELECT * FROM routes WHERE id='$rId'")->fetch_assoc();
                    if ($route) {
                        $basePrice = ($curr['passengerType'] === 'Pelajar') ? $route['price_pelajar'] : $route['price_umum'];
                        $newTotalPrice = $basePrice * $seatCount;
                        
                        $fields[] = "totalPrice=?";
                        $types .= "d";
                        $params[] = $newTotalPrice;
                    }
                }
        } elseif ($clearSeat) {
                $fields[] = "seatNumbers=NULL";
                $fields[] = "selectedSeats='[]'";
        }

        if ($batchNumber !== null) {
            $fields[] = "batchNumber=?";
            $types .= "i";
            $params[] = $batchNumber;
        }

        $sql = "UPDATE bookings SET " . implode(", ", $fields) . " WHERE id=?";
        $types .= "s";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- L. GET ALL BOOKING LOGS (Global) ---
if ($action === 'get_all_booking_logs') {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    
    $sql = "SELECT * FROM booking_logs ORDER BY timestamp DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    echo json_encode(['status' => 'success', 'logs' => $logs]);
    exit;
}

?>
