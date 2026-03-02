<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include Database Connection from display-v11
if (file_exists('../display-v11/base.php')) {
    include '../display-v11/base.php';
} else {
    echo json_encode(['status' => 'error', 'message' => 'Base configuration not found']);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle Input (GET or POST JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (is_array($input) && isset($input['action'])) {
    $action = $input['action'];
} else {
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
}

// --- DASHBOARD STATS (Restored for index.html) ---
if ($action === 'get_dashboard_stats') {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

    try {
        // 1. Fetch Trips (Dispatched/Completed)
        $sql = "SELECT * FROM trips WHERE (status = 'On Trip' OR status = 'Tiba' OR status = 'Selesai') AND date BETWEEN '$startDate' AND '$endDate' ORDER BY date DESC, time DESC";
        $result = $conn->query($sql);

        $trips = [];
        $totalRevenue = 0;
        $totalPax = 0;
        $allBookingIds = [];
        $tempTrips = []; // Temporary storage

        while ($row = $result->fetch_assoc()) {
            $row['routeConfig'] = json_decode($row['routeConfig'], true);
            $row['fleet'] = json_decode($row['fleet'], true);
            $row['driver'] = json_decode($row['driver'], true);
            $row['passengers'] = json_decode($row['passengers'], true);
            
            // Collect all booking IDs first
            foreach ($row['passengers'] as $p) {
                if (isset($p['id'])) {
                    $allBookingIds[] = $p['id'];
                }
            }
            $tempTrips[] = $row;
        }

        // 2. Fetch Real Prices & Refunds from Bookings Table
        $priceMap = [];
        $refunds = [];
        
        if (!empty($allBookingIds)) {
            $idsChunked = array_chunk(array_unique($allBookingIds), 500); // Handle varying limits
            foreach ($idsChunked as $chunk) {
                $idsStr = implode(',', array_map('intval', $chunk)); // Sanitize
                if (!empty($idsStr)) {
                    // Get Prices
                    $sqlPrices = "SELECT id, totalPrice FROM bookings WHERE id IN ($idsStr)";
                    $resPrices = $conn->query($sqlPrices);
                    while ($pr = $resPrices->fetch_assoc()) {
                        $priceMap[$pr['id']] = (float)$pr['totalPrice'];
                    }

                    // Get Refunds
                    $sqlRefunds = "SELECT r.*, b.passengerName, b.routeId 
                                   FROM refunds r 
                                   JOIN bookings b ON r.booking_id = b.id 
                                   WHERE r.booking_id IN ($idsStr)";
                    $resRefund = $conn->query($sqlRefunds);
                    while ($rf = $resRefund->fetch_assoc()) {
                        $refunds[] = $rf;
                    }
                }
            }
        }

        // 3. Re-iterate trips to calculate stats with real prices
        foreach ($tempTrips as $row) {
            $tripRevenue = 0;
            $tripPax = count($row['passengers']);
            
            // Re-map passengers to include real price (optional, but good for frontend if needed)
            foreach ($row['passengers'] as &$p) {
                if (isset($p['id']) && isset($priceMap[$p['id']])) {
                    $realPrice = $priceMap[$p['id']];
                    $p['ticketPrice'] = $realPrice; // Inject real price into JSON for frontend
                    $tripRevenue += $realPrice;
                } elseif (isset($p['ticketPrice'])) {
                    // Fallback to existing if not found in DB (rare)
                    $tripRevenue += (float)$p['ticketPrice'];
                }
            }
            unset($p); // Break reference

            $row['trip_stats'] = [
                'pax_count' => $tripPax,
                'revenue' => $tripRevenue
            ];
            
            $trips[] = $row;
            $totalPax += $tripPax;
            $totalRevenue += $tripRevenue;
        }
        
        // 3. Simple aggregate stats
        $stats = [
            'total_trips' => count($trips),
            'total_pax' => $totalPax,
            'total_revenue' => $totalRevenue,
            'total_refunds_count' => count($refunds),
            'total_refunded_amount' => array_reduce($refunds, function($carry, $item) {
                return $carry + $item['amount'];
            }, 0)
        ];

        echo json_encode(['status' => 'success', 'data' => [
            'trips' => $trips,
            'refunds' => $refunds,
            'stats' => $stats
        ]]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- D. GET REPORTS (Ported from display-v12/api_modules/reports.php) ---
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
            'refundAmount' => 0, 
            'refundDeduction' => 0 
        ];
    }

    // 2. Get Cancelled/Refund Data
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
                $dataMap[$d] = [
                    'revenue' => 0, 'revenueCash' => 0, 'revenueTransfer' => 0, 
                    'pax' => 0, 'unpaid' => 0, 'unpaidAmount' => 0, 'unvalidated' => 0,
                    'refundAmount' => 0, 'refundDeduction' => 0
                ];
            }
            $dataMap[$d]['refundAmount'] = (int)$r['totalRefund'];
            $dataMap[$d]['refundDeduction'] = (int)$r['totalDeduction'];
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

// --- E. GET REPORT DETAILS (Ported from display-v12/api_modules/reports.php) ---
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
    
    echo json_encode(['bookings' => $bookings, 'cancelled' => $cancelled]);
    exit;
}

// --- GET CANCELLED REPORT (Ported from display-v12/api_modules/cancellation.php) ---
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

// --- UPDATE CANCELLATION (Ported from display-v12/api_modules/cancellation.php) ---
if ($action === 'update_cancellation') {
    $id = isset($input['id']) ? $input['id'] : '';
    $refundAmount = isset($input['refundAmount']) ? $input['refundAmount'] : 0;
    $refundAccount = isset($input['refundAccount']) ? $input['refundAccount'] : '';
    $reason = isset($input['reason']) ? $input['reason'] : '';
    $status = isset($input['refundStatus']) ? $input['refundStatus'] : 'Pending';

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

// Default
echo json_encode(['status' => 'active', 'message' => 'Keuangan API Service Ready']);
?>
