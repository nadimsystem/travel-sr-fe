<?php

// --- D. GET REPORTS ---
if ($action === 'get_reports') {
    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
    $monthFilter = (!empty($_GET['month'])) ? $_GET['month'] : date('Y-m');
    $routeKeyword = isset($_GET['routeKeyword']) ? $conn->real_escape_string($_GET['routeKeyword']) : '';
    $routeCondition = $routeKeyword ? " AND (routeName LIKE '$routeKeyword%') " : "";

    $sql = "SELECT date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
            SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice ELSE 0 END) as revenueCash,
            SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice ELSE 0 END) as revenueTransfer,
            SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN 1 ELSE 0 END) as unpaidCount,
            SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN (totalPrice - COALESCE(downPaymentAmount,0)) ELSE 0 END) as unpaidAmount,
            SUM(CASE WHEN validationStatus = 'Menunggu Validasi' OR (validationStatus IS NULL AND paymentProof != '') THEN 1 ELSE 0 END) as unvalidatedCount
            FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Batal') AND DATE_FORMAT(date, '%Y-%m') = '$monthFilter' $routeCondition
            GROUP BY date ORDER BY date DESC";
    
    if ($period === 'monthly') {
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice ELSE 0 END) as revenueCash,
                SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice ELSE 0 END) as revenueTransfer,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN 1 ELSE 0 END) as unpaidCount,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN (totalPrice - COALESCE(downPaymentAmount,0)) ELSE 0 END) as unpaidAmount,
                SUM(CASE WHEN validationStatus = 'Menunggu Validasi' OR (validationStatus IS NULL AND paymentProof != '') THEN 1 ELSE 0 END) as unvalidatedCount
                FROM bookings WHERE status NOT IN ('Cancelled', 'Batal') $routeCondition GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY date DESC LIMIT 12";
    }

    // 1. Get Active Bookings Data
    $result = $conn->query($sql);
    $dataMap = [];

    // Initialize Map with Active Data
    while ($row = $result->fetch_assoc()) {
        $dateKey = $row['date'];
        $dataMap[$dateKey] = [
            'revenue' => (int)$row['revenue'],
            'revenueCash' => (int)$row['revenueCash'],
            'revenueTransfer' => (int)$row['revenueTransfer'],
            'pax' => (int)$row['pax'],
            'unpaid' => (int)$row['unpaidCount'],
            'unpaidAmount' => (int)$row['unpaidAmount'],
            'unvalidated' => (int)$row['unvalidatedCount'],
            'refundAmount' => 0, // NEW
            'refundDeduction' => 0 // NEW
        ];
    }

    // 2. Get Cancelled/Refund Data
    // Group by Date (Travel Date) to align with reports
    $refundSql = "SELECT DATE_FORMAT(date, '%Y-%m-%d') as date, 
                  SUM(refund_amount) as totalRefund,
                  SUM(CASE 
                    WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
                    WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
                    ELSE 0 
                  END) as totalDeduction
                  FROM cancelled_bookings 
                  WHERE DATE_FORMAT(date, '%Y-%m') = '$monthFilter' $routeCondition
                  GROUP BY DATE_FORMAT(date, '%Y-%m-%d')";

    if ($period === 'monthly') {
        $refundSql = "SELECT DATE_FORMAT(date, '%Y-%m') as date, 
                  SUM(refund_amount) as totalRefund,
                  SUM(CASE 
                    WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
                    WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
                    ELSE 0 
                  END) as totalDeduction
                  FROM cancelled_bookings 
                  WHERE 1=1 $routeCondition
                  GROUP BY DATE_FORMAT(date, '%Y-%m')";
    }

    $refundRes = $conn->query($refundSql);
    if($refundRes) {
        while($r = $refundRes->fetch_assoc()) {
            $d = $r['date'];
            if(!isset($dataMap[$d])) {
                // If date exists in cancellations but not in active bookings
                $dataMap[$d] = [
                    'revenue' => 0, 'revenueCash' => 0, 'revenueTransfer' => 0, 
                    'pax' => 0, 'unpaid' => 0, 'unpaidAmount' => 0, 'unvalidated' => 0,
                    'refundAmount' => 0, 'refundDeduction' => 0
                ];
            }
            $dataMap[$d]['refundAmount'] = (int)$r['totalRefund'];
            $dataMap[$d]['refundDeduction'] = (int)$r['totalDeduction'];
            
            // Add Deduction to Revenue? 
            // Usually Deduction (Revenue from Cancellation) is Revenue.
            // Active Revenue is distinct. We should probably keep them separate or add them?
            // User requested: "berapa total refund berapa total uang potongan refund"
            // So we send them as separate fields.
            // Note: If you want Total Revenue to include Deduction, do: $dataMap[$d]['revenue'] += (int)$r['totalDeduction'];
            // For now, let's keep revenue as "Ticket Sales" and add "Cancellation Revenue" separately.
        }
    }

    // Sort by Date Desc
    krsort($dataMap);
    if($period === 'monthly') $dataMap = array_slice($dataMap, 0, 12);

    $labels = [];
    $revenue = [];
    $revenueCash = [];
    $revenueTransfer = [];
    $pax = [];
    $unpaid = [];
    $unpaidAmount = [];
    $unvalidated = [];
    $refundTotal = [];
    $refundRevenue = [];
    $details = [];

    foreach ($dataMap as $date => $val) {
        $labels[] = $date;
        $revenue[] = $val['revenue'];
        $revenueCash[] = $val['revenueCash'];
        $revenueTransfer[] = $val['revenueTransfer'];
        $pax[] = $val['pax'];
        $unpaid[] = $val['unpaid'];
        $unpaidAmount[] = $val['unpaidAmount'];
        $unvalidated[] = $val['unvalidated'];
        $refundTotal[] = $val['refundAmount'];
        $refundRevenue[] = $val['refundDeduction'];
        $details[$date] = [];
    }

    $detailSql = "SELECT date, time, routeName, COUNT(id) as count, SUM(seatCount) as seats, SUM(totalPrice) as tripRevenue 
                  FROM bookings 
                  WHERE status NOT IN ('Cancelled', 'Batal') $routeCondition AND (DATE_FORMAT(date, '%Y-%m') = '$monthFilter' OR date IN ('" . implode("','", $labels) . "'))
                  GROUP BY date, time, routeName";
    
    if ($period === 'daily') {
            $detailRes = $conn->query($detailSql);
            if ($detailRes) {
                while ($d = $detailRes->fetch_assoc()) {
                    $dDate = $d['date'];
                    if (isset($details[$dDate])) {
                        $details[$dDate][] = [
                            'time' => $d['time'],
                            'routeName' => $d['routeName'],
                            'count' => $d['count'],
                            'seats' => $d['seats'],
                            'tripRevenue' => $d['tripRevenue']
                        ];
                    }
                }
            }
    }

    echo json_encode([
        'reports' => [
            'labels' => array_reverse($labels),
            'revenue' => array_reverse($revenue),
            'revenueCash' => array_reverse($revenueCash),
            'revenueTransfer' => array_reverse($revenueTransfer),
            'pax' => array_reverse($pax),
            'unpaid' => array_reverse($unpaid),
            'unpaidAmount' => array_reverse($unpaidAmount),
            'unvalidated' => array_reverse($unvalidated),
            'refundTotal' => array_reverse($refundTotal),
            'refundRevenue' => array_reverse($refundRevenue),
            'details' => $details
        ]
    ]);
    exit;
}

