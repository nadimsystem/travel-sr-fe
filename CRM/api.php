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

include 'base.php';

// Try Local first
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Fallback or Error
    die(json_encode(['status' => 'error', 'message' => 'Connection Failed: ' . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle POST for Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if(isset($input['action']) && $input['action'] === 'login') {
        $username = $input['username'];
        $password = $input['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            echo json_encode(['status' => 'success', 'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name']
            ]]);
        } else {
            // Fallback for Admin (Safety Net)
            if ($username === 'admin' && $password === 'admin123') {
                 echo json_encode(['status' => 'success', 'user' => ['username'=>'admin', 'name'=>'Administrator']]);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
            }
        }
        exit;
    }
}

if ($action === 'get_dashboard_stats') {
    // 1. Total Customers
    $res = $conn->query("SELECT COUNT(DISTINCT passengerPhone) as count FROM bookings WHERE status != 'Cancelled' AND passengerPhone != ''");
    $totalCustomers = $res->fetch_assoc()['count'];

    // 2. Total Revenue
    $res = $conn->query("SELECT SUM(totalPrice * seatCount) as total FROM bookings WHERE status != 'Cancelled'");
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
                   SUM(seatCount) as totalSeats,
                   SUM(totalPrice * seatCount) as totalRevenue, 
                   MAX(date) as lastTrip,
                   MAX(id) as lastBookingId,
                   GROUP_CONCAT(DISTINCT routeName SEPARATOR ', ') as historyRoutes,
                   GROUP_CONCAT(DISTINCT passengerType SEPARATOR ', ') as historyTypes
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
    $sql = "SELECT id, date, time, routeName, seatCount, totalPrice, status, seatNumbers 
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

if ($action === 'get_crm_analytics') {
    $analytics = [];

    // 1. CHAMPIONS
    // Top Spender
    $res = $conn->query("SELECT passengerName as name, passengerPhone as phone, SUM(totalPrice * seatCount) as total FROM bookings WHERE status != 'Cancelled' AND passengerName != '' GROUP BY passengerPhone ORDER BY total DESC LIMIT 1");
    $analytics['champion_revenue'] = $res->fetch_assoc();

    // Most Trips
    $res = $conn->query("SELECT passengerName as name, passengerPhone as phone, COUNT(id) as total FROM bookings WHERE status != 'Cancelled' AND passengerName != '' GROUP BY passengerPhone ORDER BY total DESC LIMIT 1");
    $analytics['champion_trips'] = $res->fetch_assoc();

    // Most Seats
    $res = $conn->query("SELECT passengerName as name, passengerPhone as phone, SUM(seatCount) as total FROM bookings WHERE status != 'Cancelled' AND passengerName != '' GROUP BY passengerPhone ORDER BY total DESC LIMIT 1");
    $analytics['champion_seats'] = $res->fetch_assoc();

    // 2. DEMOGRAPHICS (Passenger Type)
    $res = $conn->query("SELECT passengerType, COUNT(id) as count FROM bookings WHERE status != 'Cancelled' GROUP BY passengerType");
    $demographics = [];
    while($row = $res->fetch_assoc()) {
        $demographics[] = $row;
    }
    $analytics['demographics'] = $demographics;

    // 3. MONTHLY GROWTH (New Customers)
    // Get last 6 months
    $growth = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthLabel = date('M Y', strtotime("-$i months"));
        
        $sql = "SELECT COUNT(*) as count FROM (
                    SELECT passengerPhone, MIN(date) as firstTrip 
                    FROM bookings 
                    WHERE status != 'Cancelled' AND passengerPhone != '' 
                    GROUP BY passengerPhone 
                    HAVING DATE_FORMAT(firstTrip, '%Y-%m') = '$month'
                ) as new_cust";
        $res = $conn->query($sql);
        $count = $res->fetch_assoc()['count'];
        
        $growth[] = ['month' => $monthLabel, 'count' => (int)$count];
    }
    $analytics['growth'] = $growth;

    echo json_encode($analytics);
    exit;
}

if ($action === 'add_to_queue') {
    $input = json_decode(file_get_contents('php://input'), true);
    $targets = $input['targets'] ?? [];
    $message = $input['message'] ?? '';
    
    if (empty($targets) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Data incomplete']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO broadcast_queue (phone, name, message, status) VALUES (?, ?, ?, 'pending')");
    
    $count = 0;
    foreach ($targets as $t) {
        $phone = $t['phone']; // Ensure normalized phone
        $name = $t['name'];
        // We store the raw message template, frontend replaces it? 
        // Or better: backend stores the raw template, but we might want to resolve it per user if we send via API.
        // For now, let's store the raw template.
        $stmt->bind_param("sss", $phone, $name, $message);
        if($stmt->execute()) $count++;
    }

    echo json_encode(['status' => 'success', 'count' => $count]);
    exit;
}

if ($action === 'get_queue_stats') {
    $stats = [];
    
    // Counts
    $res = $conn->query("SELECT status, COUNT(*) as count FROM broadcast_queue GROUP BY status");
    while($row = $res->fetch_assoc()) {
        $stats[$row['status']] = $row['count'];
    }
    
    // Recent/Pending Items
    $items = [];
    $res = $conn->query("SELECT * FROM broadcast_queue ORDER BY FIELD(status, 'processing', 'pending', 'failed', 'sent'), id ASC LIMIT 50");
    while($row = $res->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode(['stats' => $stats, 'items' => $items]);
    exit;
}

if ($action === 'get_next_queue_item') {
    // Transaction to lock row
    $conn->begin_transaction();

    try {
        // Select one pending item
        $res = $conn->query("SELECT * FROM broadcast_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1 FOR UPDATE");
        if ($res->num_rows > 0) {
            $item = $res->fetch_assoc();
            
            // Mark as processing
            $conn->query("UPDATE broadcast_queue SET status = 'processing', updated_at = NOW() WHERE id = " . $item['id']);
            
            $conn->commit();
            echo json_encode(['status' => 'found', 'item' => $item]);
        } else {
            $conn->commit();
            echo json_encode(['status' => 'empty']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update_queue_status') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'];
    $status = $input['status']; // sent, failed
    
    if(!$id || !$status) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE broadcast_queue SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'clear_queue') {
    $conn->query("TRUNCATE TABLE broadcast_queue");
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'ready']);
?>
