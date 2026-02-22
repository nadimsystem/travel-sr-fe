<?php
// fix_payroll_schema.php
// Place this file in the same directory as base.php (e.g., display-v12)

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain');

echo "Initializing...\n";

if (file_exists('base.php')) {
    require_once 'base.php';
    echo "Loaded base.php.\n";
} else {
    die("Error: base.php not found in current directory.\n");
}

// Check if connection exists, if not try to create it
if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "Database connection not found in base.php. Attempting to connect using variables...\n";
    
    if (!isset($host) || !isset($user) || !isset($pass) || !isset($db)) {
        die("Error: Database configuration variables ($host, $user, $pass, $db) are missing.\n");
    }

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    echo "Connected successfully.\n";
} else {
    echo "Using existing database connection.\n";
}

echo "Starting Database Schema Update for Payroll Feature...\n\n";

$columnsToAdd = [
    'batchNumber' => "INT(11) DEFAULT 1 AFTER time",
    'uang_jalan' => "DECIMAL(15,2) DEFAULT 0.00 AFTER unitNumber",
    'uang_bensin' => "DECIMAL(15,2) DEFAULT 0.00 AFTER uang_jalan",
    'uang_jalan_status' => "ENUM('Pending', 'Given') DEFAULT 'Pending' AFTER uang_bensin",
    'payroll_method' => "VARCHAR(20) NULL AFTER uang_jalan_status",
    'payroll_date' => "DATE NULL AFTER payroll_method",
    'payroll_proof_image' => "VARCHAR(255) NULL AFTER payroll_date"
];

foreach ($columnsToAdd as $colName => $colDef) {
    // Check if column exists
    $checkSql = "SHOW COLUMNS FROM trips LIKE '$colName'";
    $checkResult = $conn->query($checkSql);

    if (!$checkResult) {
        echo "[ERROR] Failed to check column '$colName': " . $conn->error . "\n";
        continue;
    }

    if ($checkResult->num_rows > 0) {
        echo "[SKIP] Column '$colName' already exists.\n";
    } else {
        // Add column
        $alterSql = "ALTER TABLE trips ADD COLUMN $colName $colDef";
        if ($conn->query($alterSql)) {
            echo "[SUCCESS] Added column '$colName'.\n";
        } else {
            echo "[ERROR] Failed to add column '$colName': " . $conn->error . "\n";
        }
    }
}

echo "\nUpdate Complete.";
?>
