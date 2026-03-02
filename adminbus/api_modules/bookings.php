<?php
// api_modules/bookings.php

if ($action === 'get_bookings') {
    // Filter by Date (Initial Load) or Range
    $date = $input['date'] ?? date('Y-m-d');
    
    // Fetch bookings that overlap with this date
    // Logic: (start <= date AND end >= date)
    $sql = "SELECT b.*, f.name as fleetName, d.name as driverName 
            FROM bus_bookings b 
            LEFT JOIN bus_fleet f ON b.fleetId = f.id
            LEFT JOIN bus_drivers d ON b.driverId = d.id
            WHERE ? BETWEEN b.tripDateStart AND b.tripDateEnd
            ORDER BY b.createdAt DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $bookings = [];
    while($row = $res->fetch_assoc()) $bookings[] = $row;
    
    // Also fetch Availability for this date
    // A bus is available if NO booking overlaps this date
    // Subquery: SELECT fleetId FROM bus_bookings WHERE date overlaps
    $sqlAvail = "SELECT * FROM bus_fleet WHERE id NOT IN (
        SELECT fleetId FROM bus_bookings 
        WHERE ? BETWEEN tripDateStart AND tripDateEnd
        AND status != 'Cancelled'
        AND fleetId IS NOT NULL
    )";
    $stmtAvail = $conn->prepare($sqlAvail);
    $stmtAvail->bind_param("s", $date);
    $stmtAvail->execute();
    $resAvail = $stmtAvail->get_result();
    $availableFleet = [];
    while($row = $resAvail->fetch_assoc()) $availableFleet[] = $row;
    
    jsonResponse('success', 'Data fetched', ['bookings' => $bookings, 'availableFleet' => $availableFleet]);
}

if ($action === 'create_booking') {
    $data = $input['data'];
    
    $code = 'BUS-' . date('ymd') . '-' . rand(100,999);
    $start = $data['tripDateStart'];
    $duration = (int)$data['durationDays'];
    $end = date('Y-m-d', strtotime($start . ' + ' . ($duration - 1) . ' days'));
    
    $stmt = $conn->prepare("INSERT INTO bus_bookings (bookingCode, customerName, customerPhone, tripDateStart, tripDateEnd, durationDays, pickupLocation, dropoffLocation, routeDescription, totalPrice, dpAmount, paymentMethod, paymentLocation, paymentReceiver, paymentStatus, fleetId, driverId, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Nullable Integers
    $fId = !empty($data['fleetId']) ? $data['fleetId'] : null;
    $dId = !empty($data['driverId']) ? $data['driverId'] : null;
    
    $stmt->bind_param("sssssissssdsisssiis", 
        $code,
        $data['customerName'],
        $data['customerPhone'],
        $start,
        $end,
        $duration,
        $data['pickupLocation'],
        $data['dropoffLocation'],
        $data['routeDescription'],
        $data['totalPrice'],
        $data['dpAmount'],
        $data['paymentMethod'],
        $data['paymentLocation'],       // New
        $data['paymentReceiver'],       // New
        $data['paymentStatus'],
        $fId,
        $dId,
        $data['status'],
        $data['notes']
    );
    
    if ($stmt->execute()) jsonResponse('success', 'Booking Created', ['id' => $conn->insert_id]);
    else jsonResponse('error', $conn->error);
}
?>
