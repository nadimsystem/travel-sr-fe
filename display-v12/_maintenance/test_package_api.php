<?php
// Test Script for Package Logic
$baseUrl = 'http://localhost/travel-sr-fe/display-v12/api.php';

function post($url, $data) {
    echo "POST $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error: " . curl_error($ch) . "\n";
    }
    curl_close($ch);
    return json_decode($response, true);
}

// 1. Test Create Package
echo "--- TEST 1: Create Package ---\n";
$newPackage = [
    'action' => 'create_package',
    'data' => [
        'senderName' => 'Test Sender',
        'senderPhone' => '08123456789',
        'receiverName' => 'Test Receiver',
        'receiverPhone' => '08987654321',
        'itemType' => 'Dokumen',
        'itemDescription' => 'Test Item Description',
        'category' => 'Pool to Pool',
        'route' => 'Padang - Bukittinggi',
        'price' => 30000,
        // Omit optional fields to test null handling
    ]
];
$res1 = post($baseUrl, $newPackage);
print_r($res1);

if (isset($res1['status']) && $res1['status'] === 'success') {
    echo "SUCCESS: Package Created. ID: " . $res1['id'] . "\n";
    $pkgId = $res1['id'];

    // 2. Test Update Status
    echo "\n--- TEST 2: Update Status ---\n";
    $updateData = [
        'action' => 'update_package_status',
        'id' => $pkgId,
        'status' => 'Dikirim',
        'location' => 'Gudang Pusat',
        'description' => 'Paket sedang disortir'
    ];
    $res2 = post($baseUrl, $updateData);
    print_r($res2);
    
    if (isset($res2['status']) && $res2['status'] === 'success') {
        echo "SUCCESS: Status Updated.\n";
    } else {
        echo "FAILED: Status Update.\n";
    }

} else {
    echo "FAILED: Package Creation.\n";
}
?>
