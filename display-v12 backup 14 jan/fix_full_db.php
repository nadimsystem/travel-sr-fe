<?php
// fix_full_db.php
include 'base.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper to execute commands
function execSQL($conn, $sql, $msg) {
    echo "$msg ... ";
    try {
        if ($conn->query($sql) === TRUE) {
            echo "<b style='color:green'>SUCCESS</b><br>";
        } else {
            // Check for various acceptable errors (like duplicate column/table)
            if ($conn->errno == 1050) { // Table exists
                echo "<span style='color:orange'>Skipped (Table exists)</span><br>";
            } elseif ($conn->errno == 1060) { // Column exists
                echo "<span style='color:orange'>Skipped (Column exists)</span><br>";
            } else {
                echo "<b style='color:red'>ERROR: " . $conn->error . "</b><br>";
            }
        }
    } catch (Exception $e) {
        echo "<b style='color:red'>EXCEPTION: " . $e->getMessage() . "</b><br>";
    }
}

// 1. Missing Tables (Purchasing, Broadcasts, Refunds)
$tables = [
    "CREATE TABLE IF NOT EXISTS `broadcast_queue` ( `id` int(11) NOT NULL AUTO_INCREMENT, `phone` varchar(20) DEFAULT NULL, `name` varchar(100) DEFAULT NULL, `message` text DEFAULT NULL, `status` enum('pending','processing','sent','failed') DEFAULT 'pending', `attempts` int(11) DEFAULT 0, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    "CREATE TABLE IF NOT EXISTS `purchasing_assets` ( `id` int(11) NOT NULL AUTO_INCREMENT, `code` varchar(50) DEFAULT NULL, `name` varchar(255) NOT NULL, `category` varchar(100) DEFAULT NULL, `value` decimal(15,2) DEFAULT NULL, `location` varchar(100) DEFAULT NULL, `pic` varchar(100) DEFAULT NULL, `status` varchar(50) DEFAULT 'Active', `purchase_date` date DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `usage` varchar(100) DEFAULT 'Universal', PRIMARY KEY (`id`), UNIQUE KEY `code` (`code`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    "CREATE TABLE IF NOT EXISTS `purchasing_rooms` ( `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(100) NOT NULL, `notes` text DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    // Dependent tables need to be created carefully or just structure first then constraints. 
    // To keep it simple for this script, we create tables. Foreign keys might fail if parent doesn't exist, so order matters.
    
    "CREATE TABLE IF NOT EXISTS `refunds` ( `id` int(11) NOT NULL AUTO_INCREMENT, `booking_id` bigint(20) NOT NULL, `amount` decimal(10,2) NOT NULL, `reason` varchar(255) DEFAULT NULL, `status` enum('pending','approved','rejected','processed') DEFAULT 'pending', `requested_at` datetime DEFAULT current_timestamp(), `processed_at` datetime DEFAULT NULL, PRIMARY KEY (`id`), KEY `booking_id` (`booking_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
];

echo "<h3>Creating Missing Tables</h3>";
foreach($tables as $sql) {
    execSQL($conn, $sql, "Creating table...");
}

// 2. Missing Columns
echo "<h3>Adding Missing Columns</h3>";
$columns = [
    "ALTER TABLE `packages` ADD COLUMN `receiptNumber` varchar(50) DEFAULT NULL",
    "ALTER TABLE `trips` ADD COLUMN `departureTime` varchar(50) DEFAULT NULL" // Just in case
];

foreach($columns as $sql) {
    execSQL($conn, $sql, "Altering table...");
}

echo "<br><b>Selesai!</b> Database online sekarang seharusnya sudah sinkron dengan local.";
?>
