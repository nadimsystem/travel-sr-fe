<?php
// api_modules/crud.php

if ($action === 'get_fleet') {
    $res = $conn->query("SELECT * FROM bus_fleet ORDER BY name ASC");
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    jsonResponse('success', 'Data fetched', ['data' => $data]);
}

if ($action === 'save_fleet') {
    $data = $input['data'];
    $id = $data['id'] ?? null;
    $name = $data['name'];
    $plate = $data['plateNumber'];
    $cap = $data['capacity'];
    $price = $data['pricePerDay'] ?? 0;
    $status = $data['status'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE bus_fleet SET name=?, plateNumber=?, capacity=?, pricePerDay=?, status=? WHERE id=?");
        $stmt->bind_param("ssidsi", $name, $plate, $cap, $price, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO bus_fleet (name, plateNumber, capacity, pricePerDay, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssids", $name, $plate, $cap, $price, $status);
    }
    
    if ($stmt->execute()) jsonResponse('success', 'Armada saved');
    else jsonResponse('error', $conn->error);
}

if ($action === 'delete_fleet') {
    $id = $input['id'];
    $conn->query("DELETE FROM bus_fleet WHERE id=$id");
    jsonResponse('success', 'Deleted');
}

// --- DRIVERS ---
if ($action === 'get_drivers') {
    $res = $conn->query("SELECT * FROM bus_drivers ORDER BY name ASC");
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    jsonResponse('success', 'Data fetched', ['data' => $data]);
}

if ($action === 'save_driver') {
    $data = $input['data'];
    $id = $data['id'] ?? null;
    $name = $data['name'];
    $phone = $data['phone'];
    $status = $data['status'];
    
    if ($id) {
        $stmt = $conn->prepare("UPDATE bus_drivers SET name=?, phone=?, status=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $phone, $status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO bus_drivers (name, phone, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $phone, $status);
    }
    
    if ($stmt->execute()) jsonResponse('success', 'Driver saved');
    else jsonResponse('error', $conn->error);
}

if ($action === 'delete_driver') {
    $id = $input['id'];
    $conn->query("DELETE FROM bus_drivers WHERE id=$id");
    jsonResponse('success', 'Deleted');
}
?>
