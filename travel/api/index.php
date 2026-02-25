<?php
// FILE: api/index.php
// Travel App Backend - Lightweight & Fast

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Disable Error Display in Output (Log files instead)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Database Connection
require_once 'db_config.php';

// Helper: JSON Response
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Rate Limiting (Simple File-Based)
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = sys_get_temp_dir() . '/rate_limit_' . md5($ip);
$limit = 100; // Requests per minute
$window = 60; 

$currentData = @json_decode(file_get_contents($rateLimitFile), true);
$now = time();

if ($currentData && $now - $currentData['start_time'] < $window) {
    if ($currentData['count'] > $limit) {
        http_response_code(429);
        echo json_encode(['status' => 'error', 'message' => 'Too Many Requests. Please try again later.']);
        exit;
    }
    $currentData['count']++;
} else {
    $currentData = ['start_time' => $now, 'count' => 1];
}
file_put_contents($rateLimitFile, json_encode($currentData));

// Security Helper: Recursive Sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    // Remove null bytes
    $data = str_replace(chr(0), '', $data); 
    // Strip tags and trim
    return trim(strip_tags($data));
}

// Security Helper: WAF (Basic Pattern Blocking)
function firewallCheck($data) {
    if (is_array($data)) {
        foreach ($data as $value) {
            firewallCheck($value);
        }
        return;
    }
    
    // Block Suspicious SQL/Script Patterns
    $patterns = [
        '/union\s+select/i',
        '/information_schema/i',
        '/<script>/i',
        '/javascript:/i',
        '/onload=/i',
        '/eval\(/i',
        '/base64_decode/i',
        '/system\(/i',
        '/exec\(/i',
        '/shell_exec/i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $data)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Security Violation: Suspicious input detected.']);
            exit;
        }
    }
}

// Apply Security to GET and POST (JSON)
$_GET = sanitizeInput($_GET);
firewallCheck($_GET);

$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    // Decode, Sanitize, Check, Re-encode (to pass to functions expecting JSON flow)
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $jsonData = sanitizeInput($jsonData);
        firewallCheck($jsonData);
        // We don't overwrite php://input, but we will pass $jsonData to functions if possible.
        // However, existing functions read php://input again. 
        // We should overload the global request access or pass data directly.
        // For minimal refactor, we will store sanitized data in a global or static var, 
        // OR simply trust that the logic below will decode specific fields.
        // Ideally, we refactor createBooking to take an argument, but let's stick to the current flow
        // effectively, we just validated the input. If createBooking reads php://input again, 
        // it reads the raw unsafe data. THIS IS A PROBLEM.
        
        // FIX: We will register a global variable $SANITIZED_INPUT
        $GLOBALS['SANITIZED_INPUT'] = $jsonData;
    }
}

// Router
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'get_routes':
            getRoutes($conn);
            break;
        case 'get_daily_booked_seats':
            getDailyBookings($conn);
            break;
        case 'get_occupied_seats':
        case 'get_booked_seats':
            getOccupiedSeats($conn);
            break;
        case 'create_booking':
            // Pass explicitly to avoid re-reading php://input
            createBooking($conn, $GLOBALS['SANITIZED_INPUT'] ?? null);
            break;
        case 'get_booking_history':
            getBookingHistory($conn);
            break;
        case 'get_bank_accounts':
            getBankAccounts($conn);
            break;
        default:
            jsonResponse(['status' => 'error', 'message' => 'Invalid Action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
}

// --- Controller Functions ---

function getOccupiedSeats($conn) {
    $routeId = isset($_GET['routeId']) ? $_GET['routeId'] : '';
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $time = isset($_GET['time']) ? $_GET['time'] : '';

    if (!$routeId || !$date || !$time) {
        jsonResponse(['status' => 'success', 'data' => new stdClass()]); 
    }

    $sql = "SELECT seatNumbers, batchNumber, validationStatus, passengerName FROM bookings 
            WHERE routeId = ? AND date = ? AND time = ? AND status != 'Cancelled' AND status != 'Antrian' AND status != 'Pending'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $routeId, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $bn = (int)($row['batchNumber'] ?: 1);
        if (!isset($batches[$bn])) {
            $batches[$bn] = [];
        }
        
        $seatsStr = $row['seatNumbers'];
        if ($seatsStr) {
            $parts = array_map('trim', explode(',', $seatsStr));
            foreach ($parts as $p) {
                if ($p !== '') {
                    $batches[$bn][] = $p;
                }
            }
        }
    }
    
    jsonResponse(['status' => 'success', 'data' => empty($batches) ? new stdClass() : $batches]);
}

