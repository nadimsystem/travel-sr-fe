<?php
// update_schema_multibatch.php
include 'base.php';

$host = 'localhost';
$user = 'root'; // Adjust if needed
$pass = '';     // Adjust if needed
$db   = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>Starting Schema Update for Multi-Batch Support...</h2>";

// 1. Add unitNumber column to schedule_defaults if not exists
$check = $conn->query("SHOW COLUMNS FROM schedule_defaults LIKE 'unitNumber'");
if ($check->num_rows == 0) {
    if ($conn->query("ALTER TABLE schedule_defaults ADD COLUMN unitNumber INT DEFAULT 1")) {
        echo "✅ Added 'unitNumber' column to schedule_defaults.<br>";
    } else {
        echo "❌ Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Column 'unitNumber' already exists.<br>";
}

// 2. Drop old Unique Key (routeId, time)
// First check if it exists
$checkIndex = $conn->query("SHOW INDEX FROM schedule_defaults WHERE Key_name = 'unique_schedule'");
if ($checkIndex->num_rows > 0) {
    if ($conn->query("ALTER TABLE schedule_defaults DROP INDEX unique_schedule")) {
        echo "✅ Dropped old unique index 'unique_schedule'.<br>";
    } else {
        echo "❌ Error dropping index: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Index 'unique_schedule' not found (maybe already dropped).<br>";
}

// 3. Add new Unique Key (routeId, time, unitNumber)
$checkNewIndex = $conn->query("SHOW INDEX FROM schedule_defaults WHERE Key_name = 'unique_schedule_unit'");
if ($checkNewIndex->num_rows == 0) {
    if ($conn->query("ALTER TABLE schedule_defaults ADD UNIQUE KEY unique_schedule_unit (routeId, time, unitNumber)")) {
        echo "✅ Added new unique index 'unique_schedule_unit'.<br>";
    } else {
        echo "❌ Error adding new index: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Index 'unique_schedule_unit' already exists.<br>";
}

// 4. Update Trips Table - Add unitNumber column
$checkTrips = $conn->query("SHOW COLUMNS FROM trips LIKE 'unitNumber'");
if ($checkTrips->num_rows == 0) {
    if ($conn->query("ALTER TABLE trips ADD COLUMN unitNumber INT DEFAULT 1")) {
        echo "✅ Added 'unitNumber' column to trips.<br>";
    } else {
        echo "❌ Error adding column unitNumber to trips: " . $conn->error . "<br>";
    }
} else {
    echo "ℹ️ Column 'unitNumber' in trips already exists.<br>";
}

// 5. Update Trips Table - Add date and time columns (Required for new save_trip)
$checkDate = $conn->query("SHOW COLUMNS FROM trips LIKE 'date'");
if ($checkDate->num_rows == 0) {
    if ($conn->query("ALTER TABLE trips ADD COLUMN date VARCHAR(20)")) {
        echo "✅ Added 'date' column to trips.<br>";
    } else {
        echo "❌ Error adding column date to trips: " . $conn->error . "<br>";
    }
}
$checkTime = $conn->query("SHOW COLUMNS FROM trips LIKE 'time'");
if ($checkTime->num_rows == 0) {
    if ($conn->query("ALTER TABLE trips ADD COLUMN time VARCHAR(10)")) {
        echo "✅ Added 'time' column to trips.<br>";
    } else {
        echo "❌ Error adding column time to trips: " . $conn->error . "<br>";
    }
}

echo "<h3>Update Complete.</h3>";
?>
