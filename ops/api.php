<?php
// FILE: api.php
// Display v12 - Modular Backend
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// include 'base.php';
// NEW INDEPENDENT CONFIGURATION
if (file_exists('db_config.php')) {
    include 'db_config.php';
} else {
    // Fallback or Error
    die(json_encode(['status' => 'error', 'message' => 'Database configuration (db_config.php) not found in ops folder']));
}

session_start();

ini_set('display_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', 'php_error.log');
ini_set('memory_limit', '256M'); 
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
error_reporting(E_ALL);

function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
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

// Connection is now handled in db_config.php, but if we need to ensure $conn exists:
if (!isset($conn)) {
    // This should have been handled by db_config.php, but double check
    die(json_encode(['status' => 'error', 'message' => 'Database connection variable not set']));
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ---------------------------------------------------------
// GET METHOD
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    include 'api_modules/auth.php'; 
    include 'api_modules/bookings.php';
    include 'api_modules/reports.php';
    include 'api_modules/finance.php';
    include 'api_modules/packages.php';
    include 'api_modules/master_data.php'; 
    include 'api_modules/cancellation.php';
    include 'api_modules/proofs.php';
    include 'api_modules/trip_history.php';

    // Default Fallback (Get Initial Data)
    include 'api_modules/init_data.php';
}

// ---------------------------------------------------------
// POST METHOD
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = isset($input['action']) ? $input['action'] : (isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : ''));
    
    include 'api_modules/auth.php';
    include 'api_modules/bookings.php';
    include 'api_modules/schedules.php';
    include 'api_modules/master_data.php';
    include 'api_modules/finance.php';
    include 'api_modules/packages.php';
    include 'api_modules/cancellation.php';
    include 'api_modules/proofs.php';
    include 'api_modules/trip_history.php';

    echo json_encode(['status'=>'error', 'message'=>'Invalid Action', 'action_received'=>$action]);
    exit;
}

// Helper Functions
function saveBase64Image($base64String, $filenamePrefix) {
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
        $data = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif
        
        if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
            return ''; 
        }
        
        $data = base64_decode($data);
        if ($data === false) {
            return ''; 
        }
        
        $filename = $filenamePrefix . '.' . $type;
        $path = 'buktibayar/' . $filename;
        
        if (!file_exists('buktibayar')) {
            if (!mkdir('buktibayar', 0777, true)) {
                file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Failed to create directory buktibayar\n", FILE_APPEND);
                return '';
            }
        }
        
        if (file_put_contents($path, $data) !== false) {
            return $path;
        } else {
            file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Failed to write file: $path\n", FILE_APPEND);
        }
    } else {
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " Invalid Base64 String format\n", FILE_APPEND);
    }
    return '';
}
?>