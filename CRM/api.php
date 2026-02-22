<?php
// CRM Backend API
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Database Config (Matches display-v11/api.php)
// $host = 'localhost';
// $user = 'root';      
// $pass = '';          
// $db   = 'sutanraya_v11'; 

include '../display-v11/base.php';

// Try Local first
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Fallback or Error
    die(json_encode(['status' => 'error', 'message' => 'Connection Failed: ' . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_dashboard_stats') {
    // 1. Total Customers
    $res = $conn->query("SELECT COUNT(DISTINCT passengerPhone) as count FROM bookings WHERE status != 'Cancelled' AND passengerPhone != ''");
    $totalCustomers = $res->fetch_assoc()['count'];

    // 2. Total Revenue
    $res = $conn->query("SELECT SUM(totalPrice) as total FROM bookings WHERE status != 'Cancelled'");
    $totalRevenue = $res->fetch_assoc()['total'];

    // 3. New Customers This Month
    $thisMonth = date('Y-m');
    $res = $conn->query("
        SELECT COUNT(*) as count FROM (
            SELECT passengerPhone, MIN(date) as firstTrip 
            FROM bookings 
            WHERE status != 'Cancelled' AND passengerPhone != '' 
            GROUP BY passengerPhone 
            HAVING DATE_FORMAT(firstTrip, '%Y-%m') = '$thisMonth'
        ) as new_cust
    ");
    $newCustomers = $res->fetch_assoc()['count'];

    // 4. Repeat Rate (Customers with > 1 trip)
    $res = $conn->query("
        SELECT COUNT(*) as count FROM (
            SELECT passengerPhone 
            FROM bookings 
            WHERE status != 'Cancelled' AND passengerPhone != '' 
            GROUP BY passengerPhone 
            HAVING COUNT(id) > 1
        ) as repeat_cust
    ");
    $repeatCustomers = $res->fetch_assoc()['count'];

    echo json_encode([
        'totalCustomers' => (int)$totalCustomers,
        'totalRevenue' => (double)$totalRevenue,
        'newCustomers' => (int)$newCustomers,
        'repeatRate' => $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0
    ]);
    exit;
}

if ($action === 'get_contacts') {
    $sql = "SELECT passengerPhone as phone, 
                   MAX(passengerName) as name, 
                   COUNT(id) as totalTrips, 
                   SUM(totalPrice) as totalRevenue, 
                   MAX(date) as lastTrip,
                   MAX(id) as lastBookingId
            FROM bookings 
            WHERE status != 'Cancelled' AND passengerPhone != '' 
            GROUP BY passengerPhone 
            ORDER BY lastTrip DESC";
    
    $result = $conn->query($sql);
    $contacts = [];
    while($row = $result->fetch_assoc()) {
        $contacts[] = $row;
    }
    echo json_encode(['contacts' => $contacts]);
    exit;
}

if ($action === 'get_customer_detail') {
    $phone = $conn->real_escape_string($_GET['phone']);
    
    // History
    $sql = "SELECT id, date, time, routeName, seatCount, totalPrice, status 
            FROM bookings 
            WHERE passengerPhone = '$phone' 
            ORDER BY date DESC";
    $res = $conn->query($sql);
    $history = [];
    while($row = $res->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode(['history' => $history]);
    exit;
}

echo json_encode(['status' => 'ready']);
?>
