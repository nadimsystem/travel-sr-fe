<?php

// --- X. CANCELLATION WORKFLOW ---

if ($action === 'process_cancellation') {
    $data = $input['data'];
    $id = $data['id'];
    $reason = $data['reason'];
    $refundAccount = $data['refundAccount'];
    $refundAmount = $data['refundAmount'];
    $cancelledBy = isset($data['cancelledBy']) ? $data['cancelledBy'] : 'Admin'; 

    $conn->begin_transaction();
    try {
        // 1. Get Existing Booking Data
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        if (!$booking) {
            throw new Exception("Booking ID $id tidak ditemukan (mungkin sudah dihapus).");
        }
        
        $cols = "id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, validationStatus, paymentLocation, paymentReceiver, paymentProof, status, seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName, pickupAddress, dropoffAddress, cancelled_at, cancelled_by, refund_amount, refund_account, cancellation_reason";
        
        $sqlInsert = "INSERT INTO cancelled_bookings ($cols) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
        
        $stmtIns = $conn->prepare($sqlInsert);
        
        $status = 'Cancelled';
        
        // Format: sssssssisi dssssss sss disissss sds s
        // Combined: sssssssisidsssssssssdsissssssdss
        
        $stmtIns->bind_param("ssssssssisidsssssssssdsissssssdss", 
            $booking['id'], 
            $booking['serviceType'], 
            $booking['routeId'], 
            $booking['date'], 
            $booking['time'], 
            $booking['passengerName'], 
            $booking['passengerPhone'], 
            $booking['passengerType'], 
            $booking['seatCount'], 
            $booking['selectedSeats'], 
            $booking['duration'], 
            $booking['totalPrice'], 
            $booking['paymentMethod'], 
            $booking['paymentStatus'], 
            $booking['validationStatus'], 
            $booking['paymentLocation'], 
            $booking['paymentReceiver'], 
            $booking['paymentProof'], 
            $status, // Force Cancelled
            $booking['seatNumbers'], 
            $booking['ktmProof'], 
            $booking['downPaymentAmount'], 
            $booking['type'], 
            $booking['seatCapacity'], 
            $booking['priceType'], 
            $booking['packageType'], 
            $booking['routeName'], 
            $booking['pickupAddress'], 
            $booking['dropoffAddress'],
            // Extra Fields
            $cancelledBy,
            $refundAmount,
            $refundAccount,
            $reason
        );
        $stmtIns->execute();

        // 3. Delete from original table
        $stmtDel = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmtDel->bind_param("s", $id);
        $stmtDel->execute();
        
        // 4. Also remove from TRIPS if assigned
        $sqlTrips = "SELECT id, passengers FROM trips WHERE passengers LIKE '%\"id\":\"$id\"%' OR passengers LIKE '%\"id\":$id%'";
        $resTrips = $conn->query($sqlTrips);
        if ($resTrips) {
            while ($trip = $resTrips->fetch_assoc()) {
                $pList = json_decode($trip['passengers'], true);
                $newPList = [];
                foreach ($pList as $p) {
                        if ((string)$p['id'] !== (string)$id) {
                            $newPList[] = $p;
                        }
                }
                // Update Trip
                $updSql = "UPDATE trips SET passengers = '" . json_encode($newPList) . "' WHERE id = '" . $trip['id'] . "'";
                $conn->query($updSql);
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dipindahkan ke Pembatalan.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_cancelled_report') {
    $sql = "SELECT * FROM cancelled_bookings ORDER BY cancelled_at DESC, date DESC";
    $result = $conn->query($sql);
    $data = [];
    if($result) {
        while ($row = $result->fetch_assoc()) {
            $row['totalPrice'] = (float)$row['totalPrice'];
            $row['refund_amount'] = (float)$row['refund_amount'];
            $data[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

if ($action === 'update_cancellation') {
    $id = $input['id'];
    $refundAmount = $input['refundAmount'];
    $refundAccount = $input['refundAccount'];
    $reason = $input['reason'];
    $status = $input['refundStatus']; // Pending / Refunded

    $sql = "UPDATE cancelled_bookings SET 
            refund_amount = ?, 
            refund_account = ?, 
            cancellation_reason = ?,
            refund_status = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssss", $refundAmount, $refundAccount, $reason, $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

?>
