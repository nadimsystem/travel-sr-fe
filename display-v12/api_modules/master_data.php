<?php

// --- F. GET USERS ---
if ($action === 'get_users') {
    $sql = "SELECT id, username, name, position, placement, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['status' => 'success', 'users' => $users]);
    exit;
}

if ($action == 'save_user') {
    $data = $input;
    
    $mode = $data['mode']; // 'add' or 'edit'
    $username = $conn->real_escape_string($data['username']);
    $name = $conn->real_escape_string($data['name']);
    
    $position = isset($data['position']) ? $conn->real_escape_string($data['position']) : '-';
    $placement = isset($data['placement']) ? $conn->real_escape_string($data['placement']) : '-';

    $password = isset($data['password']) && !empty($data['password']) ? $data['password'] : '';
    
    if ($mode == 'add') {
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmtCheck->bind_param("s", $username);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            exit;
        }
        
        $id = time() . rand(100,999);
        if (empty($password)) {
            $password = '123456';
        }
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (id, username, password, name, position, placement) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $id, $username, $passHash, $name, $position, $placement);
        
    } else {
        $id = $data['id'];
        if (!empty($password)) {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, name=?, position=?, placement=?, password=? WHERE id=?");
            $stmt->bind_param("ssssss", $username, $name, $position, $placement, $passHash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, name=?, position=?, placement=? WHERE id=?");
            $stmt->bind_param("sssss", $username, $name, $position, $placement, $id);
        }
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}

if ($action == 'delete_user') {
    $data = $input;
    $id = $data['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    exit;
}


// --- DRIVERS CRUD ---
if ($action === 'create_driver') {
    $d = $input['data'];
    $id = $d['id'];
    $name = $d['name'];
    $phone = $d['phone'];
    $license = $d['licenseType'];
    $status = $d['status'];

    $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, licenseType, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id, $name, $phone, $license, $status);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'update_driver') {
    $d = $input['data'];
    $id = $d['id'];
    $name = $d['name'];
    $phone = $d['phone'];
    $license = $d['licenseType'];
    $status = $d['status'];

    $stmt = $conn->prepare("UPDATE drivers SET name=?, phone=?, licenseType=?, status=? WHERE id=?");
    $stmt->bind_param("sssss", $name, $phone, $license, $status, $id);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'save_driver') {
    $id = $input['id'];
    $name = $input['name'];
    $phone = $input['phone'];
    $status = $input['status'];
    $licenseType = isset($input['licenseType']) ? $input['licenseType'] : '';

    $stmt = $conn->prepare("INSERT INTO drivers (id, name, phone, status, licenseType) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, phone=?, status=?, licenseType=?");
    $stmt->bind_param("sssssssss", 
        $id, $name, $phone, $status, $licenseType,
        $name, $phone, $status, $licenseType
    );

    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'delete_driver') {
    $id = $input['id'];
    $stmt = $conn->prepare("DELETE FROM drivers WHERE id=?");
    $stmt->bind_param("s", $id);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// --- FLEET CRUD ---
if ($action === 'create_fleet') {
    $f = $input['data'];
    $id = $f['id'];
    $name = $f['name'];
    $plate = $f['plate'];
    $capacity = $f['capacity'];
    $status = $f['status'];
    $icon = isset($f['icon']) ? $f['icon'] : 'bi-truck-front-fill';

    $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $id, $name, $plate, $capacity, $status, $icon);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'update_fleet') {
    $f = $input['data'];
    $id = $f['id'];
    $name = $f['name'];
    $plate = $f['plate'];
    $capacity = $f['capacity'];
    $status = $f['status'];
    $icon = isset($f['icon']) ? $f['icon'] : 'bi-truck-front-fill';

    $stmt = $conn->prepare("UPDATE fleet SET name=?, plate=?, capacity=?, status=?, icon=? WHERE id=?");
    $stmt->bind_param("ssisss", $name, $plate, $capacity, $status, $icon, $id);
    
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'save_fleet') {
    $id = $input['id'];
    $name = $input['name'];
    $plate = $input['plate'];
    $capacity = $input['capacity'];
    $status = $input['status'];
    $icon = $input['icon'];

    $stmt = $conn->prepare("INSERT INTO fleet (id, name, plate, capacity, status, icon) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=?, plate=?, capacity=?, status=?, icon=?");
    $stmt->bind_param("sssissssiss", 
        $id, $name, $plate, $capacity, $status, $icon,
        $name, $plate, $capacity, $status, $icon
    );

    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'delete_fleet') {
    $id = $input['id'];
    $stmt = $conn->prepare("DELETE FROM fleet WHERE id=?");
    $stmt->bind_param("s", $id);
    if($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// --- H. MANAJEMEN RUTE (ROUTE) ---
if ($action === 'save_route') {
    $id = $input['id'];
    $origin = $input['origin'];
    $destination = $input['destination'];
    $schedules = json_encode($input['schedules']);
    $prices = $input['prices'];

    $stmt = $conn->prepare("INSERT INTO routes (id, origin, destination, price_umum, price_pelajar, price_dropping, price_carter, schedules) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE origin=?, destination=?, price_umum=?, price_pelajar=?, price_dropping=?, price_carter=?, schedules=?");
    
    $stmt->bind_param("sssddddsssdddds", 
        $id, $origin, $destination, $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], $schedules,
        $origin, $destination, $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], $schedules
    );

    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

if ($action === 'delete_route') {
    $id = $input['id'];
    $stmt = $conn->prepare("DELETE FROM routes WHERE id=?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}
?>
