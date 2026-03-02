<?php
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

echo "--- Booking Stats Analysis ---\n";

// 1. Count Total Bookings
$total = $conn->query("SELECT COUNT(*) as cnt FROM bookings")->fetch_assoc()['cnt'];
echo "Total Bookings: $total\n";

// 2. Count Cancelled
$cancelled = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE status IN ('Cancelled', 'Batal')")->fetch_assoc()['cnt'];
echo "Cancelled/Batal: $cancelled\n";

// 3. Count Lunas
$lunas = $conn->query("SELECT COUNT(*) as cnt FROM bookings WHERE paymentStatus = 'Lunas'")->fetch_assoc()['cnt'];
echo "Lunas: $lunas\n";

// 4. Analysis of Outstanding
$sql = "SELECT 
            COUNT(*) as cnt, 
            SUM(totalPrice - COALESCE(downPaymentAmount,0)) as total_outstanding,
            MIN(date) as min_date,
            MAX(date) as max_date
        FROM bookings 
        WHERE (totalPrice - COALESCE(downPaymentAmount,0)) > 100 
        AND status NOT IN ('Cancelled', 'Batal') 
        AND paymentStatus != 'Lunas'";

$res = $conn->query($sql)->fetch_assoc();
echo "\n--- Outstanding Bookings ---\n";
echo "Count: " . $res['cnt'] . "\n";
echo "Total Amount: " . number_format($res['total_outstanding']) . "\n";
echo "Date Range: " . $res['min_date'] . " to " . $res['max_date'] . "\n";

// 5. Check for weird high values
$sqlHigh = "SELECT id, passengerName, totalPrice, downPaymentAmount, date FROM bookings 
            WHERE (totalPrice - COALESCE(downPaymentAmount,0)) > 1000000 
            AND status NOT IN ('Cancelled', 'Batal') 
            AND paymentStatus != 'Lunas' 
            LIMIT 5";
$resHigh = $conn->query($sqlHigh);
echo "\n--- High Value Outstanding Bookings (> 1 Juta) ---\n";
while($row = $resHigh->fetch_assoc()) {
    echo "ID: {$row['id']} | Name: {$row['passengerName']} | Total: {$row['totalPrice']} | Date: {$row['date']}\n";
}

// 6. Check for duplicates (same passenger, date, time, route)
$sqlDup = "SELECT date, time, routeId, passengerName, COUNT(*) as cnt 
           FROM bookings 
           WHERE status NOT IN ('Cancelled', 'Batal')
           GROUP BY date, time, routeId, passengerName
           HAVING cnt > 1
           LIMIT 5";
$resDup = $conn->query($sqlDup);
echo "\n--- Potential Duplicates ---\n";
while($row = $resDup->fetch_assoc()) {
    echo "Date: {$row['date']} | Time: {$row['time']} | Name: {$row['passengerName']} | Count: {$row['cnt']}\n";
}

?>
