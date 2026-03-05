<?php
// FILE: api/index.php
// Travel App Backend — Hardened Security v2

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Database Connection
require_once 'db_config.php';

// ─── Helper: JSON Response ────────────────────────────────────────────────────
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// ─── Helper: Sanitize (recursive) ────────────────────────────────────────────
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = str_replace(chr(0), '', $data);
    return trim(strip_tags($data));
}

// ─── Helper: WAF pattern check ───────────────────────────────────────────────
function firewallCheck($data) {
    if (is_array($data)) {
        foreach ($data as $value) { firewallCheck($value); }
        return;
    }
    $patterns = [
        '/union\s+select/i', '/information_schema/i',
        '/<script>/i', '/javascript:/i', '/onload=/i',
        '/eval\(/i', '/base64_decode/i',
        '/system\(/i', '/exec\(/i', '/shell_exec/i',
        '/passthru\(/i', '/popen\(/i',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $data)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Security Violation: Suspicious input detected.']);
            exit;
        }
    }
}

// ─── Rate Limiter (generic, file-based, per IP) ───────────────────────────────
function checkRateLimit($key, $limit, $window = 60) {
    $ip  = $_SERVER['REMOTE_ADDR'];
    
    // Whitelist localhost during development
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return;
    }

    $file = sys_get_temp_dir() . '/rl_' . md5($ip . '_' . $key);
    $data = @json_decode(file_get_contents($file), true);
    $now  = time();

    if ($data && ($now - $data['t']) < $window) {
        if ($data['c'] >= $limit) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Coba lagi sebentar.']);
            exit;
        }
        $data['c']++;
    } else {
        $data = ['t' => $now, 'c' => 1];
    }
    file_put_contents($file, json_encode($data));
}

// Apply to all requests: max 120/min per IP
checkRateLimit('global', 120);

// ─── Security applied to GET params ──────────────────────────────────────────
$_GET = sanitizeInput($_GET);
firewallCheck($_GET);

// ─── Decode & sanitize POST body ─────────────────────────────────────────────
$rawInput = file_get_contents('php://input');
$GLOBALS['SANITIZED_INPUT'] = null;
if (!empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $jsonData = sanitizeInput($jsonData);
        firewallCheck($jsonData);
        $GLOBALS['SANITIZED_INPUT'] = $jsonData;
    }
}

// ─── Router ───────────────────────────────────────────────────────────────────
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
            checkRateLimit('create_booking', 5);          // max 5 bookings/min per IP
            createBooking($conn, $GLOBALS['SANITIZED_INPUT'] ?? null);
            break;
        case 'get_booking_history':
            checkRateLimit('get_booking_history', 10);    // max 10 lookups/min per IP
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

// ─── Controller Functions ─────────────────────────────────────────────────────

function getOccupiedSeats($conn) {
    $routeId = isset($_GET['routeId']) ? $_GET['routeId'] : '';
    $date    = isset($_GET['date'])    ? $_GET['date']    : '';
    $time    = isset($_GET['time'])    ? $_GET['time']    : '';

    if (!$routeId || !$date || !$time) {
        jsonResponse(['status' => 'success', 'data' => new stdClass()]);
    }

    $sql  = "SELECT seatNumbers, batchNumber, validationStatus, passengerName FROM bookings
             WHERE routeId = ? AND date = ? AND time = ?
               AND status != 'Cancelled' AND status != 'Antrian' AND status != 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $routeId, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $bn = (int)($row['batchNumber'] ?: 1);
        if (!isset($batches[$bn])) $batches[$bn] = [];
        $seatsStr = $row['seatNumbers'];
        if ($seatsStr) {
            foreach (array_map('trim', explode(',', $seatsStr)) as $p) {
                if ($p !== '') $batches[$bn][] = $p;
            }
        }
    }
    jsonResponse(['status' => 'success', 'data' => empty($batches) ? new stdClass() : $batches]);
}