function getRoutes($conn) {
    $routes = fetchRoutesData($conn);
    // Return both for compatibility
    jsonResponse(['status' => 'success', 'data' => $routes, 'routes' => $routes]);
}

function getBankAccounts($conn) {
    $sql = "SELECT id, route_id, bank_name, account_number, account_holder, sort_order FROM route_bank_accounts ORDER BY route_id, sort_order ASC";
    $result = $conn->query($sql);
    
    $accounts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }
    }
    
    // Provide a fallback if database fails or is empty
    if (empty($accounts)) {
         $accounts = [
             ['id' => 'fallback_1', 'route_id' => 'ALL', 'bank_name' => 'BCA PADANG', 'account_number' => '7425888781', 'account_holder' => 'Sutan Raya', 'sort_order' => 1]
         ];
    }
    
    jsonResponse(['status' => 'success', 'data' => $accounts]);
}

function getDailyBookings($conn) {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Optimize Query: Select ONLY needed columns for the seat map.
    // We need: seatNumbers, batchNumber, time, routeId, status, validationStatus, passengerName
    // Added: physicalRouteId (for transfer logic)
    
    $sql = "SELECT 
                id, 
                routeId, 
                physicalRouteId,
                time, 
                batchNumber, 
                seatNumbers, 
                status, 
                validationStatus, 
                passengerName 
            FROM bookings 
            WHERE date = ? AND status != 'Cancelled' AND status != 'Pending'
            ORDER BY routeId, time, batchNumber ASC, id ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    // Fetch Routes as well since frontend expects them bundled
    $routes = fetchRoutesData($conn);
    
    jsonResponse(['status' => 'success', 'data' => $bookings, 'routes' => $routes]);
}

function fetchRoutesData($conn) {
    $sql = "SELECT id, origin, destination, schedules, price_umum, price_pelajar FROM routes ORDER BY id ASC";
    $result = $conn->query($sql);
    
    $routes = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['schedules'] = json_decode($row['schedules']);
            $routes[] = $row;
        }
    }
    return $routes;
}

function createBooking($conn, $sanitizedData = null) {
    // 1. Get Input
    // Use sanitized data if available, otherwise fallback (should not happen with new flow)
    $input = $sanitizedData ? $sanitizedData : json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['data'])) {
        jsonResponse(['status' => 'error', 'message' => 'No data provided'], 400);
    }
    $b = $input['data'];

    // 2. Validate Essential
    if (empty($b['passengerName']) || empty($b['routeId']) || empty($b['date'])) {
        jsonResponse(['status' => 'error', 'message' => 'Lengkapi data wajib'], 400);
    }

    // 3. Prepare Defaults
    $id = $b['id'];
    if (empty($id)) $id = time() . rand(100,999);
    
    $status = 'Antrian';
    $validationStatus = isset($b['validationStatus']) ? $b['validationStatus'] : 'Menunggu Validasi';
    $paymentStatus = isset($b['paymentStatus']) ? $b['paymentStatus'] : 'Menunggu Validasi';
    $seatNumbers = isset($b['seatNumbers']) ? $b['seatNumbers'] : 'Pending';
    $selectedSeats = isset($b['selectedSeats']) ? json_encode($b['selectedSeats']) : json_encode([]);
    
    $date = $b['date'];
    $time = $b['time'];
    $routeId = $b['routeId'];
    $passengerName = $b['passengerName'];
    $passengerPhone = $b['passengerPhone'];
    $passengerType = isset($b['passengerType']) ? $b['passengerType'] : 'Umum';
    $seatCount = intval($b['seatCount']);
    $totalPrice = floatval($b['totalPrice']);
    $paymentMethod = isset($b['paymentMethod']) ? $b['paymentMethod'] : 'Transfer';
    
    $pickup = isset($b['pickupAddress']) ? $b['pickupAddress'] : '';
    $dropoff = isset($b['dropoffAddress']) ? $b['dropoffAddress'] : '';
    $bookingNote = isset($b['bookingNote']) ? $b['bookingNote'] : '';
    
    $routeObj = getRouteById($conn, $routeId);
    $routeName = $routeObj ? "{$routeObj['origin']} - {$routeObj['destination']}" : '';
    $serviceType = 'Travel';
    $inputDate = date('Y-m-d H:i:s');
    
    // Payment & KTM Proof Handling (Base64)
    $paymentProof = $b['paymentProof'] ?? '';
    if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
        $paymentProof = saveBase64Image($paymentProof, 'proof_' . $id);
    }
    
    $ktmProof = $b['ktmProof'] ?? '';
    if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
        $ktmProof = saveBase64Image($ktmProof, 'ktm_' . $id);
    }

    $transferSentDate = isset($b['transferSentDate']) ? $b['transferSentDate'] : null;
    $destinationAccount = isset($b['destinationAccount']) ? $b['destinationAccount'] : null;
    $batchNumber = isset($b['batchNumber']) ? intval($b['batchNumber']) : 1;

    // 4. Insert
    // Columns: id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, seatCount, selectedSeats, totalPrice, paymentStatus, validationStatus, status, seatNumbers, routeName, pickupAddress, dropoffAddress, input_date, paymentMethod, paymentProof, ktmProof, transferSentDate, destinationAccount, bookingNote, batchNumber
    
    $sql = "INSERT INTO bookings (
        id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType,
        seatCount, selectedSeats, totalPrice, paymentStatus, validationStatus, status, 
        seatNumbers, routeName, pickupAddress, dropoffAddress, input_date,
        paymentMethod, paymentProof, ktmProof, transferSentDate, destinationAccount, bookingNote, batchNumber
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if(!$stmt) {
        jsonResponse(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error], 500);
    }
    
    $stmt->bind_param("ssssssssissssssssssssssssi", 
        $id, $serviceType, $routeId, $date, $time, $passengerName, $passengerPhone, $passengerType,
        $seatCount, $selectedSeats, $totalPrice, $paymentStatus, $validationStatus, $status,
        $seatNumbers, $routeName, $pickup, $dropoff, $inputDate,
        $paymentMethod, $paymentProof, $ktmProof, $transferSentDate, $destinationAccount, $bookingNote, $batchNumber
    );

    if ($stmt->execute()) {
        jsonResponse(['status' => 'success', 'bookingId' => $id]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'DB Error: ' . $stmt->error], 500);
    }
}

