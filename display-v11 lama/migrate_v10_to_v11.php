<?php
// migrate_v10_to_v11.php

$host = 'localhost';
$user = 'root';
$pass = '';

$source_db = 'sutanraya';
$target_db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully\n";

$tables = [
    'fleet' => [
        'columns' => ['id', 'name', 'plate', 'capacity', 'status', 'icon']
    ],
    'drivers' => [
        'columns' => ['id', 'name', 'phone', 'status', 'licenseType']
    ],
    'trips' => [
        'columns' => ['id', 'routeConfig', 'fleet', 'driver', 'passengers', 'status', 'departureTime']
    ],
    'routes' => [
        'columns' => ['id', 'origin', 'destination', 'price_umum', 'price_pelajar', 'price_dropping', 'price_carter', 'schedules']
    ],
    'bus_routes' => [
        'columns' => ['id', 'name', 'big_bus_config', 'price_s33', 'price_s35', 'is_long_trip', 'minDays'],
        'source_columns' => ['id', 'name', 'big_bus_config', 'price_s33', 'price_s35', 'is_long_trip', 'min_days']
    ],
    'bookings' => [
        'columns' => [
            'id', 'serviceType', 'routeId', 'date', 'time', 'passengerName', 'passengerPhone', 
            'passengerType', 'seatCount', 'selectedSeats', 'duration', 'totalPrice', 
            'paymentMethod', 'paymentStatus', 'validationStatus', 'paymentLocation', 
            'paymentReceiver', 'paymentProof', 'status', 'seatNumbers', 'ktmProof', 
            'downPaymentAmount', 'type', 'seatCapacity', 'priceType', 'packageType', 'routeName'
        ]
    ]
];

foreach ($tables as $table => $config) {
    echo "Migrating table: $table...\n";
    
    // Truncate target table
    $sql_truncate = "TRUNCATE TABLE `$target_db`.`$table`";
    if ($conn->query($sql_truncate) === TRUE) {
        echo "  - Table truncated.\n";
    } else {
        echo "  - Error truncating table: " . $conn->error . "\n";
        continue;
    }

    // Build columns list for INSERT (Target)
    $cols = $config['columns'];
    $cols_str = implode("`, `", $cols);
    
    // Build SELECT list (Source)
    $source_cols = isset($config['source_columns']) ? $config['source_columns'] : $config['columns'];
    $select_parts = [];
    foreach ($source_cols as $col) {
        $select_parts[] = "`$col`";
    }
    $select_str = implode(", ", $select_parts);

    // Copy data
    $sql_copy = "INSERT INTO `$target_db`.`$table` (`$cols_str`) SELECT $select_str FROM `$source_db`.`$table`";
    
    if ($conn->query($sql_copy) === TRUE) {
        echo "  - Data copied successfully. Rows: " . $conn->affected_rows . "\n";
    } else {
        echo "  - Error copying data: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migration completed.\n";
?>
