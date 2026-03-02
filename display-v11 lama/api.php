<?php
// FILE: api.php
// Display v10 - Stable Backend
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");



include 'base.php';
session_start();


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 0); // Disable HTML output
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');
ini_set('memory_limit', '256M'); // Increase memory
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
error_reporting(E_ALL);

function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    
    // Clean buffer
    if(ob_get_length()) ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => "PHP Error: $errstr in $errfile:$errline"
    ]);
    exit;
}
set_error_handler("jsonErrorHandler");

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        if(ob_get_length()) ob_clean();
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => "Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}"
        ]);
    }
});

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Koneksi Database Gagal: ' . $e->getMessage()]);
    exit;
}

// Handle Preflight Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Prevent Caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ---------------------------------------------------------
// 1. GET METHOD: Mengambil Semua Data / Auth Check
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    if ($action === 'check_session') {
        if (isset($_SESSION['user'])) {
            echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        }
        exit;
    }

    // --- AUTO-CLOSE OLD TRIPS (Maintenance) ---
    try {
        // Only target trips that are ALREADY DISPATCHED ('On Trip') AND from previous days.
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // echo "DEBUG: Yesterday is $yesterday\n";
        $sqlMaintenance = "SELECT * FROM trips WHERE status = 'On Trip' AND date <= '$yesterday'";
        $resMaint = $conn->query($sqlMaintenance);
        
        $tripsToClose = [];
        if ($resMaint && $resMaint->num_rows > 0) {
            while($trip = $resMaint->fetch_assoc()) {
                $tripsToClose[] = $trip;
            }
            $resMaint->free(); // Important: Free result before updates
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
                throw $e; // Re-throw to outer catch
            }
        }
    } catch (Exception $e) {
        // Log error but continue execution so UI doesn't break
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
    $result = $conn->query("SELECT * FROM bookings ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['id']; // Remove (float)
        $row['seatCount'] = (int)$row['seatCount'];
        $row['totalPrice'] = (float)$row['totalPrice'];
        $row['selectedSeats'] = $row['selectedSeats'] ? json_decode($row['selectedSeats']) : [];
        $data['bookings'][] = $row;
    }

    // Ambil Fleet
    $res = $conn->query("SELECT * FROM fleet");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = $row['id']; // Remove (float)
        $data['fleet'][] = $row;
    }

    // Ambil Drivers
    $res = $conn->query("SELECT * FROM drivers");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = $row['id']; // Remove (float)
        $data['drivers'][] = $row;
    }
    
    // Ambil Trips
    $res = $conn->query("SELECT * FROM trips");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = $row['id']; // Remove (float)
        $row['routeConfig'] = json_decode($row['routeConfig']);
        $row['fleet'] = json_decode($row['fleet']);
        $row['driver'] = json_decode($row['driver']);
        $row['passengers'] = json_decode($row['passengers']);
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
             'carter' => (int)$row['price_carter']
         ];
         unset($row['price_umum'], $row['price_pelajar'], $row['price_dropping'], $row['price_carter']);
         $data['routes'][] = $row;
     }

     // Ambil Schedule Defaults
     $data['scheduleDefaults'] = [];
     $res = $conn->query("SELECT * FROM schedule_defaults");
     if ($res) {
         while ($row = $res->fetch_assoc()) {
             $row['fleetId'] = $row['fleetId']; // Remove (float)
             $row['driverId'] = $row['driverId']; // Remove (float)
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

    // --- C. GET OCCUPIED SEATS ---
    if ($action === 'get_occupied_seats') {
        $routeId = $_GET['routeId'];
        $date = $_GET['date'];
        $time = $_GET['time'];
        $excludeId = isset($_GET['excludeId']) ? $_GET['excludeId'] : null;

        $sql = "SELECT seatNumbers, seatCount, batchNumber FROM bookings WHERE routeId=? AND date=? AND time=? AND status != 'Cancelled'";
        $params = "sss";
        $args = [$routeId, $date, $time];

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

    // --- D. GET REPORTS ---
    if ($action === 'get_reports') {
        $period = isset($_GET['period']) ? $_GET['period'] : 'daily';
        
        // Default: Daily (Filtered by Month)
        // Group bookings by Date
        $monthFilter = (!empty($_GET['month'])) ? $_GET['month'] : date('Y-m');
        
        $sql = "SELECT date, SUM(totalPrice * seatCount) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice * seatCount ELSE 0 END) as revenueCash,
                SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice * seatCount ELSE 0 END) as revenueTransfer
                FROM bookings 
                WHERE status != 'Cancelled' AND DATE_FORMAT(date, '%Y-%m') = '$monthFilter'
                GROUP BY date ORDER BY date DESC";
        
        if ($period === 'monthly') {
            $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as date, SUM(totalPrice * seatCount) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                    SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice * seatCount ELSE 0 END) as revenueCash,
                    SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice * seatCount ELSE 0 END) as revenueTransfer
                    FROM bookings WHERE status != 'Cancelled' GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY date DESC LIMIT 12";
        }

        $result = $conn->query($sql);
        $labels = [];
        $revenue = [];
        $revenueCash = [];
        $revenueTransfer = [];
        $pax = [];
        $details = [];

        while ($row = $result->fetch_assoc()) {
            $dateKey = $row['date'];
            $labels[] = $dateKey; 
            $revenue[] = (int)$row['revenue'];
            $revenueCash[] = (int)$row['revenueCash'];
            $revenueTransfer[] = (int)$row['revenueTransfer'];
            $pax[] = (int)$row['pax'];
            
            // Initialize details for this date
            $details[$dateKey] = [];
        }

        // OPTIMIZED: Fetch ALL details for the filtered period in ONE query
        // Instead of querying inside the loop
        
        $detailSql = "SELECT date, time, routeName, COUNT(id) as count, SUM(seatCount) as seats, SUM(totalPrice * seatCount) as tripRevenue 
                      FROM bookings 
                      WHERE status != 'Cancelled' AND (DATE_FORMAT(date, '%Y-%m') = '$monthFilter' OR date IN ('" . implode("','", $labels) . "'))
                      GROUP BY date, time, routeName";
                      
        // Note: The OR condition handles both daily (labels contain dates) and Monthly logic if needed, 
        // though for 'monthly' period view we might want different detail aggregation. 
        // But for 'daily' view (default), this single query works perfectly.
        
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

        // Reverse for chart (oldest to newest)
        echo json_encode([
            'reports' => [
                'labels' => array_reverse($labels),
                'revenue' => array_reverse($revenue),
                'revenueCash' => array_reverse($revenueCash),
                'revenueTransfer' => array_reverse($revenueTransfer),
                'pax' => array_reverse($pax),
                'details' => $details // Keyed by date
            ]
        ]);
        exit;
    }

    // --- E. GET REPORT DETAILS (List of Transactions) ---
    if ($action === 'get_report_details') {
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        if (!$date) {
            echo json_encode(['error' => 'Date required']);
            exit;
        }

        // Fetch bookings for this date
        // Note: For monthly view date input might be 'YYYY-MM', so we use LIKE
        if (strlen($date) === 7) {
             // Monthly
             $where = "DATE_FORMAT(date, '%Y-%m') = '$date'";
        } else {
             // Daily
             $where = "date = '$date'";
        }

        $sql = "SELECT id, time, routeName, passengerName, seatCount, seatNumbers, selectedSeats, totalPrice, paymentMethod, status 
                FROM bookings 
                WHERE $where AND status != 'Cancelled' 
                ORDER BY time ASC, id ASC";
        
        $result = $conn->query($sql);
        $bookings = [];
        while($row = $result->fetch_assoc()) {
            $row['id'] = (float)$row['id'];
            $row['seatCount'] = (int)$row['seatCount'];
            $row['totalPrice'] = (double)$row['totalPrice'];
            
            // Fallback for seatNumbers if empty
            if (empty($row['seatNumbers']) && !empty($row['selectedSeats'])) {
                $seats = json_decode($row['selectedSeats'], true);
                if (is_array($seats)) {
                    $row['seatNumbers'] = implode(', ', $seats);
                }
            }
            unset($row['selectedSeats']); // Clean up to reduce payload
            
            $bookings[] = $row;
        }

        echo json_encode(['bookings' => $bookings]);
        exit;
    }

    // --- F. GET USERS ---
    if ($action === 'get_users') {
        $sql = "SELECT id, username, name, position, placement, created_at FROM users ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $users = [];
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['status' => 'success', 'users' => $users]);
        exit;
    }

    // --- G. GET CRM DATA ---
    if ($action === 'get_crm_data') {
        // Group by Phone Number
        $sql = "SELECT passengerPhone as phone, 
                       MAX(passengerName) as name, 
                       COUNT(id) as totalTrips, 
                       SUM(totalPrice) as totalRevenue, 
                       MAX(date) as lastTrip 
                FROM bookings 
                WHERE status != 'Cancelled' AND passengerPhone != '' 
                GROUP BY passengerPhone 
                ORDER BY lastTrip DESC";
        
        $result = $conn->query($sql);
        $customers = [];
        while($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        echo json_encode(['customers' => $customers]);
        exit;
    }

    // --- H. GET CUSTOMER HISTORY ---
    if ($action === 'get_customer_history') {
        $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE passengerPhone = ? ORDER BY date DESC LIMIT 5");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        echo json_encode(['status' => 'success', 'history' => $history]);
        exit;
    }
    // --- I. GET PACKAGES ---
    if ($action === 'get_packages') {
        $sql = "SELECT * FROM packages ORDER BY createdAt DESC";
        $result = $conn->query($sql);
        $packages = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $row['id'] = (float)$row['id'];
                $row['price'] = (double)$row['price'];
                $packages[] = $row;
            }
        }
        
        echo json_encode(['packages' => $packages]);
        exit;
    }

    echo json_encode($data);
    exit;
}