function saveBase64Image($base64_string, $output_file_without_extension) {
    // Determine path - Dynamic check for Local (display-v12) vs Server (display-v111 or display-v11)
    $basePath = "../../";
    $folderName = "display-v12"; // Default (Local)
    
    if (is_dir($basePath . "display-v11")) {
        $folderName = "display-v11"; 
    } elseif (is_dir($basePath . "display-v111")) {
        $folderName = "display-v111"; 
    }
    
    $targetDir = $basePath . $folderName . "/uploads/";
    
    // Check if directory exists, if not try to create it
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
             error_log("Failed to create directory: " . $targetDir);
             return null; // Return null on failure
        }
    }

    // Split the base64 string
    $splited = explode(',', substr($base64_string, 5), 2);
    
    if (count($splited) != 2) return null;

    $mime = $splited[0];
    $data = $splited[1];

    $mime_split_without_base64 = explode(';', $mime, 2);
    $mime_split = explode('/', $mime_split_without_base64[0], 2);
    
    $extension = 'jpg'; // Default
    if (count($mime_split) == 2) {
        $extension = $mime_split[1];
        if ($extension == 'jpeg') $extension = 'jpg';
        if ($extension == 'png') $extension = 'png';
    }

    $output_file_with_extension = $output_file_without_extension . '.' . $extension;
    $fullPath = $targetDir . $output_file_with_extension;

    if(file_put_contents($fullPath, base64_decode($data))) {
         return 'uploads/' . $output_file_with_extension; // Return relative path for DB
    } else {
        error_log("Failed to write file to: " . $fullPath);
        return null;
    }
}

function getRouteById($conn, $id) {
    $stmt = $conn->prepare("SELECT origin, destination FROM routes WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function getBookingHistory($conn) {
    $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    
    // Normalize phone (remove 0 or 62 prefix for searching if needed, but for now exact match)
    // Basic formatting
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (empty($phone) || empty($name)) {
        jsonResponse(['status' => 'error', 'message' => 'Nomor HP dan Nama Penumpang harus diisi'], 400);
    }

    // 1. Verify if a booking exists with this Phone AND Name (Case insensitive for name)
    // We check if there is AT LEAST ONE booking with this pairing.
    $checkSql = "SELECT id FROM bookings WHERE passengerPhone = ? AND LOWER(passengerName) = LOWER(?) LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $phone, $name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        jsonResponse(['status' => 'error', 'message' => 'Data tidak ditemukan. Pastikan Nama dan Nomor HP sesuai dengan booking sebelumnya.'], 404);
    }
    
    // 2. If verified, fetch ALL bookings for this Phone Number
    // Ordered by newest first
    $sql = "SELECT 
                id, routeName, date, time, status, seatNumbers, totalPrice, 
                pickupAddress, dropoffAddress, paymentStatus, paymentProof, ktmProof 
            FROM bookings 
            WHERE passengerPhone = ? 
            ORDER BY input_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    jsonResponse(['status' => 'success', 'data' => $history]);
}
?>