function getRoutes($conn) {
    $routes = fetchRoutesData($conn);
    jsonResponse(['status' => 'success', 'data' => $routes, 'routes' => $routes]);
}

function getBankAccounts($conn) {
    $sql    = "SELECT id, route_id, bank_name, account_number, account_holder, sort_order
               FROM route_bank_accounts ORDER BY route_id, sort_order ASC";
    $result = $conn->query($sql);
    $accounts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) $accounts[] = $row;
    }
    if (empty($accounts)) {
        $accounts = [
            ['id' => 'fallback_1', 'route_id' => 'ALL', 'bank_name' => 'BCA PADANG',
             'account_number' => '7425888781', 'account_holder' => 'Sutan Raya', 'sort_order' => 1]
        ];
    }
    jsonResponse(['status' => 'success', 'data' => $accounts]);
}

function getDailyBookings($conn) {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $sql  = "SELECT id, routeId, physicalRouteId, time, batchNumber,
                    seatNumbers, status, validationStatus, passengerName
             FROM bookings
             WHERE date = ? AND status != 'Cancelled' AND status != 'Pending'
             ORDER BY routeId, time, batchNumber ASC, id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) $bookings[] = $row;
    $routes = fetchRoutesData($conn);
    jsonResponse(['status' => 'success', 'data' => $bookings, 'routes' => $routes]);
}

function fetchRoutesData($conn) {
    $sql    = "SELECT id, origin, destination, schedules, price_umum, price_pelajar
               FROM routes ORDER BY id ASC";
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

// ─── saveBase64Image — validates magic bytes (JPEG/PNG only) ─────────────────
function saveBase64Image($base64_string, $output_file_without_extension) {
    // Only allow image/jpeg and image/png MIME headers
    $allowedMimes = ['image/jpeg', 'image/png'];
    if (!preg_match('/^data:(image\/(?:jpeg|png));base64,/', $base64_string, $matches)) {
        error_log("saveBase64Image: rejected non-jpeg/png MIME header");
        return null;
    }
    $mime    = $matches[1];
    $b64Data = substr($base64_string, strpos($base64_string, ',') + 1);
    $rawData = base64_decode($b64Data, true);
    if ($rawData === false) { error_log("saveBase64Image: base64_decode failed"); return null; }

    // Validate magic bytes
    $sig = substr($rawData, 0, 4);
    $isJpeg = (substr($sig, 0, 2) === "\xFF\xD8");
    $isPng  = ($sig === "\x89PNG");
    if (!$isJpeg && !$isPng) {
        error_log("saveBase64Image: magic bytes mismatch — possible file spoofing attempt");
        return null;
    }

    // Force correct extension based on magic bytes (never trust MIME header alone)
    $extension = $isJpeg ? 'jpg' : 'png';

    // Build target directory
    $basePath   = "../../";
    $folderName = "display-v12";
    if (is_dir($basePath . "display-v11"))       $folderName = "display-v11";
    elseif (is_dir($basePath . "display-v111"))  $folderName = "display-v111";
    $targetDir = $basePath . $folderName . "/uploads/";

    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0755, true)) {
            error_log("saveBase64Image: failed to create dir $targetDir");
            return null;
        }
    }

    // Generate safe filename (never use client-supplied name parts for extension)
    $safeBasename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $output_file_without_extension);
    $filename     = $safeBasename . '.' . $extension;
    $fullPath     = $targetDir . $filename;

    if (file_put_contents($fullPath, $rawData) !== false) {
        return 'uploads/' . $filename;
    }
    error_log("saveBase64Image: failed to write $fullPath");
    return null;
}

