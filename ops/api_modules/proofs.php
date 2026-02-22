<?php

// --- GET PAYMENT PROOFS ---
if ($action === 'get_payment_proofs') {
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $month = isset($_GET['month']) ? $_GET['month'] : null; // YYYY-MM
    
    try {
        if ($date) {
            // Get proofs for a specific date
            $sql = "SELECT id, date, time, passengerName, passengerPhone, totalPrice, paymentProof, validationStatus, paymentStatus, routeId, routeName, serviceType, seatCount, destinationAccount, transferSentDate, bookingNote 
                    FROM bookings 
                    WHERE paymentProof IS NOT NULL AND paymentProof != '' 
                    AND date = ?
                    ORDER BY time DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $proofs = [];
            while ($row = $result->fetch_assoc()) {
                // Ensure totalPrice is float
                $row['totalPrice'] = (float)$row['totalPrice'];
                $proofs[] = $row;
            }
            echo json_encode(['status' => 'success', 'proofs' => $proofs]);
        } else {
            // Get available dates and counts for navigation
            // If month is provided, filter by month
            $whereClause = "WHERE paymentProof IS NOT NULL AND paymentProof != ''";
            $params = [];
            $types = "";

            if ($month) {
                $whereClause .= " AND DATE_FORMAT(date, '%Y-%m') = ?";
                $params[] = $month;
                $types .= "s";
            }

            $sql = "SELECT date, COUNT(*) as count 
                    FROM bookings 
                    $whereClause
                    GROUP BY date 
                    ORDER BY date DESC";
            
            // Only limit if no month is selected (default view)
            if (!$month) {
                $sql .= " LIMIT 60";
            }

            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $dates = [];
            while ($row = $result->fetch_assoc()) {
                $dates[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'dates' => $dates]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- GET AVAILABLE MONTHS FOR PROOFS ---
if ($action === 'get_proof_months') {
    try {
        $sql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month_value, DATE_FORMAT(date, '%M %Y') as month_label
                FROM bookings 
                WHERE paymentProof IS NOT NULL AND paymentProof != '' 
                ORDER BY month_value DESC";
        $result = $conn->query($sql);
        $months = [];
        while ($row = $result->fetch_assoc()) {
            // Translate month label to ID if needed, or handle in frontend
            $months[] = $row;
        }
        echo json_encode(['status' => 'success', 'months' => $months]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- GET KTM PROOFS ---
if ($action === 'get_ktm_proofs') {
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    
    try {
        if ($date) {
            // Get proofs for a specific date
            $sql = "SELECT id, date, time, passengerName, passengerPhone, totalPrice, ktmProof, validationStatus, paymentStatus, routeId, routeName, serviceType, seatCount, destinationAccount, transferSentDate, bookingNote 
                    FROM bookings 
                    WHERE ktmProof IS NOT NULL AND ktmProof != '' 
                    AND date = ?
                    ORDER BY time DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $proofs = [];
            while ($row = $result->fetch_assoc()) {
                // Ensure totalPrice is float
                $row['totalPrice'] = (float)$row['totalPrice'];
                $proofs[] = $row;
            }
            echo json_encode(['status' => 'success', 'proofs' => $proofs]);
        } else {
            // Get available dates and counts for navigation
            $sql = "SELECT date, COUNT(*) as count 
                    FROM bookings 
                    WHERE ktmProof IS NOT NULL AND ktmProof != '' 
                    GROUP BY date 
                    ORDER BY date DESC 
                    LIMIT 60";
            $result = $conn->query($sql);
            $dates = [];
            while ($row = $result->fetch_assoc()) {
                $dates[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'dates' => $dates]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

?>
