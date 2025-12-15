<?php
include 'base.php';

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>Reverting Schema (Rollback Multi-Fleet)...</h2>";

// 1. Revert schedule_defaults
// Drop Index unique_schedule_unit
$checkIndex = $conn->query("SHOW INDEX FROM schedule_defaults WHERE Key_name = 'unique_schedule_unit'");
if ($checkIndex->num_rows > 0) {
    if ($conn->query("ALTER TABLE schedule_defaults DROP INDEX unique_schedule_unit")) {
        echo "✅ Dropped index 'unique_schedule_unit'.<br>";
    } else {
        echo "❌ Error dropping index: " . $conn->error . "<br>";
    }
}

// Drop Column unitNumber
$checkCol = $conn->query("SHOW COLUMNS FROM schedule_defaults LIKE 'unitNumber'");
if ($checkCol->num_rows > 0) {
    if ($conn->query("ALTER TABLE schedule_defaults DROP COLUMN unitNumber")) {
        echo "✅ Dropped column 'unitNumber' from schedule_defaults.<br>";
    } else {
        echo "❌ Error dropping column: " . $conn->error . "<br>";
    }
}

// Restore Old Index unique_schedule (routeId, time)
// Force check again
$checkOldIndex = $conn->query("SHOW INDEX FROM schedule_defaults WHERE Key_name = 'unique_schedule'");
if ($checkOldIndex->num_rows == 0) {
    // Check if duplicates exist first! If duplicates exist, ALTER will fail.
    // We should delete newer duplicates? Or older?
    // Let's delete all but the latest for each routeId/time pair to be safe.
    
    $dedupSql = "DELETE t1 FROM schedule_defaults t1
                 INNER JOIN schedule_defaults t2 
                 WHERE t1.id < t2.id AND t1.routeId = t2.routeId AND t1.time = t2.time";
                 
    if ($conn->query($dedupSql)) {
        echo "✅ Deduped schedule_defaults.<br>";
    } else {
        echo "❌ Error deduping: " . $conn->error . "<br>";
    }

    if ($conn->query("ALTER TABLE schedule_defaults ADD UNIQUE KEY unique_schedule (routeId, time)")) {
        echo "✅ Restored index 'unique_schedule' (routeId, time).<br>";
    } else {
        echo "❌ Error restoring index: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Index 'unique_schedule' already exists.<br>";
}

// 2. Revert trips table
// Drop Column unitNumber
$checkTripsCol = $conn->query("SHOW COLUMNS FROM trips LIKE 'unitNumber'");
if ($checkTripsCol->num_rows > 0) {
    if ($conn->query("ALTER TABLE trips DROP COLUMN unitNumber")) {
        echo "✅ Dropped column 'unitNumber' from trips.<br>";
    } else {
        echo "❌ Error dropping column from trips: " . $conn->error . "<br>";
    }
}

// OPTIONAL: Drop date/time from trips? 
// The user said "rollback without EXTRA HOURS". date/time query optimization is probably fine to keep?
// But strictly speaking, reverting to "before" state means removing them if they cause issues.
// But `save_trip` code I reverted REMOVED `unitNumber` but kept `date` and `time` in the INSERT?
// Let me check my reverted api.php.
// checking api.php...
// I reverted `save_trip` to:
// $stmt = $conn->prepare("UPDATE trips SET routeConfig=?, fleet=?, driver=?, passengers=?, status=?, date=?, time=?, note=? WHERE id=?");
// So `api.php` STILL USES `date` and `time` columns!
// Therefore, I MUST NOT drop `date` and `time` columns from `trips`.
echo "ℹ️ Keeping 'date' and 'time' columns in trips table as they are used by the code.<br>";

echo "<h3>Rollback Complete.</h3>";
?>
