// File: display-v12/update_payroll_schema.php

// Include database connection (base.php)
if (file_exists('base.php')) {
    require_once 'base.php';
} else {
    die("Error: base.php not found.");
}

// Check if $conn exists (base.php usually creates $conn or $link)
if (!isset($conn)) {
    // Some legacy files use $link or $db
    if (isset($link)) $conn = $link;
    else if (isset($db)) $conn = $db;
}
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h1>Updating Routes Schema for Payroll</h1>";

// 1. Add payroll_1_6 column
$sql1 = "SHOW COLUMNS FROM routes LIKE 'payroll_1_6'";
$result1 = mysqli_query($conn, $sql1);

if (mysqli_num_rows($result1) == 0) {
    $alter1 = "ALTER TABLE routes ADD COLUMN payroll_1_6 DECIMAL(10, 2) DEFAULT 0 AFTER price_carter";
    if (mysqli_query($conn, $alter1)) {
        echo "<p style='color:green'>[SUCCESS] Added column 'payroll_1_6' to 'routes' table.</p>";
    } else {
        echo "<p style='color:red'>[ERROR] Failed to add 'payroll_1_6': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:orange'>[INFO] Column 'payroll_1_6' already exists.</p>";
}

// 2. Add payroll_full column
$sql2 = "SHOW COLUMNS FROM routes LIKE 'payroll_full'";
$result2 = mysqli_query($conn, $sql2);

if (mysqli_num_rows($result2) == 0) {
    $alter2 = "ALTER TABLE routes ADD COLUMN payroll_full DECIMAL(10, 2) DEFAULT 0 AFTER payroll_1_6";
    if (mysqli_query($conn, $alter2)) {
        echo "<p style='color:green'>[SUCCESS] Added column 'payroll_full' to 'routes' table.</p>";
    } else {
        echo "<p style='color:red'>[ERROR] Failed to add 'payroll_full': " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:orange'>[INFO] Column 'payroll_full' already exists.</p>";
}

// 3. Update existing routes with default values (75000 & 100000)
// Only update if they are currently 0 (to avoid overwriting custom edits if run again)
$sql_update = "UPDATE routes SET payroll_1_6 = 75000, payroll_full = 100000 WHERE payroll_1_6 = 0 AND payroll_full = 0";

if (mysqli_query($conn, $sql_update)) {
    $affected = mysqli_affected_rows($conn);
    if ($affected > 0) {
        echo "<p style='color:green'>[SUCCESS] Updated $affected routes with default payroll values (75.000 / 100.000).</p>";
    } else {
        echo "<p style='color:orange'>[INFO] No routes needed updating (values might already be set).</p>";
    }
} else {
    echo "<p style='color:red'>[ERROR] Failed to update route values: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<p>Done.</p>";
?>
