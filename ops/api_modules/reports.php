<?php

// --- D. GET REPORTS ---
if ($action === 'get_reports') {
    $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
    $monthFilter = (!empty($_GET['month'])) ? $_GET['month'] : date('Y-m');
    $routeKeyword = isset($_GET['routeKeyword']) ? $conn->real_escape_string($_GET['routeKeyword']) : '';
    
    $routeCondition = "";
    if ($routeKeyword === 'Carter') {
        $routeCondition = " AND serviceType = 'Carter' ";
    } elseif ($routeKeyword === 'Dropping') {
        $routeCondition = " AND serviceType = 'Dropping' ";
    } elseif ($routeKeyword === 'Padang - Bukittinggi') {
        // Strict Direction: Starts with Padang, contains Bukittinggi (and Padang index < Bukittinggi index roughly, but SQL LIKE is easier if naming is consistent)
        // Assuming consistent naming "Padang - Bukittinggi..." vs "Bukittinggi - Padang..."
        // Using LIKE 'Padang - Bukittinggi%' covers most.
        $routeCondition = " AND routeName LIKE 'Padang - Bukittinggi%' ";
    } elseif ($routeKeyword === 'Bukittinggi - Padang') {
        $routeCondition = " AND routeName LIKE 'Bukittinggi - Padang%' ";
    } elseif ($routeKeyword === 'Padang - Payakumbuh') {
        $routeCondition = " AND routeName LIKE 'Padang - Payakumbuh%' ";
    } elseif ($routeKeyword === 'Payakumbuh - Padang') {
        $routeCondition = " AND routeName LIKE 'Payakumbuh - Padang%' ";
    } elseif ($routeKeyword) {
        $routeCondition = " AND routeName LIKE '$routeKeyword%' ";
    }

    $sql = "SELECT date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
            SUM(CASE WHEN paymentMethod LIKE '%Cash%' OR paymentMethod LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueCash,
            SUM(CASE WHEN paymentMethod NOT LIKE '%Cash%' AND paymentMethod NOT LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueTransfer,
            SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN 1 ELSE 0 END) as unpaidCount,
            SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN (totalPrice - COALESCE(downPaymentAmount,0)) ELSE 0 END) as unpaidAmount,
            SUM(CASE WHEN validationStatus = 'Menunggu Validasi' OR (validationStatus IS NULL AND paymentProof != '') THEN 1 ELSE 0 END) as unvalidatedCount
            FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND DATE_FORMAT(date, '%Y-%m') = '$monthFilter' $routeCondition
            GROUP BY date ORDER BY date DESC";
    
    if ($period === 'monthly') {
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                SUM(CASE WHEN paymentMethod LIKE '%Cash%' OR paymentMethod LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueCash,
                SUM(CASE WHEN paymentMethod NOT LIKE '%Cash%' AND paymentMethod NOT LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueTransfer,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN 1 ELSE 0 END) as unpaidCount,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN (totalPrice - COALESCE(downPaymentAmount,0)) ELSE 0 END) as unpaidAmount,
                SUM(CASE WHEN validationStatus = 'Menunggu Validasi' OR (validationStatus IS NULL AND paymentProof != '') THEN 1 ELSE 0 END) as unvalidatedCount
                FROM bookings WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') $routeCondition GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY date DESC LIMIT 12";
    }
    
    if ($period === 'weekly') {
        // Group by Week 1-5 of the month
        // Formula: FLOOR((DAY(date) - 1) / 7) + 1
        $sql = "SELECT CONCAT('Minggu ', FLOOR((DAY(date) - 1) / 7) + 1) as date, 
                SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                SUM(CASE WHEN paymentMethod LIKE '%Cash%' OR paymentMethod LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueCash,
                SUM(CASE WHEN paymentMethod NOT LIKE '%Cash%' AND paymentMethod NOT LIKE '%Tunai%' THEN totalPrice ELSE 0 END) as revenueTransfer,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN 1 ELSE 0 END) as unpaidCount,
                SUM(CASE WHEN paymentStatus != 'Lunas' AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100 THEN (totalPrice - COALESCE(downPaymentAmount,0)) ELSE 0 END) as unpaidAmount,
                SUM(CASE WHEN validationStatus = 'Menunggu Validasi' OR (validationStatus IS NULL AND paymentProof != '') THEN 1 ELSE 0 END) as unvalidatedCount
                FROM bookings 
                WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND DATE_FORMAT(date, '%Y-%m') = '$monthFilter' $routeCondition
                GROUP BY FLOOR((DAY(date) - 1) / 7)
                ORDER BY date ASC";
    }

    // 1. Get Active Bookings Data
    $result = $conn->query($sql);
    $dataMap = [];

    // Initialize Map with Active Data
    while ($row = $result->fetch_assoc()) {
        $dateKey = $row['date'];
        $dataMap[$dateKey] = [
            'travelRevenue' => (int)$row['revenue'], // Renamed for clarity, but mapped to revenue later
            'revenueCash' => (int)$row['revenueCash'],
            'revenueTransfer' => (int)$row['revenueTransfer'],
            'pax' => (int)$row['pax'],
            'unpaid' => (int)$row['unpaidCount'],
            'unpaidAmount' => (int)$row['unpaidAmount'],
            'unvalidated' => (int)$row['unvalidatedCount'],
            'refundAmount' => 0, 
            'refundRevenue' => 0, // Deduction/Cancellation Fee = Income
            'packageRevenue' => 0 // NEW
        ];
    }

    // 2. Get Cancelled/Refund Data (Deduction = Income)
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

    if ($period === 'weekly') {
        $refundSql = "SELECT CONCAT('Minggu ', FLOOR((DAY(date) - 1) / 7) + 1) as date, 
                  SUM(refund_amount) as totalRefund,
                  SUM(CASE 
                    WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
                    WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
                    ELSE 0 
                  END) as totalDeduction
                  FROM cancelled_bookings 
                  WHERE DATE_FORMAT(date, '%Y-%m') = '$monthFilter' $routeCondition
                  GROUP BY FLOOR((DAY(date) - 1) / 7)";
    }

    $refundRes = $conn->query($refundSql);
    if($refundRes) {
        while($r = $refundRes->fetch_assoc()) {
            $d = $r['date'];
            if(!isset($dataMap[$d])) {
                // If date exists in cancellations but not in active bookings
                $dataMap[$d] = [
                    'travelRevenue' => 0, 'revenueCash' => 0, 'revenueTransfer' => 0, 
                    'pax' => 0, 'unpaid' => 0, 'unpaidAmount' => 0, 'unvalidated' => 0,
                    'refundAmount' => 0, 'refundRevenue' => 0, 'packageRevenue' => 0
                ];
            }
            $dataMap[$d]['refundAmount'] = (int)$r['totalRefund'];
            $dataMap[$d]['refundRevenue'] = (int)$r['totalDeduction'];
            
            // Add Deduction to Revenue? 
            // Usually Deduction (Revenue from Cancellation) is Revenue.
            // Active Revenue is distinct. We should probably keep them separate or add them?
            // User requested: "berapa total refund berapa total uang potongan refund"
            // So we send them as separate fields.
            // Note: If you want Total Revenue to include Deduction, do: $dataMap[$d]['revenue'] += (int)$r['totalDeduction'];
            // For now, let's keep revenue as "Ticket Sales" and add "Cancellation Revenue" separately.
        }
    }

    // 3. Get Package Data (NEW)
    // Only if routeCondition is empty (Package routes are separate, usually not filtered by travel route)
    // Or if we map package routes to the filter? For now, if user selects "Padang - Bukittinggi", we include relevant packages.
    // For simplicity, if route is specific travel route (Carter/Dropping), maybe exclude packages? 
    // But route names overlap ("Padang - Bukittinggi"). So we include them if names match.
    
    // Package SQL
    $pkgRouteCondition = "";
    if ($routeKeyword) {
          $pkgRouteCondition = " AND route LIKE '%$routeKeyword%' ";
    }

    $pkgSql = "SELECT DATE_FORMAT(bookingDate, '%Y-%m-%d') as date, SUM(price) as revenue FROM packages 
               WHERE status != 'Cancelled' AND DATE_FORMAT(bookingDate, '%Y-%m') = '$monthFilter' $pkgRouteCondition 
               GROUP BY DATE_FORMAT(bookingDate, '%Y-%m-%d')";

    if ($period === 'monthly') {
        $pkgSql = "SELECT DATE_FORMAT(bookingDate, '%Y-%m') as date, SUM(price) as revenue FROM packages 
                   WHERE status != 'Cancelled' $pkgRouteCondition
                   GROUP BY DATE_FORMAT(bookingDate, '%Y-%m')";
    }
    
    if ($period === 'weekly') {
        $pkgSql = "SELECT CONCAT('Minggu ', FLOOR((DAY(bookingDate) - 1) / 7) + 1) as date, SUM(price) as revenue FROM packages 
                   WHERE status != 'Cancelled' AND DATE_FORMAT(bookingDate, '%Y-%m') = '$monthFilter' $pkgRouteCondition
                   GROUP BY FLOOR((DAY(bookingDate) - 1) / 7)";
    }

    $pkgRes = $conn->query($pkgSql);
    if ($pkgRes) {
        while ($p = $pkgRes->fetch_assoc()) {
             $d = $p['date'];
             if(!isset($dataMap[$d])) {
                $dataMap[$d] = [
                    'travelRevenue' => 0, 'revenueCash' => 0, 'revenueTransfer' => 0, 
                    'pax' => 0, 'unpaid' => 0, 'unpaidAmount' => 0, 'unvalidated' => 0,
                    'refundAmount' => 0, 'refundRevenue' => 0, 'packageRevenue' => 0
                ];
            }
            $dataMap[$d]['packageRevenue'] = (int)$p['revenue'];
            // Assume package is Cash for now or add logic?
            // For simplicity, we just add to total revenue.
        }
    }


    // Sort by Date Desc by default
    krsort($dataMap);

    if($period === 'monthly') {
        $dataMap = array_slice($dataMap, 0, 12);
    }

    $labels = [];
    $totalRevenue = []; // Grand Total
    $travelRevenue = [];
    $packageRevenue = [];
    $refundRevenue = [];
    $revenueCash = [];
    $revenueTransfer = [];
    $unpaidAmount = [];

    foreach ($dataMap as $date => $val) {
        $labels[] = $date;
        $tRev = $val['travelRevenue'];
        $pRev = $val['packageRevenue'];
        $rRev = $val['refundRevenue'];
        
        $grandTotal = $tRev + $pRev + $rRev;

        $totalRevenue[] = $grandTotal;
        $travelRevenue[] = $tRev;
        $packageRevenue[] = $pRev;
        $refundRevenue[] = $rRev;
        
        $revenueCash[] = $val['revenueCash'];
        $revenueTransfer[] = $val['revenueTransfer'];
        $unpaidAmount[] = $val['unpaidAmount'];
        
        $pax[] = $val['pax'];
        $details[$date] = [];
    }
    
    // ... (Detail SQL Logic remains similar, focusing on Trips for now) ...
    // Note: Detail SQL only fetches bookings. If we want package details, we need another query. 
    // Keeping detail modal regarding Trips/Bookings for now as per usual Ops usage.

    $detailSql = "SELECT date, time, REPLACE(routeName, ' (Normal)', '') as routeName, batchNumber, COUNT(id) as count, SUM(seatCount) as seats, SUM(totalPrice) as tripRevenue 
                  FROM bookings 
                  WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') $routeCondition AND (DATE_FORMAT(date, '%Y-%m') = '$monthFilter' OR date IN ('" . implode("','", $labels) . "'))
                  GROUP BY date, time, REPLACE(routeName, ' (Normal)', ''), batchNumber";
    
    if ($period === 'daily') {
            $detailRes = $conn->query($detailSql);
            if ($detailRes) {
                while ($d = $detailRes->fetch_assoc()) {
                    $dDate = $d['date'];
                    if (isset($details[$dDate])) {
                        $details[$dDate][] = [
                            'time' => $d['time'],
                            'routeName' => $d['routeName'],
                            'batchNumber' => $d['batchNumber'],
                            'count' => $d['count'],
                            'seats' => $d['seats'],
                            'tripRevenue' => $d['tripRevenue']
                        ];
                    }
                }
            }
    }

    // 4. Get Route Stats (New for Reports Table)
    $routeStatsStartDate = $monthFilter . '-01';
    $routeStatsEndDate = date('Y-m-t', strtotime($routeStatsStartDate));

    if ($period === 'monthly') {
        $routeStatsEndDate = date('Y-m-t'); // Current month end
        $routeStatsStartDate = date('Y-m-01', strtotime('-11 months')); // Last 12 months
    }

    $sqlRouteStats = "SELECT routeName, serviceType, SUM(totalPrice) as revenue, SUM(seatCount) as pax 
               FROM bookings 
               WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') 
               AND date BETWEEN '$routeStatsStartDate' AND '$routeStatsEndDate'
               GROUP BY routeName, serviceType";
    
    $resRouteStats = $conn->query($sqlRouteStats);
    $routeGroups = [];

    if ($resRouteStats) {
        while($row = $resRouteStats->fetch_assoc()) {
            $route = trim($row['routeName']);
            $type = $row['serviceType'];
            $rev = (float)$row['revenue'];
            $routePax = (int)$row['pax'];
            $rLower = strtolower($route);

            // Grouping Logic (Same as Dashboard)
            $key = $route; // Default

            if ($type === 'Carter' || stripos($rLower, 'carter') !== false) {
                $key = 'Carter';
            } elseif ($type === 'Dropping' || stripos($rLower, 'dropping') !== false) {
                $key = 'Dropping';
            } elseif (stripos($rLower, 'payakumbuh') !== false && stripos($rLower, 'padang') !== false) {
                 $idxPadang = stripos($rLower, 'padang');
                 $idxPayakumbuh = stripos($rLower, 'payakumbuh');
                 if ($idxPadang !== false && $idxPayakumbuh !== false) {
                     $key = ($idxPadang < $idxPayakumbuh) ? 'Padang - Payakumbuh' : 'Payakumbuh - Padang';
                 }
            } elseif (stripos($rLower, 'bukittinggi') !== false && stripos($rLower, 'padang') !== false) {
                $idxPadang = stripos($rLower, 'padang');
                $idxBukittinggi = stripos($rLower, 'bukittinggi');
                if ($idxPadang !== false && $idxBukittinggi !== false) {
                    $key = ($idxPadang < $idxBukittinggi) ? 'Padang - Bukittinggi' : 'Bukittinggi - Padang';
                }
            } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'padang') !== false) {
                $idxPadang = stripos($rLower, 'padang');
                $idxPekanbaru = stripos($rLower, 'pekanbaru');
                if ($idxPadang !== false && $idxPekanbaru !== false) {
                    $key = ($idxPadang < $idxPekanbaru) ? 'Padang - Pekanbaru' : 'Pekanbaru - Padang';
                }
            } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'bukittinggi') !== false) {
                $idxBkt = stripos($rLower, 'bukittinggi');
                $idxPekanbaru = stripos($rLower, 'pekanbaru');
                if ($idxBkt !== false && $idxPekanbaru !== false) {
                     $key = ($idxPekanbaru < $idxBkt) ? 'Pekanbaru - Bukittinggi' : 'Bukittinggi - Pekanbaru';
                }
            } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'payakumbuh') !== false) {
                $idxPyk = stripos($rLower, 'payakumbuh');
                $idxPekanbaru = stripos($rLower, 'pekanbaru');
                if ($idxPyk !== false && $idxPekanbaru !== false) {
                     $key = ($idxPekanbaru < $idxPyk) ? 'Pekanbaru - Payakumbuh' : 'Payakumbuh - Pekanbaru';
                }
            }

            if (!isset($routeGroups[$key])) {
                $routeGroups[$key] = ['revenue' => 0, 'pax' => 0];
            }
            $routeGroups[$key]['revenue'] += $rev;
            $routeGroups[$key]['pax'] += $routePax;
        }
    }

    // Filter & Sort
    $routeGroups = array_filter($routeGroups, function($g) { return $g['revenue'] > 0 || $g['pax'] > 0; });
    uasort($routeGroups, function($a, $b) { return $b['revenue'] <=> $a['revenue']; });

    $finalRouteStats = [];
    foreach ($routeGroups as $name => $val) {
        $finalRouteStats[] = [
            'label' => $name,
            'revenue' => $val['revenue'],
            'pax' => $val['pax']
        ];
    }

    echo json_encode([
        'reports' => [
            'labels' => array_reverse($labels),
            'revenue' => array_reverse($totalRevenue), // Grand Total
            'travelRevenue' => array_reverse($travelRevenue),
            'packageRevenue' => array_reverse($packageRevenue),
            'refundRevenue' => array_reverse($refundRevenue),
            'revenueCash' => array_reverse($revenueCash),
            'revenueTransfer' => array_reverse($revenueTransfer),
            'unpaidAmount' => array_reverse($unpaidAmount),
            'pax' => array_reverse($pax),
            'details' => $details,
            'routeStats' => $finalRouteStats // NEW
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
    
    $routeCondition = "";
    if ($routeKeyword === 'Carteran') {
        $routeCondition = " AND serviceType = 'Carter' ";
    } elseif ($routeKeyword === 'Dropping') {
        $routeCondition = " AND serviceType = 'Dropping' ";
    } elseif ($routeKeyword) {
        $routeCondition = " AND routeName LIKE '$routeKeyword%' ";
    }

    $sql = "SELECT id, time, REPLACE(routeName, ' (Normal)', '') as routeName, passengerName, seatCount, seatNumbers, selectedSeats, totalPrice, paymentMethod, paymentStatus, downPaymentAmount, status 
            FROM bookings 
            WHERE $where AND status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') $routeCondition
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
        WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND passengerPhone != '' 
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
            SELECT id, date, routeName, status, totalPrice FROM bookings WHERE passengerPhone = ? AND status NOT IN ('Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak')
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

// --- DASHBOARD SUMMARY (FAST OPTIMIZED) ---
// --- DASHBOARD SUMMARY (FAST OPTIMIZED) ---
if ($action === 'get_dashboard_summary') {
    try {
        $filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'month'; // month, year
        $dateParam = isset($_GET['date']) ? $_GET['date'] : date('Y-m');

        $today = date('Y-m-d'); // For "Today" stats (always realtime)
        
        $startDate = '';
        $endDate = '';

        if ($filterType === 'year') {
            // Whole Year
            $year = $dateParam; // Expecting '2024'
            $startDate = $year . '-01-01';
            $endDate = $year . '-12-31';
        } else {
            // Month (Default)
            // Expecting '2024-01'
            $startDate = $dateParam . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
        }

        // 1. TODAY'S STATS (Travel) - Always Realtime / Today
        // Uses index on 'date' column
        $sqlToday = "SELECT 
            SUM(totalPrice) as revenue, 
            SUM(seatCount) as pax,
            SUM(CASE WHEN validationStatus = 'Menunggu Validasi' THEN 1 ELSE 0 END) as pendingValidation
            FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND date = '$today'";
        $resToday = $conn->query($sqlToday)->fetch_assoc();
        $travelToday = (float)($resToday['revenue'] ?? 0);

        // Package Today
        $sqlPkgToday = "SELECT SUM(price) as revenue FROM packages WHERE status != 'Cancelled' AND bookingDate = '$today'";
        $resPkgToday = $conn->query($sqlPkgToday)->fetch_assoc();
        $pkgToday = (float)($resPkgToday['revenue'] ?? 0);

        // Refund Revenue Today
        $sqlRefundToday = "SELECT SUM(CASE 
            WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
            WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
            ELSE 0 END) as revenue FROM cancelled_bookings WHERE date = '$today'";
        $resRefundToday = $conn->query($sqlRefundToday)->fetch_assoc();
        $refundToday = (float)($resRefundToday['revenue'] ?? 0);


        // 2. PERIOD STATS (Month or Year)
        // Changed from DATE_FORMAT to BETWEEN to use index
        $sqlPeriod = "SELECT 
            SUM(totalPrice) as revenue, 
            SUM(seatCount) as paxTotal
            FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND date BETWEEN '$startDate' AND '$endDate'";
        $resPeriod = $conn->query($sqlPeriod)->fetch_assoc();
        $travelPeriod = (float)($resPeriod['revenue'] ?? 0);

        // Package Period
        $sqlPkgPeriod = "SELECT SUM(price) as revenue FROM packages WHERE status != 'Cancelled' AND bookingDate BETWEEN '$startDate' AND '$endDate'";
        $resPkgPeriod = $conn->query($sqlPkgPeriod)->fetch_assoc();
        $pkgPeriod = (float)($resPkgPeriod['revenue'] ?? 0);

        // Refund Revenue Period
        $sqlRefundPeriod = "SELECT SUM(CASE 
            WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
            WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
            ELSE 0 END) as revenue FROM cancelled_bookings WHERE date BETWEEN '$startDate' AND '$endDate'";
        $resRefundPeriod = $conn->query($sqlRefundPeriod)->fetch_assoc();
        $refundPeriod = (float)($resRefundPeriod['revenue'] ?? 0);


        // 3. PENDING DISPATCH (Simple Count - Realtime)
        $sqlDispatch = "SELECT COUNT(id) as count 
            FROM bookings 
            WHERE status = 'Pending' 
            AND validationStatus = 'Valid' 
            AND paymentStatus IN ('Lunas', 'DP')
            AND date = '$today'"; 
        $resDispatch = $conn->query($sqlDispatch)->fetch_assoc();
        
        // 4. UNPAID (Optimized Timeframe)
        // Added date limit (last 90 days) to prevent full table scan on large dataset
        // Assuming debts older than 90 days are handled in a separate report
        $ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));
        $sqlUnpaid = "SELECT 
            COUNT(id) as count, 
            SUM(totalPrice - COALESCE(downPaymentAmount,0)) as amount 
            FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') 
            AND paymentStatus != 'Lunas' 
            AND date >= '$ninetyDaysAgo'
            AND (totalPrice - COALESCE(downPaymentAmount,0)) > 100";
        $resUnpaid = $conn->query($sqlUnpaid)->fetch_assoc();

        // 5. GRAPH DATA 
        // Logic: If Month -> Daily Data. If Year -> Monthly Data.
        $graphLabels = [];
        $graphRevenue = [];
        $graphPax = [];
        $graphMap = [];

        if ($filterType === 'year') {
             // GROUP BY Month
             // Initialize all months
             for ($m=1; $m<=12; $m++) {
                 $k = sprintf("%s-%02d", $year, $m); // YYYY-MM
                 $graphMap[$k] = ['revenue' => 0, 'pax' => 0];
             }

             // Travel
             $sqlGraph = "SELECT DATE_FORMAT(date, '%Y-%m') as dateKey, SUM(totalPrice) as revenue, SUM(seatCount) as pax 
                          FROM bookings 
                          WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND date BETWEEN '$startDate' AND '$endDate' 
                          GROUP BY DATE_FORMAT(date, '%Y-%m')";
            
             // Package
             $sqlPkgGraph = "SELECT DATE_FORMAT(bookingDate, '%Y-%m') as dateKey, SUM(price) as revenue 
                             FROM packages 
                             WHERE status != 'Cancelled' AND bookingDate BETWEEN '$startDate' AND '$endDate' 
                             GROUP BY DATE_FORMAT(bookingDate, '%Y-%m')";

             // Refund
             $sqlRefGraph = "SELECT DATE_FORMAT(date, '%Y-%m') as dateKey, SUM(CASE 
                WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
                WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
                ELSE 0 END) as revenue 
                FROM cancelled_bookings 
                WHERE date BETWEEN '$startDate' AND '$endDate' 
                GROUP BY DATE_FORMAT(date, '%Y-%m')";

        } else {
            // GROUP BY Date (Daily)
            // Initialize all days in month? Or just existing? usually graphs look better with gaps filled or just range.
            // Let's rely on data presence or simple fill.
            
            // Travel
             $sqlGraph = "SELECT date as dateKey, SUM(totalPrice) as revenue, SUM(seatCount) as pax 
                          FROM bookings 
                          WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') AND date BETWEEN '$startDate' AND '$endDate' 
                          GROUP BY date";

            // Package
             $sqlPkgGraph = "SELECT bookingDate as dateKey, SUM(price) as revenue 
                             FROM packages 
                             WHERE status != 'Cancelled' AND bookingDate BETWEEN '$startDate' AND '$endDate' 
                             GROUP BY bookingDate";

             // Refund
             $sqlRefGraph = "SELECT date as dateKey, SUM(CASE 
                WHEN paymentStatus = 'Lunas' OR validationStatus = 'Valid' THEN (totalPrice - refund_amount)
                WHEN paymentStatus = 'DP' THEN (downPaymentAmount - refund_amount)
                ELSE 0 END) as revenue 
                FROM cancelled_bookings 
                WHERE date BETWEEN '$startDate' AND '$endDate' 
                GROUP BY date";
        }

        // Execute Graph Queries
        $resGraph = $conn->query($sqlGraph);
        while($r = $resGraph->fetch_assoc()) {
            $dk = $r['dateKey'];
            if(!isset($graphMap[$dk])) $graphMap[$dk] = ['revenue' => 0, 'pax' => 0];
            $graphMap[$dk]['revenue'] += (float)$r['revenue'];
            $graphMap[$dk]['pax'] += (int)$r['pax'];
        }

        $resPkgGraph = $conn->query($sqlPkgGraph);
        while($p = $resPkgGraph->fetch_assoc()) {
            $dk = $p['dateKey'];
             if(!isset($graphMap[$dk])) $graphMap[$dk] = ['revenue' => 0, 'pax' => 0];
             $graphMap[$dk]['revenue'] += (float)$p['revenue'];
        }

        $resRefGraph = $conn->query($sqlRefGraph);
        while($rf = $resRefGraph->fetch_assoc()) {
             $dk = $rf['dateKey'];
             if(!isset($graphMap[$dk])) $graphMap[$dk] = ['revenue' => 0, 'pax' => 0];
             $graphMap[$dk]['revenue'] += (float)$rf['revenue'];
        }

         // Sort and Flatten
         ksort($graphMap);
         foreach($graphMap as $d => $val) {
             // Format Label
             if ($filterType === 'year') {
                 // Convert '2024-01' to 'Jan'
                 $dateObj = DateTime::createFromFormat('Y-m', $d);
                 $label = $dateObj ? $dateObj->format('M') : $d;
                  // Indonsia Month Logic if needed, or stick to short English 'Jan', 'Feb' etc.
                  // Or use numbers 1-12
                  // Let's use YYYY-MM for safety, frontend can format.
                  $graphLabels[] = $d; 
             } else {
                 $graphLabels[] = $d;
             }
             
             $graphRevenue[] = $val['revenue'];
             $graphPax[] = $val['pax'];
         }


        $periodData = [
            'revenue' => ($travelPeriod + $pkgPeriod + $refundPeriod),
            'pax' => (int)($resPeriod['paxTotal'] ?? 0),
            'breakdown' => [
                'travel' => $travelPeriod,
                'package' => $pkgPeriod,
                'refund' => $refundPeriod
            ]
        ];

        echo json_encode(['status' => 'success', 'data' => [
            'today' => [
                'revenue' => ($travelToday + $pkgToday + $refundToday),
                'pax' => (int)($resToday['pax'] ?? 0), 
                'pendingValidation' => (int)($resToday['pendingValidation'] ?? 0),
                'breakdown' => [
                    'travel' => $travelToday,
                    'package' => $pkgToday,
                    'refund' => $refundToday
                ]
            ],
            'period' => $periodData,
            'month' => $periodData, // BACKWARD COMPATIBILITY: Alias for 'period' to prevent crash on old frontend
            'pendingDispatch' => (int)($resDispatch['count'] ?? 0),
            'unpaid' => [
                'count' => (int)($resUnpaid['count'] ?? 0),
                'amount' => (float)($resUnpaid['amount'] ?? 0)
            ],
            'graph' => [
                'labels' => $graphLabels,
                'revenue' => $graphRevenue,
                'pax' => $graphPax
            ],
            'pie_stats' => (function() use ($conn, $startDate, $endDate) {
                // 6. PIE CHART DATA (Revenue & Pax by Route Group)
                $sqlPie = "SELECT routeName, serviceType, SUM(totalPrice) as revenue, SUM(seatCount) as pax 
                           FROM bookings 
                           WHERE status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') 
                           AND date BETWEEN '$startDate' AND '$endDate'
                           GROUP BY routeName, serviceType";
                $resPie = $conn->query($sqlPie);
                
                $groups = [];
                // Initialize default major groups if needed, but better to be dynamic.
                // We will collect all then sort/assign colors.

                while($row = $resPie->fetch_assoc()) {
                    $route = trim($row['routeName']);
                    $type = $row['serviceType'];
                    $rev = (float)$row['revenue'];
                    $pax = (int)$row['pax'];
                    $rLower = strtolower($route);

                    // Grouping Logic
                    $key = $route; // Default

                    if ($type === 'Carter' || stripos($rLower, 'carter') !== false) {
                        $key = 'Carter';
                    } elseif ($type === 'Dropping' || stripos($rLower, 'dropping') !== false) {
                        $key = 'Dropping';
                    } elseif (stripos($rLower, 'payakumbuh') !== false && stripos($rLower, 'padang') !== false) {
                         // Determine direction
                         $idxPadang = stripos($rLower, 'padang');
                         $idxPayakumbuh = stripos($rLower, 'payakumbuh');
                         
                         if ($idxPadang !== false && $idxPayakumbuh !== false) {
                             $key = ($idxPadang < $idxPayakumbuh) ? 'Padang - Payakumbuh' : 'Payakumbuh - Padang';
                         }
                    } elseif (stripos($rLower, 'bukittinggi') !== false && stripos($rLower, 'padang') !== false) {
                        $idxPadang = stripos($rLower, 'padang');
                        $idxBukittinggi = stripos($rLower, 'bukittinggi');
                        
                        if ($idxPadang !== false && $idxBukittinggi !== false) {
                            $key = ($idxPadang < $idxBukittinggi) ? 'Padang - Bukittinggi' : 'Bukittinggi - Padang';
                        }
                    } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'padang') !== false) {
                        $idxPadang = stripos($rLower, 'padang');
                        $idxPekanbaru = stripos($rLower, 'pekanbaru');
                        
                        if ($idxPadang !== false && $idxPekanbaru !== false) {
                            $key = ($idxPadang < $idxPekanbaru) ? 'Padang - Pekanbaru' : 'Pekanbaru - Padang';
                        }
                    } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'bukittinggi') !== false) {
                        $idxBkt = stripos($rLower, 'bukittinggi');
                        $idxPekanbaru = stripos($rLower, 'pekanbaru');
                        
                        if ($idxBkt !== false && $idxPekanbaru !== false) {
                             $key = ($idxPekanbaru < $idxBkt) ? 'Pekanbaru - Bukittinggi' : 'Bukittinggi - Pekanbaru';
                        }
                    } elseif (stripos($rLower, 'pekanbaru') !== false && stripos($rLower, 'payakumbuh') !== false) {
                        $idxPyk = stripos($rLower, 'payakumbuh');
                        $idxPekanbaru = stripos($rLower, 'pekanbaru');
                        
                        if ($idxPyk !== false && $idxPekanbaru !== false) {
                             $key = ($idxPekanbaru < $idxPyk) ? 'Pekanbaru - Payakumbuh' : 'Payakumbuh - Pekanbaru';
                        }
                    }
                    // Fallback to original route name for anything else (e.g. "Padang - Solok")

                    if (!isset($groups[$key])) {
                        $groups[$key] = ['revenue' => 0, 'pax' => 0];
                    }
                    $groups[$key]['revenue'] += $rev;
                    $groups[$key]['pax'] += $pax;
                }
                
                // Filter out empty groups 
                $groups = array_filter($groups, function($g) {
                    return $g['revenue'] > 0 || $g['pax'] > 0;
                });
                
                // Sort by Revenue Desc
                uasort($groups, function($a, $b) {
                    return $b['revenue'] <=> $a['revenue'];
                });

                // Assign Colors
                $richColors = [
                    '#2563eb', '#dc2626', '#16a34a', '#d97706', '#9333ea', '#0891b2', 
                    '#db2777', '#4f46e5', '#ca8a04', '#0d9488', '#be123c', '#1e293b',
                    '#64748b', '#8b5cf6', '#f43f5e', '#14b8a6'
                ];
                
                $result = [
                    'labels' => [],
                    'revenue' => [],
                    'pax' => [],
                    'colors' => []
                ];
                
                $i = 0;
                foreach ($groups as $name => $val) {
                    $result['labels'][] = $name;
                    $result['revenue'][] = $val['revenue'];
                    $result['pax'][] = $val['pax'];
                    $result['colors'][] = $richColors[$i % count($richColors)];
                    $i++;
                }
                
                return $result;
            })()
        ]]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- 7. DAILY MANIFEST (Bookings + Packages + Refunds) ---
