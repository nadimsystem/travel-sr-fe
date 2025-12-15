<?php
// FILE: api.php
// Display v10 - Stable Backend
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// // --- KONFIGURASI DATABASE ---
// $host = 'localhost';
// $user = 'sutanray_admin2';      // Ganti dengan username database hosting Anda
// $pass = 'adminpass1998';          // Ganti dengan password database hosting Anda
// $db   = 'sutanray_v11'; // Ganti dengan nama database Anda
// --- KONFIGURASI DATABASE ---
// $host = 'localhost';
// $user = 'root';      // Ganti dengan username database hosting Anda
// $pass = '';          // Ganti dengan password database hosting Anda
// $db   = 'sutanraya_v11'; // Ganti dengan nama database Anda

include 'base.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 0); // Disable HTML output
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');
ini_set('memory_limit', '256M'); // Increase memory
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
error_reporting(E_ALL);

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

// ---------------------------------------------------------
// 1. GET METHOD: Mengambil Semua Data (Read Only)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : null;

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
        $row['id'] = (float)$row['id']; 
        $row['seatCount'] = (int)$row['seatCount'];
        $row['totalPrice'] = (float)$row['totalPrice'];
        $row['selectedSeats'] = $row['selectedSeats'] ? json_decode($row['selectedSeats']) : [];
        $data['bookings'][] = $row;
    }

    // Ambil Fleet
    $res = $conn->query("SELECT * FROM fleet");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $data['fleet'][] = $row;
    }

    // Ambil Drivers
    $res = $conn->query("SELECT * FROM drivers");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $data['drivers'][] = $row;
    }
    
    // Ambil Trips
    $res = $conn->query("SELECT * FROM trips");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
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

        $sql = "SELECT seatNumbers, seatCount FROM bookings WHERE routeId=? AND date=? AND time=? AND status != 'Cancelled'";
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
            
            // Detailed breakdown: Time, Route, Pax, Revenue
            $detailSql = "SELECT time, routeName, COUNT(id) as count, SUM(seatCount) as seats, SUM(totalPrice * seatCount) as tripRevenue FROM bookings WHERE date='$dateKey' AND status != 'Cancelled' GROUP BY time, routeName";
            $detailRes = $conn->query($detailSql);
            $dayDetails = [];
            while ($d = $detailRes->fetch_assoc()) {
                $dayDetails[] = $d;
            }
            $details[$dateKey] = $dayDetails;
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
        echo json_encode(['users' => $users]);
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
        $phone = $conn->real_escape_string($_GET['phone']);
        $sql = "SELECT id, date, time, routeName, seatCount, totalPrice, status 
                FROM bookings 
                WHERE passengerPhone = '$phone' 
                ORDER BY date DESC";
        
        $result = $conn->query($sql);
        $history = [];
        while($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        echo json_encode(['history' => $history]);
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
    file_put_contents('debug_log.txt', print_r($input, true), FILE_APPEND);
    
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
            $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
            if ($check->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
                exit;
            }
            
            $id = time() . rand(100,999);
            // Default password if empty
            if (empty($password)) {
                $password = '123456';
            }
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (id, username, password, name, position, placement) VALUES ('$id', '$username', '$passHash', '$name', '$position', '$placement')";
            
        } else {
            $id = $data['id'];
            if (!empty($password)) {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username='$username', name='$name', position='$position', placement='$placement', password='$passHash' WHERE id='$id'";
            } else {
                $sql = "UPDATE users SET username='$username', name='$name', position='$position', placement='$placement' WHERE id='$id'";
            }
        }
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action == 'delete_user') {
        $data = $input;
        $id = $conn->real_escape_string($data['id']);
        
        // Prevent deleting last admin if needed, but for now simple delete
        if ($conn->query("DELETE FROM users WHERE id='$id'")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
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
            pickupAddress, dropoffAddress
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

        // Corrected type string: ssssssss isid sssssssss dsi sssss
        // 1-8: s (inc passType)
        // 9: i (seatCount)
        // 10: s (selectedSeats - JSON string)
        // 11: i (duration)
        // 12: d (totalPrice)
        // 13-21: s (inc ktmProof)
        // 22: d (downPayment)
        // 23: s (type)
        // 24: i (seatCapacity)
        // 25-29: s
        $stmt->bind_param("ssssssssisidsssssssssdsisssss", 
            $b['id'], $b['serviceType'], $b['routeId'], $b['date'], $b['time'], 
            $b['passengerName'], $b['passengerPhone'], $b['passengerType'], $b['seatCount'], 
            $selectedSeats, $b['duration'], $b['totalPrice'], $b['paymentMethod'], 
            $b['paymentStatus'], $b['validationStatus'], $b['paymentLocation'], $b['paymentReceiver'], 
            $paymentProof, $status, $b['seatNumbers'], $ktmProof, 
            $downPaymentAmount, $b['type'], $seatCapacity, $b['priceType'], 
            $b['packageType'], $b['routeName'], $pickupAddress, $dropoffAddress
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Booking berhasil disimpan']);
        } else {
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
        $stmt->bind_param("s", $id); // Changed id to s
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- D. DISPATCH TRIP BARU ---
    if ($action === 'create_trip') {
        $t = $input['data'];
        
        $conn->begin_transaction();
        try {
            // 1. Insert Trip
            $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, departureTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $routeJson = json_encode($t['routeConfig']);
            $fleetJson = json_encode($t['fleet']);
            $driverJson = json_encode($t['driver']);
            $passJson = json_encode($t['passengers']);
            $now = date('Y-m-d H:i:s');
            
            $stmt->bind_param("sssssss", $t['id'], $routeJson, $fleetJson, $driverJson, $passJson, $t['status'], $now); // Changed id to s
            $stmt->execute();

            // 2. Update Status Armada & Driver
            $conn->query("UPDATE fleet SET status='On Trip' WHERE id='{$t['fleet']['id']}'"); // Quote ID
            $conn->query("UPDATE drivers SET status='Jalan' WHERE id='{$t['driver']['id']}'"); // Quote ID

            // 3. Update Status Booking Penumpang
            foreach ($t['passengers'] as $p) {
                $conn->query("UPDATE bookings SET status='On Trip' WHERE id='{$p['id']}'"); // Quote ID
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
        $check = $conn->query("SELECT id FROM trips WHERE id='$id'");
        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=?, note=? WHERE id=?");
            $stmt->bind_param("sssssssss", $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, date, time, note, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $id, $routeConfig, $fleet, $driver, $passengers, $status, $date, $time, $note, $createdAt);
        }

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    // --- E. UPDATE TRIP STATUS (Tiba/Kendala) ---
    if ($action === 'update_trip_status') {
        $tripId = $input['tripId'];
        $status = $input['status'];
        $fleetId = $input['fleetId'];
        $driverId = $input['driverId'];
        $passengers = $input['passengers']; // Array of objects with ID

        $conn->begin_transaction();
        try {
            // Update Trip
            $conn->query("UPDATE trips SET status='$status' WHERE id='$tripId'"); // Quote ID

            if ($status === 'Tiba') {
                // Release Assets
                $conn->query("UPDATE fleet SET status='Tersedia' WHERE id='$fleetId'"); // Quote ID
                $conn->query("UPDATE drivers SET status='Standby' WHERE id='$driverId'"); // Quote ID
                // Update Bookings
                foreach($passengers as $p) {
                     $conn->query("UPDATE bookings SET status='Tiba' WHERE id='{$p['id']}'"); // Quote ID
                }
            } elseif ($status === 'Kendala') {
                $conn->query("UPDATE fleet SET status='Perbaikan' WHERE id='$fleetId'"); // Quote ID
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
        
        $stmt = $conn->prepare("INSERT INTO schedule_defaults (routeId, time, fleetId, driverId) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE fleetId=?, driverId=?");
        $stmt->bind_param("ssssss", $routeId, $time, $fleetId, $driverId, $fleetId, $driverId);
        
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
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
        $id = $conn->real_escape_string($input['id']);
        $sql = "SELECT * FROM booking_logs WHERE booking_id='$id' ORDER BY timestamp DESC";
        $result = $conn->query($sql);
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
        
        $conn->begin_transaction();
        try {
            // Update Booking
            if ($clearSeat) {
                $stmt = $conn->prepare("UPDATE bookings SET date=?, time=?, seatNumbers=NULL WHERE id=?");
                $stmt->bind_param("sss", $date, $time, $id);
            } else {
                $stmt = $conn->prepare("UPDATE bookings SET date=?, time=? WHERE id=?");
                $stmt->bind_param("sss", $date, $time, $id);
            }
            $stmt->execute();
            
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