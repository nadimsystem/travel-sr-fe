<?php
header("Content-Type: application/json");
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'sutanraya';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$conn->begin_transaction();

try {
    // 1. Truncate Tables
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['routes', 'bus_routes', 'fleet', 'drivers', 'bookings', 'trips'];
    foreach ($tables as $table) {
        $conn->query("TRUNCATE TABLE $table");
    }
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // 2. Insert Routes
    $routes = [
        ["PDG-BKT", "Padang", "Bukittinggi", 120000, 100000, 900000, 1500000, json_encode(["08:00", "10:00", "12:00", "14:00", "16:00", "18:00", "20:00"])],
        ["BKT-PDG", "Bukittinggi", "Padang", 120000, 100000, 900000, 1500000, json_encode(["06:00", "08:00", "10:00", "13:00", "15:00", "17:00", "18:00", "19:00"])],
        ["PDG-PYK", "Padang", "Payakumbuh", 150000, 130000, 1100000, 1800000, json_encode(["08:00", "10:00", "14:00", "18:00"])],
        ["PYK-PDG", "Payakumbuh", "Padang", 150000, 130000, 1100000, 1800000, json_encode(["05:00", "07:00", "10:00", "14:00", "17:00"])]
    ];

    $stmt = $conn->prepare("INSERT INTO routes (id, origin, destination, price_umum, price_pelajar, price_dropping, price_carter, schedules) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($routes as $r) {
        $stmt->bind_param("sssdddds", $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7]);
        $stmt->execute();
    }

    // 3. Insert Bus Routes
    // Schema: id, name, min_days, price_s33, price_s35, is_long_trip, big_bus_config
    $busRoutes = [
        ["PDG-BKT", "Padang - Bukittinggi", 1, 2500000, 2600000, 0, json_encode(["s45"=>["kantor"=>4000000, "agen"=>3800000], "s32"=>["kantor"=>4500000, "agen"=>4300000]])],
        ["PDG-PYK", "Padang - Payakumbuh", 1, 2600000, 2700000, 0, json_encode(["s45"=>["kantor"=>4300000, "agen"=>4000000], "s32"=>["kantor"=>4300000, "agen"=>4000000]])],
        ["PDG-JKT", "Padang - Jakarta", 6, 0, 0, 1, json_encode(["base"=>4500000, "allin"=>5500000])],
        ["PDG-KNO", "Padang - Medan", 6, 3500000, 3600000, 1, json_encode(["base"=>4500000, "allin"=>5500000])]
    ];
    
    $stmt = $conn->prepare("INSERT INTO bus_routes (id, name, min_days, price_s33, price_s35, is_long_trip, big_bus_config) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($busRoutes as $r) {
        $stmt->bind_param("ssiddis", $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]);
        $stmt->execute();
    }

    // 4. Insert Fleet
    $fleet = [
        [1, "Hiace Premio 01", "BA 1001 HP", 7, "Tersedia", "bi-truck-front-fill"],
        [2, "Medium Bus 21", "BA 7021 MB", 33, "Tersedia", "bi-bus-front-fill"],
        [3, "Hiace Commuter 02", "BA 1002 HC", 14, "On Trip", "bi-van-fill"],
        [4, "Big Bus 05", "BA 7005 BB", 45, "Perbaikan", "bi-bus-front-fill"]
    ];
    $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($fleet as $f) {
        $stmt->bind_param("dssiss", $f[0], $f[1], $f[2], $f[3], $f[4], $f[5]);
        $stmt->execute();
    }

    // 5. Insert Drivers
    $drivers = [
        [101, "Pak Budi", "0812345678", "Standby", "B1 Umum"],
        [102, "Pak Andi", "0812987654", "Jalan", "B2 Umum"],
        [103, "Pak Cecep", "0812112233", "Standby", "A Umum"]
    ];
    $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?)");
    foreach ($drivers as $d) {
        $stmt->bind_param("dssss", $d[0], $d[1], $d[2], $d[3], $d[4]);
        $stmt->execute();
    }

    // 6. Insert Bookings
    // We'll create some sample bookings
    $today = date('Y-m-d');
    $bookings = [
        [time()*1000 + 1, 'Travel', 'PDG-BKT', $today, '08:00', 'Ahmad', '0811111111', 'Umum', 1, '["3"]', 1, 120000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Admin', '', 'Pending', '3', null, 0, null, null, null, null, null],
        [time()*1000 + 2, 'Travel', 'PDG-BKT', $today, '08:00', 'Siti', '0822222222', 'Pelajar', 1, '["4"]', 1, 100000, 'Transfer', 'Lunas', 'Valid', '', '', 'proof1.jpg', 'Pending', '4', 'ktm1.jpg', 0, null, null, null, null, null],
        [time()*1000 + 3, 'Travel', 'BKT-PDG', $today, '10:00', 'Budi', '0833333333', 'Umum', 2, '["1","2"]', 1, 240000, 'Cash', 'Lunas', 'Valid', 'Loket', 'Admin', '', 'Pending', '1, 2', null, 0, null, null, null, null, null],
        [time()*1000 + 4, 'Bus Pariwisata', 'PDG-BKT', $today, null, 'SMA 1 Padang', '0844444444', 'Umum', 1, '[]', 1, 2500000, 'DP', 'DP', 'Valid', '', '', 'proof2.jpg', 'Pending', 'Bus Unit', null, 500000, 'Medium', 33, 'Kantor', 'Unit', 'Padang - Bukittinggi']
    ];

    $stmt = $conn->prepare("INSERT INTO bookings (id, serviceType, routeId, date, time, passengerName, passengerPhone, passengerType, seatCount, selectedSeats, duration, totalPrice, paymentMethod, paymentStatus, validationStatus, paymentLocation, paymentReceiver, paymentProof, status, seatNumbers, ktmProof, downPaymentAmount, type, seatCapacity, priceType, packageType, routeName) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($bookings as $b) {
        $stmt->bind_param("dsssssssisddsssssssssdsisss", 
            $b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8], 
            $b[9], $b[10], $b[11], $b[12], $b[13], $b[14], $b[15], $b[16], 
            $b[17], $b[18], $b[19], $b[20], $b[21], $b[22], $b[23], $b[24], 
            $b[25], $b[26]
        );
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Database populated successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