if ($action === 'get_daily_manifest') {
    $date = $input['date'] ?? $_GET['date'] ?? date('Y-m-d');
    
    // 1. Get Bookings
    $bookings = [];
    $sqlBookings = "SELECT * FROM bookings WHERE date = '$date' AND status NOT IN ('Cancelled', 'Batal', 'Antrian', 'Ditolak') AND (validationStatus IS NULL OR validationStatus != 'Ditolak') ORDER BY id DESC";
    $res = $conn->query($sqlBookings);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $row['seatCount'] = (int)$row['seatCount'];
            $row['totalPrice'] = (float)$row['totalPrice'];
            $row['selectedSeats'] = $row['selectedSeats'] ? json_decode($row['selectedSeats']) : [];
            $bookings[] = $row;
        }
    }

    // 2. Get Packages (By bookingDate)
    $packages = [];
    $sqlPackages = "SELECT * FROM packages WHERE bookingDate = '$date' ORDER BY createdAt DESC";
    $resPkg = $conn->query($sqlPackages);
    if ($resPkg) {
        while ($row = $resPkg->fetch_assoc()) {
            $row['price'] = (float)$row['price'];
            $packages[] = $row;
        }
    }

    // 3. Get Refunds / Cancelled Bookings (By Trip Date)
    $refunds = [];
    $sqlRefunds = "SELECT * FROM cancelled_bookings WHERE date = '$date' ORDER BY cancelled_at DESC";
    $resRef = $conn->query($sqlRefunds);
    if ($resRef) {
        while ($row = $resRef->fetch_assoc()) {
            $row['totalPrice'] = (float)$row['totalPrice'];
            $row['refund_amount'] = (float)$row['refund_amount'];
            $refunds[] = $row;
        }
    }

    echo json_encode([
        'status' => 'success',
        'date' => $date,
        'bookings' => $bookings,
        'packages' => $packages,
        'refunds' => $refunds
    ]);
    exit;
}

// --- LEGACY DASHBOARD STATS (Kept for compatibility if needed, but we will switch JS) ---
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
