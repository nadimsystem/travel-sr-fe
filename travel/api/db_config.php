<?php
// Database Configuration for Ops/Keuangan Module
// Independent configuration not relying on external folders

// Detect Environment
$isLocal = false;
if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    $isLocal = true;
}

if ($isLocal) {
    // LOCALHOST (Kenyamanan Developer)
    $host = 'localhost';
    $user = 'root';      
    $pass = '';          
    $db   = 'sutanraya_v11'; 
} else {
    // PRODUCTION (Hosting)
    $host = 'localhost';
    $user = 'sutanray_admin2';      
    $pass = 'adminpass1998';        
    $db   = 'sutanray_v11'; 
} 

// --- KONFIGURASI DATABASE ---
// $host = 'localhost';
// $user = 'sutanray_admin2';      
// $pass = 'adminpass1998';        
// $db   = 'sutanray_v11'; 



// Error reporting for connection debugging (can be turned off in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // Return JSON error if connection fails, as this file is included by API
    if (!headers_sent()) {
        header("Content-Type: application/json");
        http_response_code(500);
    }
    echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed: ' . $e->getMessage()]);
    exit;
}
?>
