<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Handle File Upload
if (isset($_FILES['file'])) {
    $targetDir = "transfer/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    $allowTypes = array('jpg','png','jpeg','gif','pdf');
    if(in_array(strtolower($fileType), $allowTypes)){
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
            echo json_encode(['status' => 'success', 'fileName' => $fileName]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'bookings' => [],
        'fleet' => [],
        'drivers' => [],
        'trips' => []
    ];

    // Fetch Bookings
    $result = $conn->query("SELECT * FROM bookings");
    while ($row = $result->fetch_assoc()) {
        // Convert numeric types
        $row['id'] = (float)$row['id']; // JS uses timestamps which are large numbers
        $row['seatCount'] = (int)$row['seatCount'];
        $row['duration'] = (int)$row['duration'];
        $row['totalPrice'] = (float)$row['totalPrice'];
        $row['downPaymentAmount'] = (float)$row['downPaymentAmount'];
        $row['seatCapacity'] = $row['seatCapacity'] ? (int)$row['seatCapacity'] : null;
        
        // Parse JSON fields
        $row['selectedSeats'] = $row['selectedSeats'] ? json_decode($row['selectedSeats']) : [];
        
        $data['bookings'][] = $row;
    }

    // Fleet
    $res = $conn->query("SELECT * FROM fleet");
    while($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $row['capacity'] = (int)$row['capacity'];
        $data['fleet'][] = $row;
    }

    // Drivers
    $res = $conn->query("SELECT * FROM drivers");
    while($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $data['drivers'][] = $row;
    }
    
    // Trips
    $res = $conn->query("SELECT * FROM trips");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (float)$row['id'];
        $row['routeConfig'] = json_decode($row['routeConfig']);
        $row['fleet'] = json_decode($row['fleet']);
        $row['driver'] = json_decode($row['driver']);
        $row['passengers'] = json_decode($row['passengers']);
        $data['trips'][] = $row;
    }

    // Routes
    $res = $conn->query("SELECT * FROM routes");
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

    // Bus Routes
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

    if (isset($_GET['action']) && $_GET['action'] === 'get_reports') {
        $period = isset($_GET['period']) ? $_GET['period'] : 'monthly';
        $reports = ['labels' => [], 'revenue' => [], 'pax' => []];
        
        $groupBy = "";
        $dateFormat = "";
        
        switch ($period) {
            case 'daily':
                $groupBy = "DATE(date)";
                $dateFormat = "%Y-%m-%d";
                break;
            case 'weekly':
                $groupBy = "YEARWEEK(date, 1)";
                $dateFormat = "Week %v %Y";
                break;
            case 'yearly':
                $groupBy = "YEAR(date)";
                $dateFormat = "%Y";
                break;
            case 'monthly':
            default:
                $groupBy = "DATE_FORMAT(date, '%Y-%m')";
                $dateFormat = "%Y-%m";
                break;
        }
        
        $sql = "SELECT 
                    DATE_FORMAT(date, '$dateFormat') as label, 
                    SUM(totalPrice) as totalRevenue, 
                    COUNT(*) as totalPax 
                FROM bookings 
                WHERE status != 'Batal' 
                GROUP BY $groupBy 
                ORDER BY date ASC";
                
        $res = $conn->query($sql);
        while($row = $res->fetch_assoc()) {
            $reports['labels'][] = $row['label'];
            $reports['revenue'][] = (float)$row['totalRevenue'];
            $reports['pax'][] = (int)$row['totalPax'];
        }
        
        // Add reports to response
        $data['reports'] = $reports;
    }

    echo json_encode($data);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $input = json_decode(file_get_contents('php://input'), true);

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

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Route saved']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to save route: ' . $conn->error]);
            }
        } else if ($action === 'delete_route') {
            $id = $input['id'];
            $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
            $stmt->bind_param("s", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Route deleted']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete route: ' . $conn->error]);
            }
        }
        exit;
    }

    // Default Sync Logic (Only if no action)
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input for sync (must have at least one known key)
    if (!$input) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    if(!isset($input['bookings']) && !isset($input['fleet']) && !isset($input['drivers']) && !isset($input['trips']) && !isset($input['routes']) && !isset($input['busRoutes'])) {
        // If not a sync request and not an action, ignore or error
        // But for safety, let's just exit if empty to prevent accidental deletion
        if(empty($input)) exit; 
    }

    $conn->begin_transaction();

    try {
        // 1. Bookings
        $conn->query("DELETE FROM bookings");
        if (!empty($input['bookings'])) {
            $stmt = $conn->prepare("INSERT INTO bookings (id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, validationStatus, paymentLocation, paymentReceiver, paymentProof, status, seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($input['bookings'] as $b) {
                $selectedSeats = isset($b['selectedSeats']) ? json_encode($b['selectedSeats']) : '[]';
                $seatCapacity = isset($b['seatCapacity']) ? $b['seatCapacity'] : null;
                $downPaymentAmount = isset($b['downPaymentAmount']) ? $b['downPaymentAmount'] : 0;
                
                $stmt->bind_param("dsssssssisddsssssssssdsisss", 
                    $b['id'], $b['serviceType'], $b['routeId'], $b['date'], $b['time'], 
                    $b['passengerName'], $b['passengerPhone'], $b['passengerType'], $b['seatCount'], 
                    $selectedSeats, $b['duration'], $b['totalPrice'], $b['paymentMethod'], 
                    $b['paymentStatus'], $b['validationStatus'], $b['paymentLocation'], $b['paymentReceiver'], 
                    $b['paymentProof'], $b['status'], $b['seatNumbers'], $b['ktmProof'], 
                    $downPaymentAmount, $b['type'], $seatCapacity, $b['priceType'], 
                    $b['packageType'], $b['routeName']
                );
                $stmt->execute();
            }
        }

        // 2. Fleet
        $conn->query("DELETE FROM fleet");
        if (!empty($input['fleet'])) {
            $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($input['fleet'] as $f) {
                $stmt->bind_param("dssiss", $f['id'], $f['name'], $f['plate'], $f['capacity'], $f['status'], $f['icon']);
                $stmt->execute();
            }
        }

        // 3. Drivers
        $conn->query("DELETE FROM drivers");
        if (!empty($input['drivers'])) {
            $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['drivers'] as $d) {
                $licenseType = isset($d['licenseType']) ? $d['licenseType'] : '';
                $stmt->bind_param("dssss", $d['id'], $d['name'], $d['phone'], $d['status'], $licenseType);
                $stmt->execute();
            }
        }

        // 4. Trips
        $conn->query("DELETE FROM trips");
        if (!empty($input['trips'])) {
            $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, departureTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($input['trips'] as $t) {
                $routeConfig = json_encode($t['routeConfig']);
                $fleet = json_encode($t['fleet']);
                $driver = json_encode($t['driver']);
                $passengers = json_encode($t['passengers']);
                $departureTime = isset($t['departureTime']) ? $t['departureTime'] : null;
                
                $stmt->bind_param("dssssss", $t['id'], $routeConfig, $fleet, $driver, $passengers, $t['status'], $departureTime);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data synced to database']);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Sync failed: ' . $e->getMessage()]);
    }
}
?>
