<?php
// test_booking_v2.php
$url = 'http://localhost/travel-sr-fe/display-v11/api.php';

$data = [
    'action' => 'create_booking',
    'data' => [
        'id' => time(), // Integer ID
        'serviceType' => 'Travel',
        'routeId' => 'TEST-ROUTE',
        'date' => date('Y-m-d'),
        'time' => '08:00',
        'passengerName' => 'Test Passenger V2',
        'passengerPhone' => '08123456789',
        'passengerType' => 'Umum',
        'seatCount' => 1,
        'selectedSeats' => ['1'],
        'duration' => 1,
        'totalPrice' => 150000,
        'paymentMethod' => 'Cash',
        'paymentStatus' => 'Lunas',
        'validationStatus' => 'Valid',
        'paymentLocation' => 'Loket',
        'paymentReceiver' => 'Admin',
        'paymentProof' => '',
        'seatNumbers' => '1',
        'ktmProof' => '',
        'downPaymentAmount' => 0,
        'type' => 'Regular',
        'seatCapacity' => 7,
        'priceType' => 'Umum',
        'packageType' => 'Perorangan',
        'routeName' => 'Padang - Bukittinggi'
    ]
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Capture error body
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response Code: " . $http_response_header[0] . "\n";
echo "Response Body: " . $result . "\n";
?>
