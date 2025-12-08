<?php
// FILE: api.php
// Display v10 - Stable Backend
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// --- KONFIGURASI DATABASE ---
$host = 'localhost';
$user = 'root';      // Ganti dengan username database hosting Anda
$pass = '';          // Ganti dengan password database hosting Anda
$db   = 'sutanraya_v11'; // Ganti dengan nama database Anda

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

        $stmt = $conn->prepare("SELECT seatNumbers, seatCount FROM bookings WHERE routeId=? AND date=? AND time=? AND status != 'Cancelled'");
        $stmt->bind_param("sss", $routeId, $date, $time);
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
        
        // Default: Daily (Last 30 days)
        // Group bookings by Date
        $sql = "SELECT date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice ELSE 0 END) as revenueCash,
                SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice ELSE 0 END) as revenueTransfer
                FROM bookings WHERE status != 'Cancelled' GROUP BY date ORDER BY date DESC LIMIT 30";
        
        if ($period === 'monthly') {
            $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as date, SUM(totalPrice) as revenue, SUM(seatCount) as pax, COUNT(id) as bookingCount,
                    SUM(CASE WHEN paymentMethod = 'Cash' THEN totalPrice ELSE 0 END) as revenueCash,
                    SUM(CASE WHEN paymentMethod = 'Transfer' OR paymentMethod = 'DP' THEN totalPrice ELSE 0 END) as revenueTransfer
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
            $detailSql = "SELECT time, routeName, COUNT(id) as count, SUM(seatCount) as seats, SUM(totalPrice) as tripRevenue FROM bookings WHERE date='$dateKey' AND status != 'Cancelled' GROUP BY time, routeName";
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

    echo json_encode($data);
    exit;
}

// ---------------------------------------------------------
// 2. POST METHOD: Transaksi (Create/Update)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Cek Action
    $action = isset($input['action']) ? $input['action'] : '';

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
        $ktmProof = $b['ktmProof'];
        if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
            $ktmProof = saveBase64Image($ktmProof, 'ktm_' . $b['id']);
        }

        // Changed 'd' to 's' for id, removed spaces if any (though none here)
        $stmt->bind_param("sssssssisddsssssssssdsissssss", 
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