// --- E. GET REPORT DETAILS ---
if ($action === 'get_report_details') {
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    if (!$date) { echo json_encode(['error' => 'Date required']); exit; }

    if (strlen($date) === 7) {
            $where = "DATE_FORMAT(date, '%Y-%m') = '$date'";
    } else {
            $where = "date = '$date'";
    }

    $routeKeyword = isset($_GET['routeKeyword']) ? $conn->real_escape_string($_GET['routeKeyword']) : '';
    $routeCondition = $routeKeyword ? " AND (routeName LIKE '$routeKeyword%') " : "";

    $sql = "SELECT id, time, routeName, passengerName, seatCount, seatNumbers, selectedSeats, totalPrice, paymentMethod, paymentStatus, downPaymentAmount, status 
            FROM bookings 
            WHERE $where AND status NOT IN ('Cancelled', 'Batal') $routeCondition
            ORDER BY time ASC, id ASC";
    
    $result = $conn->query($sql);
    $bookings = [];
    while($row = $result->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $row['seatCount'] = (int)$row['seatCount'];
        $row['totalPrice'] = (double)$row['totalPrice'];
        
        if (empty($row['seatNumbers']) && !empty($row['selectedSeats'])) {
            $seats = json_decode($row['selectedSeats'], true);
            if (is_array($seats)) {
                $row['seatNumbers'] = implode(', ', $seats);
            }
        }
        unset($row['selectedSeats']); 
        $bookings[] = $row;
    }

    // echo json_encode(['bookings' => $bookings]); // REMOVED PREMATURE OUTPUT
    
    // FETCH CANCELLED BOOKINGS FOR THIS DATE
    $startDate = $date;
    $endDate = $date; 
    
    if (strlen($date) === 7) {
         // Monthly
         $startDate = $date . '-01';
         $endDate = $date . '-31';
    }

    $cancelSql = "SELECT * FROM cancelled_bookings 
                  WHERE date BETWEEN '$startDate' AND '$endDate' $routeCondition 
                  ORDER BY time ASC";
    
    $cancelRes = $conn->query($cancelSql);
    $cancelled = [];
    if($cancelRes) {
        while($crow = $cancelRes->fetch_assoc()) {
            $crow['id'] = (float)$crow['id'];
            $crow['seatCount'] = (int)$crow['seatCount'];
            $crow['totalPrice'] = (double)$crow['totalPrice'];
            $crow['refund_amount'] = (double)$crow['refund_amount'];
            $cancelled[] = $crow;
        }
    }
    
    // Re-encode with both lists
    if(ob_get_length()) ob_clean(); // Ensure clean output
    echo json_encode(['bookings' => $bookings, 'cancelled' => $cancelled]);
    exit;
}

