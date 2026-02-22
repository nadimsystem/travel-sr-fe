<?php
// verify_transfer_api.php
include 'base.php'; // Assumes base.php sets up $host, $user, $pass, $db

// Mock $_GET for 'get_occupied_seats' helper
function check_seats($routeId, $date, $time) {
    global $host, $user, $pass, $db;
    $conn = new mysqli($host, $user, $pass, $db);
    $_GET['action'] = 'get_occupied_seats';
    $_GET['routeId'] = $routeId;
    $_GET['date'] = $date;
    $_GET['time'] = $time;
    
    // Capture output
    ob_start();
    include 'api_modules/bookings.php';
    $output = ob_get_clean();
    $conn->close();
    return json_decode($output, true);
}

// 1. Setup Data
$testId = time(); // Numeric ID
$routeA = 'ROUTE_A';
$routeB = 'ROUTE_B'; // Physical
$date = date('Y-m-d');
$time = '08:00';
$seat = '99'; // Test Seat

// 2. Insert Booking directly into DB first (to test Insert logic, we need to mock POST)
// Instead of full mock, let's just use the DB connection to Insert using the API logic if possible, 
// OR just execute the INSERT query manually to match what we expect the API to do, then test the SELECT logic.
// BUT verifying the API Insert is better.

$conn = new mysqli($host, $user, $pass, $db);
$url = "http://localhost/travel-sr-fe/display-v12/api.php?action=create_booking";
// We can't curl localhost easily if not running server. 
// We will just simulate the INSERT by running the relevant code snippet or creating a temp mock file.

// Let's just create a mock controller in this file that includes the implementation.
$action = 'create_booking';
$input = [
    'data' => [
        'id' => $testId,
        'serviceType' => 'Travel',
        'routeId' => $routeA,
        'physicalRouteId' => $routeB,
        'date' => $date,
        'time' => $time,
        'passengerName' => 'Test User',
        'passengerPhone' => '000',
        'passengerType' => 'Umum',
        'seatCount' => 1,
        'selectedSeats' => [$seat],
        'seatNumbers' => $seat,
        'duration' => 1,
        'totalPrice' => 100000,
        'paymentMethod' => 'Cash',
        'paymentStatus' => 'Lunas',
        'validationStatus' => 'Valid',
        'paymentLocation' => 'Test',
        'paymentReceiver' => 'Test',
        'paymentProof' => '',
        'batchNumber' => 1
    ]
];

// Capture Output for Create
ob_start();
// Inject variables for bookings.php
include 'api_modules/bookings.php';
$res = ob_get_clean();
echo "Create Result: $res\n";

// 3. Verify Database
$booking = $conn->query("SELECT * FROM bookings WHERE id='$testId'")->fetch_assoc();
echo "DB Check: routeId={$booking['routeId']}, physicalRouteId={$booking['physicalRouteId']}\n";

if ($booking['routeId'] === $routeA && $booking['physicalRouteId'] === $routeB) {
    echo "PASS: Database record correct.\n";
} else {
    echo "FAIL: Database record incorrect.\n";
}

// 4. Check Seats on Route A (Should be Empty of this seat)
$resetAction = $action; $action = 'get_occupied_seats';
$_GET['routeId'] = $routeA; $_GET['date'] = $date; $_GET['time'] = $time;
ob_start();
include 'api_modules/bookings.php';
$seatsA = json_decode(ob_get_clean(), true);
$foundA = false;
foreach ($seatsA as $s) {
    if (strpos($s['seatNumbers'], $seat) !== false) $foundA = true;
}
echo "Route A Occupied: " . ($foundA ? "YES (Fail)" : "NO (Pass)") . "\n";

// 5. Check Seats on Route B (Should be Occupied)
$_GET['routeId'] = $routeB;
ob_start();
include 'api_modules/bookings.php';
$seatsB = json_decode(ob_get_clean(), true);
$foundB = false;
foreach ($seatsB as $row) {
    if (strpos($row['seatNumbers'], $seat) !== false) $foundB = true;
}
echo "Route B Occupied: " . ($foundB ? "YES (Pass)" : "NO (Fail)") . "\n";

// 6. Cleanup
$conn->query("DELETE FROM bookings WHERE id='$testId'");
echo "Cleanup done.\n";

?>