// ─── createBooking — hardened ─────────────────────────────────────────────────
function createBooking($conn, $sanitizedData = null) {
    $input = $sanitizedData ?: json_decode(file_get_contents('php://input'), true);

    if (!isset($input['data'])) {
        jsonResponse(['status' => 'error', 'message' => 'No data provided'], 400);
    }
    $b = $input['data'];

    // ── 1. Required field presence ────────────────────────────────────────────
    if (empty($b['passengerName']) || empty($b['routeId']) || empty($b['date'])
        || empty($b['time']) || empty($b['passengerPhone'])) {
        jsonResponse(['status' => 'error', 'message' => 'Lengkapi data wajib (nama, rute, tanggal, jam, nomor HP)'], 400);
    }

    // ── 2. Sanitize & clamp string lengths ───────────────────────────────────
    $passengerName  = mb_substr(trim($b['passengerName']),  0, 100);
    $passengerPhone = mb_substr(trim($b['passengerPhone']), 0, 20);
    $routeId        = mb_substr(trim($b['routeId']),        0, 30);
    $date           = mb_substr(trim($b['date']),           0, 10);
    $time           = mb_substr(trim($b['time']),           0, 10);
    $pickup         = mb_substr(isset($b['pickupAddress'])  ? $b['pickupAddress']  : '', 0, 255);
    $dropoff        = mb_substr(isset($b['dropoffAddress']) ? $b['dropoffAddress'] : '', 0, 255);
    $bookingNote    = mb_substr(isset($b['bookingNote'])    ? $b['bookingNote']    : '', 0, 500);
    $passengerType  = in_array($b['passengerType'] ?? '', ['Umum', 'Mahasiswa / Pelajar'])
                        ? $b['passengerType'] : 'Umum';
    $paymentMethod  = in_array($b['paymentMethod'] ?? '', ['QRIS', 'Transfer', 'Tunai'])
                        ? $b['paymentMethod'] : 'Transfer';
    $batchNumber    = max(1, min(10, intval($b['batchNumber'] ?? 1)));

    // ── 3. Server-side validate date (must not be in the past) ────────────────
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        jsonResponse(['status' => 'error', 'message' => 'Format tanggal tidak valid.'], 400);
    }
    $today = date('Y-m-d');
    if ($date < $today) {
        jsonResponse(['status' => 'error', 'message' => 'Tanggal tidak boleh di masa lalu.'], 400);
    }
    // Max 60 days ahead
    $maxDate = date('Y-m-d', strtotime('+60 days'));
    if ($date > $maxDate) {
        jsonResponse(['status' => 'error', 'message' => 'Tanggal terlalu jauh ke depan (maksimal 60 hari).'], 400);
    }

    // ── 4. Validate routeId exists in DB & get price ──────────────────────────
    $routeObj = getRouteById($conn, $routeId);
    if (!$routeObj) {
        jsonResponse(['status' => 'error', 'message' => 'Rute tidak ditemukan.'], 400);
    }

    // ── 5. Validate seatCount (1–8) ───────────────────────────────────────────
    $seatNumbers = isset($b['seatNumbers']) ? mb_substr(trim($b['seatNumbers']), 0, 100) : 'Pending';
    $seatCount   = 0;
    if ($seatNumbers !== 'Pending' && !empty($seatNumbers)) {
        $seatArr   = array_filter(array_map('trim', explode(',', $seatNumbers)));
        $seatCount = count($seatArr);
    } else {
        $seatCount = max(1, min(8, intval($b['seatCount'] ?? 1)));
    }
    if ($seatCount < 1 || $seatCount > 8) {
        jsonResponse(['status' => 'error', 'message' => 'Jumlah kursi harus antara 1 dan 8.'], 400);
    }

    // ── 6. Server-side price calculation (NEVER trust client totalPrice) ──────
    $priceUmum    = (int)($routeObj['price_umum']    ?? 0);
    $pricePelajar = (int)($routeObj['price_pelajar'] ?? 0);

    // Fallback prices table (mirrors frontend)
    $fallbackPrices = [
        'BKT-PDG'   => ['umum' => 120000, 'pelajar' => 100000],
        'BKT-PDG-2' => ['umum' => 250000, 'pelajar' => 250000],
        'PDG-BKT'   => ['umum' => 120000, 'pelajar' => 100000],
        'PDG-BKT-2' => ['umum' => 120000, 'pelajar' => 100000],
        'PDG-PYK'   => ['umum' => 150000, 'pelajar' => 130000],
        'PDG-PYK-2' => ['umum' => 250000, 'pelajar' => 250000],
        'PYK-PDG'   => ['umum' => 150000, 'pelajar' => 130000],
        'PYK-PDG-2' => ['umum' => 250000, 'pelajar' => 250000],
        'BKT-PKU'   => ['umum' => 220000, 'pelajar' => 220000],
        'PKU-BKT'   => ['umum' => 220000, 'pelajar' => 220000],
        'PDG-PKU'   => ['umum' => 260000, 'pelajar' => 240000],
        'PKU-PDG'   => ['umum' => 260000, 'pelajar' => 240000],
        'PDG-PKU-2' => ['umum' => 260000, 'pelajar' => 240000],
        'PYK-PKU'   => ['umum' => 220000, 'pelajar' => 200000],
        'PKU-PYK'   => ['umum' => 220000, 'pelajar' => 200000],
    ];

    if ($passengerType === 'Mahasiswa / Pelajar') {
        $basePrice = $pricePelajar > 0 ? $pricePelajar
                   : ($fallbackPrices[$routeId]['pelajar'] ?? 0);
        if ($basePrice === 0) {
            $umum = $priceUmum > 0 ? $priceUmum : ($fallbackPrices[$routeId]['umum'] ?? 0);
            $basePrice = max(0, $umum - 20000);
        }
    } else {
        $basePrice = $priceUmum > 0 ? $priceUmum
                   : ($fallbackPrices[$routeId]['umum'] ?? 0);
    }
    $totalPrice = $basePrice * $seatCount;

    // ── 7. Anti-duplicate booking: same phone+route+date+time within 2 minutes ─
    $twoMinsAgo = date('Y-m-d H:i:s', strtotime('-2 minutes'));
    $dupStmt = $conn->prepare(
        "SELECT id FROM bookings
         WHERE passengerPhone = ? AND routeId = ? AND date = ? AND time = ?
           AND input_date >= ?
         LIMIT 1"
    );
    $dupStmt->bind_param("sssss", $passengerPhone, $routeId, $date, $time, $twoMinsAgo);
    $dupStmt->execute();
    if ($dupStmt->get_result()->num_rows > 0) {
        jsonResponse(['status' => 'error', 'message' => 'Booking duplikat terdeteksi. Silakan tunggu sebelum memesan kembali.'], 429);
    }

    // ── 8. Hardcoded statuses — NEVER from client ─────────────────────────────
    $status           = 'Antrian';
    $validationStatus = 'Menunggu Validasi';
    $paymentStatus    = 'Menunggu Validasi';

    // ── 9. Build ID (Numeric for bigint compatibility) ───────────────────────
    $id = date('Ymd') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

    // ── 10. Handle image uploads (validated) ──────────────────────────────────
    $paymentProof = $b['paymentProof'] ?? '';
    if (!empty($paymentProof) && strpos($paymentProof, 'data:image') === 0) {
        $saved = saveBase64Image($paymentProof, 'proof_' . $id);
        if ($saved === null) {
            jsonResponse(['status' => 'error', 'message' => 'Format bukti pembayaran tidak valid. Hanya JPG/PNG yang diizinkan.'], 400);
        }
        $paymentProof = $saved;
    } else {
        $paymentProof = '';
    }

    $ktmProof = $b['ktmProof'] ?? '';
    if (!empty($ktmProof) && strpos($ktmProof, 'data:image') === 0) {
        $saved = saveBase64Image($ktmProof, 'ktm_' . $id);
        if ($saved === null) {
            jsonResponse(['status' => 'error', 'message' => 'Format foto KTM tidak valid. Hanya JPG/PNG yang diizinkan.'], 400);
        }
        $ktmProof = $saved;
    } else {
        $ktmProof = '';
    }

    $selectedSeats       = isset($b['selectedSeats']) ? json_encode($b['selectedSeats']) : json_encode([]);
    $transferSentDate    = !empty($b['transferSentDate'])   ? mb_substr($b['transferSentDate'],   0, 20)  : null;
    $destinationAccount  = !empty($b['destinationAccount']) ? mb_substr($b['destinationAccount'], 0, 50) : null;
    $routeName           = $routeObj['origin'] . ' - ' . $routeObj['destination'];
    $serviceType         = 'Travel';
    $inputDate           = date('Y-m-d H:i:s');

    // ── 11. Insert ─────────────────────────────────────────────────────────────
    $sql = "INSERT INTO bookings (
        id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType,
        seatCount, selectedSeats, totalPrice, paymentStatus, validationStatus, status,
        seatNumbers, routeName, pickupAddress, dropoffAddress, input_date,
        paymentMethod, paymentProof, ktmProof, transferSentDate, destinationAccount, bookingNote, batchNumber
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        jsonResponse(['status' => 'error', 'message' => 'Prepare failed'], 500);
    }
    $stmt->bind_param(
        "ssssssssissssssssssssssssi",
        $id, $serviceType, $routeId, $date, $time, $passengerName, $passengerPhone, $passengerType,
        $seatCount, $selectedSeats, $totalPrice, $paymentStatus, $validationStatus, $status,
        $seatNumbers, $routeName, $pickup, $dropoff, $inputDate,
        $paymentMethod, $paymentProof, $ktmProof, $transferSentDate, $destinationAccount, $bookingNote, $batchNumber
    );

    if ($stmt->execute()) {
        jsonResponse(['status' => 'success', 'bookingId' => $id]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Gagal menyimpan booking.'], 500);
    }
}

