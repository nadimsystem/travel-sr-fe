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
        } else if ($action === 'save_fleet') {
            $id = $input['id'];
            $name = $input['name'];
            $plate = $input['plate'];
            $capacity = $input['capacity'];
            $status = $input['status'];
            $icon = $input['icon'];

            $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, plate=?, capacity=?, status=?, icon=?");
            $stmt->bind_param("dssissssiss", 
                $id, $name, $plate, $capacity, $status, $icon,
                $name, $plate, $capacity, $status, $icon
            );

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Fleet saved']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to save fleet: ' . $conn->error]);
            }
        } else if ($action === 'delete_fleet') {
            $id = $input['id'];
            $stmt = $conn->prepare("DELETE FROM fleet WHERE id = ?");
            $stmt->bind_param("d", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Fleet deleted']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete fleet: ' . $conn->error]);
            }
        } else if ($action === 'save_driver') {
            $id = $input['id'];
            $name = $input['name'];
            $phone = $input['phone'];
            $status = $input['status'];
            $licenseType = isset($input['licenseType']) ? $input['licenseType'] : '';

            $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, phone=?, status=?, licenseType=?");
            $stmt->bind_param("dssssssss", 
                $id, $name, $phone, $status, $licenseType,
                $name, $phone, $status, $licenseType
            );

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Driver saved']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to save driver: ' . $conn->error]);
            }
        } else if ($action === 'delete_driver') {
            $id = $input['id'];
            $stmt = $conn->prepare("DELETE FROM drivers WHERE id = ?");
            $stmt->bind_param("d", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Driver deleted']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete driver: ' . $conn->error]);
            }
        }
        exit;
    }

    // Default Sync Logic (Only if no action)
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Validate input for sync (must have at least one known key)
    if (!$input) {
        error_log("API Error: Invalid JSON input. Raw: " . substr($rawInput, 0, 100));
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit;
    }

    if(!isset($input['bookings']) && !isset($input['fleet']) && !isset($input['drivers']) && !isset($input['trips']) && !isset($input['routes']) && !isset($input['busRoutes'])) {
        // If empty payload, do nothing but log it
        error_log("API Warning: Empty payload received");
        if(empty($input)) exit; 
    }

    $conn->begin_transaction();

    try {
        // 1. Bookings
        $conn->query("DELETE FROM bookings");
        if (!empty($input['bookings'])) {
            $stmt = $conn->prepare("INSERT INTO bookings (id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, validationStatus, paymentLocation, paymentReceiver, paymentProof, status, seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($input['bookings'] as $b) {
                if(empty($b['id'])) continue; // Skip invalid ID
                $selectedSeats = isset($b['selectedSeats']) ? json_encode($b['selectedSeats']) : '[]';
                $seatCapacity = isset($b['seatCapacity']) ? $b['seatCapacity'] : null;
                $downPaymentAmount = isset($b['downPaymentAmount']) ? $b['downPaymentAmount'] : 0;
                
                // Safe assignment for optional fields
                $passengerType = isset($b['passengerType']) ? $b['passengerType'] : 'Umum';
                $seatCount = isset($b['seatCount']) ? $b['seatCount'] : 1;
                $duration = isset($b['duration']) ? $b['duration'] : 1;
                $paymentMethod = isset($b['paymentMethod']) ? $b['paymentMethod'] : 'Cash';
                $paymentStatus = isset($b['paymentStatus']) ? $b['paymentStatus'] : 'Pending';
                $validationStatus = isset($b['validationStatus']) ? $b['validationStatus'] : 'Pending';
                $paymentLocation = isset($b['paymentLocation']) ? $b['paymentLocation'] : '';
                $paymentReceiver = isset($b['paymentReceiver']) ? $b['paymentReceiver'] : '';
                $paymentProof = isset($b['paymentProof']) ? $b['paymentProof'] : '';
                $seatNumbers = isset($b['seatNumbers']) ? $b['seatNumbers'] : '';
                $ktmProof = isset($b['ktmProof']) ? $b['ktmProof'] : '';
                $type = isset($b['type']) ? $b['type'] : '';
                $priceType = isset($b['priceType']) ? $b['priceType'] : '';
                $packageType = isset($b['packageType']) ? $b['packageType'] : '';
                $routeName = isset($b['routeName']) ? $b['routeName'] : '';
                $routeId = isset($b['routeId']) ? $b['routeId'] : '';
                $time = isset($b['time']) ? $b['time'] : '';

                // Corrected type string: 27 chars for 27 vars
                // ssssssssisidsssssssssdsisss
                $stmt->bind_param("ssssssssisidsssssssssdsisss", 
                    $b['id'], $b['serviceType'], $routeId, $b['date'], $time, 
                    $b['passengerName'], $b['passengerPhone'], $passengerType, $seatCount, 
                    $selectedSeats, $duration, $b['totalPrice'], $paymentMethod, 
                    $paymentStatus, $validationStatus, $paymentLocation, $paymentReceiver, 
                    $paymentProof, $b['status'], $seatNumbers, $ktmProof, 
                    $downPaymentAmount, $type, $seatCapacity, $priceType, 
                    $packageType, $routeName
                );
                $stmt->execute();
            }
        }

        // 2. Fleet
        $conn->query("DELETE FROM fleet");
        if (!empty($input['fleet'])) {
            $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($input['fleet'] as $f) {
                if(empty($f['id'])) continue; // Skip invalid ID
                $stmt->bind_param("sssiss", $f['id'], $f['name'], $f['plate'], $f['capacity'], $f['status'], $f['icon']);
                $stmt->execute();
            }
        }

        // 3. Drivers
        $conn->query("DELETE FROM drivers");
        if (!empty($input['drivers'])) {
            $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?)");
            foreach ($input['drivers'] as $d) {
                if(empty($d['id'])) continue; // Skip invalid ID
                $licenseType = isset($d['licenseType']) ? $d['licenseType'] : '';
                $stmt->bind_param("sssss", $d['id'], $d['name'], $d['phone'], $d['status'], $licenseType);
                $stmt->execute();
            }
        }

        // 4. Trips
        $conn->query("DELETE FROM trips");
        if (!empty($input['trips'])) {
            $stmt = $conn->prepare("INSERT INTO trips (id, routeConfig, fleet, driver, passengers, status, departureTime) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($input['trips'] as $t) {
                if(empty($t['id'])) continue; // Skip invalid ID
                $routeConfig = json_encode($t['routeConfig']);
                $fleet = json_encode($t['fleet']);
                $driver = json_encode($t['driver']);
                $passengers = json_encode($t['passengers']);
                $departureTime = isset($t['departureTime']) ? $t['departureTime'] : null;
                
                // Changed 'd' to 's' for ID
                $stmt->bind_param("sssssss", $t['id'], $routeConfig, $fleet, $driver, $passengers, $t['status'], $departureTime);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data synced to database']);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("API Sync Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Sync failed: ' . $e->getMessage()]);
    }
}
?>
