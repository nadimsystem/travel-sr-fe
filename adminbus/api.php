<?php
// FILE: api.php for adminbus
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'base.php';
session_start();

// Error Handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'bus_error.log');

function jsonResponse($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit;
}

// DB Connection
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Router
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$input = json_decode(file_get_contents('php://input'), true);
if ($input) {
    if (isset($input['action'])) $action = $input['action'];
    // Merge input into $_POST for legacy compat if needed, but better use $input
}

// --- MODULES ---
include 'api_modules/crud.php';
include 'api_modules/bookings.php';

// Fallback
if ($action) {
    jsonResponse('error', 'Invalid Action: ' . $action);
} else {
    echo json_encode(['status' => 'ready', 'message' => 'AdminBus API Ready']);
}
?>