// ---------------------------------------------------------
// 2. POST METHOD: Transaksi (Create/Update)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    // file_put_contents('debug_log.txt', print_r($input, true), FILE_APPEND);
    
    // Cek Action


    $action = isset($input['action']) ? $input['action'] : '';



    if ($action == 'save_user') {
        $data = $input;
        
        $mode = $data['mode']; // 'add' or 'edit'
        $username = $conn->real_escape_string($data['username']);
        $name = $conn->real_escape_string($data['name']);
        
        // New Fields
        $position = isset($data['position']) ? $conn->real_escape_string($data['position']) : '-';
        $placement = isset($data['placement']) ? $conn->real_escape_string($data['placement']) : '-';

        $password = isset($data['password']) && !empty($data['password']) ? $data['password'] : '';
        
        if ($mode == 'add') {
            // Check username exists
            $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmtCheck->bind_param("s", $username);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
                exit;
            }
            
            $id = time() . rand(100,999);
            // Default password if empty
            if (empty($password)) {
                $password = '123456';
            }
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (id, username, password, name, position, placement) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $id, $username, $passHash, $name, $position, $placement);
            
        } else {
            $id = $data['id'];
            if (!empty($password)) {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, name=?, position=?, placement=?, password=? WHERE id=?");
                $stmt->bind_param("ssssss", $username, $name, $position, $placement, $passHash, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, name=?, position=?, placement=? WHERE id=?");
                $stmt->bind_param("sssss", $username, $name, $position, $placement, $id);
            }
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action == 'delete_user') {
        $data = $input;
        $id = $data['id'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("s", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }


    // --- LOGIN / LOGOUT ---
    if ($action === 'login') {
        $username = $input['username'];
        $password = $input['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
        } else {
            // Backdoor / Fallback for 'admin' if no DB record exits yet (Safety Net)
            if ($username === 'admin' && $password === 'admin123' && !$user) {
                // Auto Create Admin
                $passHash = password_hash('admin123', PASSWORD_DEFAULT);
                $id = time();
                $conn->query("INSERT INTO users (id, username, password, name, role) VALUES ('$id', 'admin', '$passHash', 'Administrator', 'Admin')");
                
                $_SESSION['user'] = ['id'=>$id, 'username'=>'admin', 'name'=>'Administrator', 'role'=>'Admin'];
                echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
            }
        }
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['status' => 'success']);
        exit;
    }

    // --- A. SAVE BOOKING BARU (Single Insert) ---
    if ($action === 'create_booking') {
        $b = $input['data'];
        
        $sql = "INSERT INTO bookings (
            id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, 
            seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, 
            validationStatus, paymentLocation, paymentReceiver, paymentProof, status, 
            seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName,
            pickupAddress, dropoffAddress, batchNumber
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        
        $selectedSeats = json_encode($b['selectedSeats']);
        // Default values untuk field optional
        $seatCapacity = isset($b['seatCapacity']) ? $b['seatCapacity'] : null;
        $downPaymentAmount = isset($b['downPaymentAmount']) ? $b['downPaymentAmount'] : 0;
        $status = 'Pending';
        $pickupAddress = isset($b['pickupAddress']) ? $b['pickupAddress'] : '';
        $dropoffAddress = isset($b['dropoffAddress']) ? $b['dropoffAddress'] : '';

        // Handle Payment Proof Upload
        $paymentProof = $b['paymentProof'];
        if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
            $paymentProof = saveBase64Image($paymentProof, 'proof_' . $b['id']);
        }

        // Handle KTM Upload
        $ktmProof = isset($b['ktmProof']) ? $b['ktmProof'] : (isset($b['ktmImage']) ? $b['ktmImage'] : '');
        if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
            $ktmProof = saveBase64Image($ktmProof, 'ktm_' . $b['id']);
        }

        $batchNumber = isset($b['batchNumber']) ? intval($b['batchNumber']) : 1;
        // Corrected type string: ssssssss isid sssssssss dsi sss ssi (Count: 30)
        $stmt->bind_param("ssssssssisidsssssssssdsisssssi", 
            $b['id'], $b['serviceType'], $b['routeId'], $b['date'], $b['time'], 
            $b['passengerName'], $b['passengerPhone'], $b['passengerType'], $b['seatCount'], 
            $selectedSeats, $b['duration'], $b['totalPrice'], $b['paymentMethod'], 
            $b['paymentStatus'], $b['validationStatus'], $b['paymentLocation'], $b['paymentReceiver'], 
            $paymentProof, $status, $b['seatNumbers'], $ktmProof, 
            $downPaymentAmount, $b['type'], $seatCapacity, $b['priceType'], 
            $b['packageType'], $b['routeName'], $pickupAddress, $dropoffAddress, $batchNumber
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Booking berhasil disimpan']);
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Create Booking Error: " . $stmt->error . "\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $stmt->error]);
        }
        exit;
    }

    // --- B. UPDATE STATUS PEMBAYARAN (Validasi) ---
    if ($action === 'update_payment_status') {
        $id = $input['id'];
        $pStatus = $input['paymentStatus'];
        $vStatus = $input['validationStatus'];
        
        $stmt = $conn->prepare("UPDATE bookings SET paymentStatus=?, validationStatus=? WHERE id=?");
        $stmt->bind_param("sss", $pStatus, $vStatus, $id); // Changed id to s
        
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

    // --- D. VALIDATE BOOKING ---
    if ($action === 'validate_booking') {
        $id = $input['id'];
        // Update status to Confirmed/Lunas/Valid
        $stmt = $conn->prepare("UPDATE bookings SET paymentStatus = 'Lunas', validationStatus = 'Valid', status = 'Confirmed' WHERE id = ?");
        $stmt->bind_param("s", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit;
    }

    // --- D. DISPATCH TRIP BARU ---
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

            // 2. Update Status Armada & Driver
            // KARENA LANGSUNG TIBA, STATUS FLEET DAN DRIVER TIDAK PERLU DIUBAH JADI 'ON TRIP'
            // MEREKA TETAP 'Tersedia' / 'Standby' AGAR BISA LANGSUNG DIPAKAI LAGI.
            
            // $stmtFleet = $conn->prepare("UPDATE fleet SET status='On Trip' WHERE id=?");
            // $stmtFleet->bind_param("s", $t['fleet']['id']);
            // $stmtFleet->execute();

            // $stmtDriver = $conn->prepare("UPDATE drivers SET status='Jalan' WHERE id=?");
            // $stmtDriver->bind_param("s", $t['driver']['id']);
            // $stmtDriver->execute();

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
        $createdAt = date('Y-m-d H:i:s');

        // Check if exists
        // Check if exists
        $stmtCheck = $conn->prepare("SELECT id FROM trips WHERE id=?");
        $stmtCheck->bind_param("s", $id);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=?, note=? WHERE id=?");
            if(!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Update Prepare Failed: ' . $conn->error]);
                exit;
            }
            $stmt->bind_param("sssssssss", $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, note, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if(!$stmt) {
                 echo json_encode(['status' => 'error', 'message' => 'Insert Prepare Failed: ' . $conn->error]);
                 exit;
            }
            $stmt->bind_param("ssssssssss", $id, $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $createdAt);
        }

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        exit;
    }

    // --- E. UPDATE TRIP STATUS (Tiba/Kendala) ---
    if ($action === 'update_trip_status') {
        $tripId = $input['tripId'];
        $status = $input['status'];
        $fleetId = $input['fleetId'] ?? null;
        $driverId = $input['driverId'] ?? null;
        $passengers = $input['passengers'] ?? []; // Array of objects with ID

        $conn->begin_transaction();
        try {
            // Update Trip
            // Update Trip
            $stmtTrip = $conn->prepare("UPDATE trips SET status=? WHERE id=?");
            $stmtTrip->bind_param("ss", $status, $tripId);
            $stmtTrip->execute();

            if ($status === 'Tiba') {
                // Release Assets
                $stmtFleet = $conn->prepare("UPDATE fleet SET status='Tersedia' WHERE id=?");
                $stmtFleet->bind_param("s", $fleetId);
                $stmtFleet->execute();

                $stmtDriver = $conn->prepare("UPDATE drivers SET status='Standby' WHERE id=?");
                $stmtDriver->bind_param("s", $driverId);
                $stmtDriver->execute();

                // Update Bookings
                if (count($passengers) > 0) {
                    $stmtPsg = $conn->prepare("UPDATE bookings SET status='Tiba' WHERE id=?");
                    foreach($passengers as $p) {
                         $stmtPsg->bind_param("s", $p['id']);
                         $stmtPsg->execute();
                    }
                }
            } elseif ($status === 'Kendala') {
                $stmtFleet = $conn->prepare("UPDATE fleet SET status='Perbaikan' WHERE id=?");
                $stmtFleet->bind_param("s", $fleetId);
                $stmtFleet->execute();
            }

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    // --- SCHEDULE DEFAULTS ---
    if ($action === 'save_schedule_default') {
        $routeId = $input['routeId'];
        $time = $input['time'];
        $fleetId = $input['fleetId'];
        $driverId = $input['driverId'];
        
        // Fix: Delete existing default first to avoid duplicates (since no unique key exists)
        $del = $conn->prepare("DELETE FROM schedule_defaults WHERE routeId=? AND time=?");
        $del->bind_param("ss", $routeId, $time);
        $del->execute();
        
        $stmt = $conn->prepare("INSERT INTO schedule_defaults (routeId, time, fleetId, driverId) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
             echo json_encode(['status' => 'error', 'message' => 'Prepare Failed: ' . $conn->error]);
             exit;
        }
        $stmt->bind_param("ssss", $routeId, $time, $fleetId, $driverId);
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- DRIVERS CRUD ---
    if ($action === 'create_driver') {
        $d = $input['data'];
        $id = $d['id'];
        $name = $d['name'];
        $phone = $d['phone'];
        $license = $d['licenseType'];
        $status = $d['status'];

        $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, licenseType, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $id, $name, $phone, $license, $status);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'update_driver') {
        $d = $input['data'];
        $id = $d['id'];
        $name = $d['name'];
        $phone = $d['phone'];
        $license = $d['licenseType'];
        $status = $d['status'];

        $stmt = $conn->prepare("UPDATE drivers SET name=?, phone=?, licenseType=?, status=? WHERE id=?");
        $stmt->bind_param("sssss", $name, $phone, $license, $status, $id);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'delete_driver') {
        $id = $input['id'];
        $stmt = $conn->prepare("DELETE FROM drivers WHERE id=?");
        $stmt->bind_param("s", $id);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // if ( $action === 'upadate_user' ){

    // }
    // --- FLEET CRUD ---
    if ($action === 'create_fleet') {
        $f = $input['data'];
        $id = $f['id'];
        $name = $f['name'];
        $plate = $f['plate'];
        $capacity = $f['capacity'];
        $status = $f['status'];
        $icon = isset($f['icon']) ? $f['icon'] : 'bi-truck-front-fill';

        $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $id, $name, $plate, $capacity, $status, $icon);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'update_fleet') {
        $f = $input['data'];
        $id = $f['id'];
        $name = $f['name'];
        $plate = $f['plate'];
        $capacity = $f['capacity'];
        $status = $f['status'];
        $icon = isset($f['icon']) ? $f['icon'] : 'bi-truck-front-fill';

        $stmt = $conn->prepare("UPDATE fleet SET name=?, plate=?, capacity=?, status=?, icon=? WHERE id=?");
        $stmt->bind_param("ssisss", $name, $plate, $capacity, $status, $icon, $id);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'delete_fleet') {
        $id = $input['id'];
        $stmt = $conn->prepare("DELETE FROM fleet WHERE id=?");
        $stmt->bind_param("s", $id);
        
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }


    // --- F. MANAJEMEN ARMADA (FLEET) ---
    if ($action === 'save_fleet') {
        $id = $input['id'];
        $name = $input['name'];
        $plate = $input['plate'];
        $capacity = $input['capacity'];
        $status = $input['status'];
        $icon = $input['icon'];

        $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, plate=?, capacity=?, status=?, icon=?");
        $stmt->bind_param("sssissssiss", 
            $id, $name, $plate, $capacity, $status, $icon,
            $name, $plate, $capacity, $status, $icon
        );

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'delete_fleet') {
        $id = $input['id'];
        $stmt = $conn->prepare("DELETE FROM fleet WHERE id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- G. MANAJEMEN SUPIR (DRIVER) ---
    if ($action === 'save_driver') {
        $id = $input['id'];
        $name = $input['name'];
        $phone = $input['phone'];
        $status = $input['status'];
        $licenseType = isset($input['licenseType']) ? $input['licenseType'] : '';

        $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, phone=?, status=?, licenseType=?");
        $stmt->bind_param("sssssssss", 
            $id, $name, $phone, $status, $licenseType,
            $name, $phone, $status, $licenseType
        );

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'delete_driver') {
        $id = $input['id'];
        $stmt = $conn->prepare("DELETE FROM drivers WHERE id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- H. MANAJEMEN RUTE (ROUTE) ---
    if ($action === 'save_route') {
        $id = $input['id'];
        $origin = $input['origin'];
        $destination = $input['destination'];
        $schedules = json_encode($input['schedules']);
        $prices = $input['prices'];

        $stmt = $conn->prepare("INSERT INTO routes (id, origin, destination, price_umum, price_pelajar, price_dropping, price_carter, schedules) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE origin=?, destination=?, price_umum=?, price_pelajar=?, price_dropping=?, price_carter=?, schedules=?");
        
        $stmt->bind_param("sssddddsssdddds", 
            $id, $origin, $destination, $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], $schedules,
            $origin, $destination, $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], $schedules
        );

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    if ($action === 'delete_route') {
        $id = $input['id'];
        $stmt = $conn->prepare("DELETE FROM routes WHERE id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- I. UPDATE BOOKING FULL (EDIT BOOKING) ---
    if ($action === 'update_booking_full') {
        $id = $input['id'];
        $adminName = $input['adminName'];
        
        // Data to update
        $date = $input['date'];
        $time = $input['time'];
        $routeId = isset($input['routeId']) ? $input['routeId'] : null;
        $passengerName = $input['passengerName'];
        $passengerPhone = $input['passengerPhone'];
        $passengerType = $input['passengerType'];
        $seatNumbers = $input['seatNumbers']; // String "1, 2"
        $seatCount = $input['seatCount'];
        $selectedSeats = json_encode($input['selectedSeats']); // Array [1, 2]
        $totalPrice = $input['totalPrice'];
        $pickupAddress = $input['pickupAddress'];
        $dropoffAddress = $input['dropoffAddress'];
        
        $conn->begin_transaction();
        try {
            // 1. Get Previous Data
            $prev = $conn->query("SELECT * FROM bookings WHERE id='$id'")->fetch_assoc();
            $prevJson = json_encode($prev);
            
            // 2. Update Booking
            $sql = "UPDATE bookings SET 
                    date=?, time=?, routeId=?, 
                    passengerName=?, passengerPhone=?, passengerType=?,
                    seatNumbers=?, seatCount=?, selectedSeats=?,
                    totalPrice=?, pickupAddress=?, dropoffAddress=?
                    WHERE id=?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssisssss", 
                $date, $time, $routeId, 
                $passengerName, $passengerPhone, $passengerType,
                $seatNumbers, $seatCount, $selectedSeats,
                $totalPrice, $pickupAddress, $dropoffAddress,
                $id
            );
            $stmt->execute();
            
            // 3. Insert Log
            $logId = time() . rand(100,999);
            $actionLog = 'Edit Full Data';
            
            // New Value Snapshot
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
    // --- K. MOVE BOOKING SCHEDULE (DRAG & DROP) ---
    if ($action === 'move_booking_schedule') {
        $id = $input['id'];
        $date = $input['date'];
        $time = $input['time'];
        $clearSeat = isset($input['clear_seat']) && $input['clear_seat'];
        
        $newSeatNumbers = isset($input['seatNumbers']) ? $input['seatNumbers'] : null;
        $batchNumber = isset($input['batchNumber']) ? intval($input['batchNumber']) : null;
        
        $conn->begin_transaction();
        try {
            // Dynamic Update Query
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

    // --- L. CREATE PACKAGE (EXPEDITION STYLE) ---
    if ($action === 'create_package') {
        $data = $input['data'];
        
        // Generate Receipt Number (RES-YYYYMMDD-XXXX)
        $receiptNumber = 'RES-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $conn->begin_transaction();
        try {
            // 1. Insert Package
            $stmt = $conn->prepare("INSERT INTO packages (receiptNumber, senderName, senderPhone, receiverName, receiverPhone, itemDescription, itemType, category, route, price, paymentMethod, paymentStatus, status, pickupAddress, dropoffAddress, mapLink, bookingDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssdssssssss", 
                $receiptNumber,
                $data['senderName'], 
                $data['senderPhone'], 
                $data['receiverName'], 
                $data['receiverPhone'], 
                $data['itemDescription'], 
                $data['itemType'], 
                $data['category'], 
                $data['route'], 
                $data['price'], 
                $data['paymentMethod'], 
                $data['paymentStatus'], 
                $data['status'], 
                $data['pickupAddress'], 
                $data['dropoffAddress'], 
                $data['mapLink'],
                $data['bookingDate']
            );
            $stmt->execute();
            $packageId = $conn->insert_id;

            // 2. Insert Initial Log
            $logDesc = "Paket berhasil dibuat (No. Resi: $receiptNumber)";
            $adminName = isset($data['adminName']) ? $data['adminName'] : 'Admin';
            
            $stmtLog = $conn->prepare("INSERT INTO package_logs (package_id, status, description, admin_name) VALUES (?, ?, ?, ?)");
            $initStatus = $data['status']; // Usually 'Pending'
            $stmtLog->bind_param("isss", $packageId, $initStatus, $logDesc, $adminName);
            $stmtLog->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'id' => $packageId, 'receiptNumber' => $receiptNumber]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // --- M. UPDATE PACKAGE STATUS WITH LOG ---
    if ($action === 'update_package_status') {
        $id = $input['id'];
        $status = $input['status'];
        $description = isset($input['description']) ? $input['description'] : "Status diubah menjadi $status";
        $adminName = isset($input['adminName']) ? $input['adminName'] : 'Admin';
        $location = isset($input['location']) ? $input['location'] : '-';

        $conn->begin_transaction();
        try {
            // 1. Update Current Status
            $stmt = $conn->prepare("UPDATE packages SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            
            // 2. Insert History Log
            $stmtLog = $conn->prepare("INSERT INTO package_logs (package_id, status, description, location, admin_name) VALUES (?, ?, ?, ?, ?)");
            $stmtLog->bind_param("issss", $id, $status, $description, $location, $adminName);
            $stmtLog->execute();

            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Gagal update: ' . $e->getMessage()]);
        }
        exit;
    }

    // --- N. GET PACKAGE DETAILS & LOGS ---
    if ($action === 'get_package_details') {
        $id = $input['id'];
        
        // Get Package
        $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $pkg = $stmt->get_result()->fetch_assoc();
        
        if (!$pkg) {
            echo json_encode(['status' => 'error', 'message' => 'Paket tidak ditemukan']);
            exit;
        }

        // Get Logs
        $stmtLog = $conn->prepare("SELECT * FROM package_logs WHERE package_id = ? ORDER BY created_at DESC");
        $stmtLog->bind_param("i", $id);
        $stmtLog->execute();
        $resLogs = $stmtLog->get_result();
        
        $logs = [];
        while($row = $resLogs->fetch_assoc()) {
            $logs[] = $row;
        }

        echo json_encode(['status' => 'success', 'package' => $pkg, 'logs' => $logs]);
        exit;
    }

    // ========================================================
    // PAYMENT MANAGEMENT API ENDPOINTS
    // ========================================================
    
    // --- O. ADD PAYMENT TO BOOKING ---
    if ($action === 'add_payment') {
        $data = $input['data'];
        $bookingId = $data['booking_id'];
        $amount = $data['amount'];
        $paymentMethod = $data['payment_method'];
        $paymentLocation = isset($data['payment_location']) ? $data['payment_location'] : '';
        $paymentReceiver = isset($data['payment_receiver']) ? $data['payment_receiver'] : '';
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        // Handle Payment Proof Upload
        $paymentProof = isset($data['payment_proof']) ? $data['payment_proof'] : '';
        if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
            $proofId = time() . rand(100,999);
            $paymentProof = saveBase64Image($paymentProof, 'payment_' . $proofId);
        }
        
        $conn->begin_transaction();
        try {
            // 1. Get booking data
            $stmt = $conn->prepare("SELECT totalPrice, seatCount, downPaymentAmount FROM bookings WHERE id=?");
            $stmt->bind_param("s", $bookingId);
            $stmt->execute();
            $booking = $stmt->get_result()->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Booking tidak ditemukan");
            }
            
            $totalBill = $booking['totalPrice'] * $booking['seatCount'];
            
            // 2. Get total paid so far
            $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_paid FROM payment_transactions WHERE booking_id=?");
            $stmt->bind_param("s", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $totalPaid = $result['total_paid'];
            
            // 3. Calculate new total paid and remaining
            $newTotalPaid = $totalPaid + $amount;
            $remaining = $totalBill - $newTotalPaid;
            $isFullyPaid = ($remaining <= 0) ? 1 : 0;
            
            // 4. Insert payment transaction
            $paymentDate = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO payment_transactions (booking_id, payment_date, payment_method, amount, payment_location, payment_receiver, payment_proof, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssds sss", $bookingId, $paymentDate, $paymentMethod, $amount, $paymentLocation, $paymentReceiver, $paymentProof, $notes);
            $stmt->execute();
            
            // 5. Update booking payment status
            $paymentStatus = $isFullyPaid ? 'Lunas' : ($newTotalPaid > 0 ? 'DP' : 'Belum Bayar');
            $validationStatus = $isFullyPaid ? 'Valid' : 'Menunggu Validasi';
            
            $stmt = $conn->prepare("UPDATE bookings SET 
                payment_remaining = ?,
                is_fully_paid = ?,
                paymentStatus = ?,
                validationStatus = ?,
                last_payment_date = ?,
                payment_type = 'installment'
                WHERE id = ?");
            $stmt->bind_param("dissss", $remaining, $isFullyPaid, $paymentStatus, $validationStatus, $paymentDate, $bookingId);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => [
                    'total_paid' => $newTotalPaid,
                    'remaining' => $remaining,
                    'is_fully_paid' => $isFullyPaid
                ]
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    // --- P. GET PAYMENT HISTORY FOR BOOKING ---
    if ($action === 'get_payment_history') {
        $bookingId = isset($_GET['booking_id']) ? $_GET['booking_id'] : $input['booking_id'];
        
        $stmt = $conn->prepare("SELECT * FROM payment_transactions WHERE booking_id = ? ORDER BY payment_date DESC");
        $stmt->bind_param("s", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'payments' => $payments]);
        exit;
    }
    
    // --- Q. GET OUTSTANDING BOOKINGS (BELUM LUNAS) ---
    if ($action === 'get_outstanding_bookings') {
        $sql = "SELECT * FROM v_outstanding_bookings ORDER BY days_overdue DESC, date ASC";
        $result = $conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'bookings' => $bookings]);
        exit;
    }
    
    // --- R. GET BILLING REPORT ---
    if ($action === 'get_billing_report') {
        // Get summary statistics
        $stats = [
            'total_outstanding' => 0,
            'total_outstanding_count' => 0,
            'total_dp' => 0,
            'total_dp_count' => 0,
            'total_overdue' => 0,
            'total_overdue_count' => 0
        ];
        
        // Outstanding bookings
        $result = $conn->query("SELECT COUNT(*) as cnt, SUM(remaining_amount) as total FROM v_outstanding_bookings");
        $row = $result->fetch_assoc();
        $stats['total_outstanding_count'] = $row['cnt'];
        $stats['total_outstanding'] = $row['total'] ?? 0;
        
        // DP bookings
        $result = $conn->query("SELECT COUNT(*) as cnt, SUM(downPaymentAmount) as total FROM bookings WHERE paymentMethod='DP' AND is_fully_paid=0");
        $row = $result->fetch_assoc();
        $stats['total_dp_count'] = $row['cnt'];
        $stats['total_dp'] = $row['total'] ?? 0;
        
        // Overdue (past departure date and not fully paid)
        $result = $conn->query("SELECT COUNT(*) as cnt, SUM(remaining_amount) as total FROM v_outstanding_bookings WHERE days_overdue > 0");
        $row = $result->fetch_assoc();
        $stats['total_overdue_count'] = $row['cnt'];
        $stats['total_overdue'] = $row['total'] ?? 0;
        
        // Recent payments (last 7 days)
        $result = $conn->query("SELECT pt.*, b.passengerName, b.passengerPhone 
            FROM payment_transactions pt 
            JOIN bookings b ON pt.booking_id = b.id 
            WHERE pt.payment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            ORDER BY pt.payment_date DESC 
            LIMIT 50");
        $recentPayments = [];
        while ($row = $result->fetch_assoc()) {
            $recentPayments[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'stats' => $stats,
            'recent_payments' => $recentPayments
        ]);
        exit;
    }
    
    // --- S. UPDATE BOOKING PAYMENT (SUPPORT SPLIT PAYMENT) ---
    if ($action === 'update_booking_payment') {
        $bookingId = $input['booking_id'];
        $paymentType = $input['payment_type']; // 'single', 'split', 'installment'
        $paymentMethod = $input['payment_method'];
        $amount = isset($input['amount']) ? $input['amount'] : 0;
        
        // For split payment
        $splitPayments = isset($input['split_payments']) ? $input['split_payments'] : [];
        
        $conn->begin_transaction();
        try {
            // Update booking
            $stmt = $conn->prepare("UPDATE bookings SET payment_type = ?, paymentMethod = ? WHERE id = ?");
            $stmt->bind_param("sss", $paymentType, $paymentMethod, $bookingId);
            $stmt->execute();
            
            // If split payment, record each payment
            if ($paymentType === 'split' && !empty($splitPayments)) {
                $paymentDate = date('Y-m-d H:i:s');
                foreach ($splitPayments as $payment) {
                    $method = $payment['method'];
                    $amt = $payment['amount'];
                    $location = isset($payment['location']) ? $payment['location'] : '';
                    $receiver = isset($payment['receiver']) ? $payment['receiver'] : '';
                    $proof = isset($payment['proof']) ? $payment['proof'] : '';
                    
                    // Handle proof upload
                    if (!empty($proof) && strpos($proof, 'data:image') === 0) {
                        $proofId = time() . rand(100,999);
                        $proof = saveBase64Image($proof, 'split_' . $proofId);
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO payment_transactions (booking_id, payment_date, payment_method, amount, payment_location, payment_receiver, payment_proof, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'Split Payment')");
                    $stmt->bind_param("sssdsss", $bookingId, $paymentDate, $method, $amt, $location, $receiver, $proof);
                    $stmt->execute();
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
}

function saveBase64Image($base64String, $filenamePrefix) {
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
        $data = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif
        
        if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
            return ''; // Invalid type
        }
        
        $data = base64_decode($data);
        if ($data === false) {
            return ''; // Decode failed
        }
        
        $filename = $filenamePrefix . '.' . $type;
        $path = 'image/proofs/' . $filename;
        
        // Ensure directory exists
        if (!file_exists('image/proofs')) {
            mkdir('image/proofs', 0777, true);
        }
        
        if (file_put_contents($path, $data)) {
            return $path;
        }
    }
    return '';
}
?>