// ─── getRouteById ─────────────────────────────────────────────────────────────
function getRouteById($conn, $id) {
    $stmt = $conn->prepare(
        "SELECT origin, destination, price_umum, price_pelajar FROM routes WHERE id = ?"
    );
    $stmt->bind_param("s", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ─── getBookingHistory ────────────────────────────────────────────────────────
function getBookingHistory($conn) {
    $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
    $name  = isset($_GET['name'])  ? $_GET['name']  : '';

    // Normalize phone to digits only
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $name  = mb_substr(trim($name), 0, 100);

    if (empty($phone) || empty($name)) {
        jsonResponse(['status' => 'error', 'message' => 'Nomor HP dan Nama Penumpang harus diisi'], 400);
    }

    // Basic phone format check
    if (strlen($phone) < 8 || strlen($phone) > 15) {
        jsonResponse(['status' => 'error', 'message' => 'Format nomor HP tidak valid.'], 400);
    }

    // Verify pairing exists
    $checkStmt = $conn->prepare(
        "SELECT id FROM bookings WHERE passengerPhone = ? AND LOWER(passengerName) = LOWER(?) LIMIT 1"
    );
    $checkStmt->bind_param("ss", $phone, $name);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows === 0) {
        jsonResponse(['status' => 'error', 'message' => 'Data tidak ditemukan. Pastikan Nama dan Nomor HP sesuai dengan booking sebelumnya.'], 404);
    }

    // Return limited fields (no sensitive admin-only data)
    $sql  = "SELECT id, routeName, date, time, status, seatNumbers, totalPrice,
                    pickupAddress, paymentStatus, paymentProof, ktmProof
             FROM bookings
             WHERE passengerPhone = ?
             ORDER BY input_date DESC
             LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result  = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) $history[] = $row;

    jsonResponse(['status' => 'success', 'data' => $history]);
}
?>