// --- G. GET CRM DATA ---
if ($action === 'get_crm_data') {
    $sql = "SELECT phone, MAX(name) as name, SUM(totalTrips) as totalTrips, SUM(totalRevenue) as totalRevenue, MAX(lastTrip) as lastTrip 
    FROM (
        SELECT passengerPhone as phone, MAX(passengerName) as name, COUNT(id) as totalTrips, SUM(totalPrice) as totalRevenue, MAX(date) as lastTrip 
        FROM bookings 
        WHERE status NOT IN ('Cancelled', 'Batal') AND passengerPhone != '' 
        GROUP BY passengerPhone
        
        UNION ALL
        
        SELECT passengerPhone as phone, MAX(passengerName) as name, COUNT(id) as totalTrips, SUM(totalPrice) as totalRevenue, MAX(date) as lastTrip 
        FROM cancelled_bookings 
        WHERE passengerPhone != '' 
        GROUP BY passengerPhone
    ) AS combined
    GROUP BY phone
    ORDER BY lastTrip DESC";
    
    $result = $conn->query($sql);
    $customers = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    echo json_encode(['customers' => $customers]);
    exit;
}

// --- H. GET CUSTOMER HISTORY ---
if ($action === 'get_customer_history') {
    $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
    $stmt = $conn->prepare("
        SELECT * FROM (
            SELECT id, date, routeName, status, totalPrice FROM bookings WHERE passengerPhone = ?
            UNION ALL
            SELECT id, date, routeName, status, totalPrice FROM cancelled_bookings WHERE passengerPhone = ?
        ) AS combined ORDER BY date DESC LIMIT 10
    ");
    $stmt->bind_param("ss", $phone, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    echo json_encode(['status' => 'success', 'history' => $history]);
    exit;
}
?>
