<?php

// --- F. GET USERS ---
if ($action === 'get_users') {
    // Check if new columns exist by trying to select them, if fail fallback to *
    // To be safe against 500 error on mismatch schema, we use * and PHP-side defaults
    $sql = "SELECT * FROM users";
    
    // Attempt query
    $result = $conn->query($sql);
    
    if (!$result) {
        // Fallback for extreme cases or log error
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
        exit;
    }

    $users = [];
    while($row = $result->fetch_assoc()) {
        // Polyfill missing columns if DB schema is old
        if (!isset($row['password_plain'])) $row['password_plain'] = null;
        if (!isset($row['placement'])) $row['placement'] = 'Padang';
        if (!isset($row['created_at'])) $row['created_at'] = null;
        
        // Handle Role vs Position alias
        if (isset($row['role']) && !isset($row['position'])) {
            $row['position'] = $row['role'];
        } elseif (!isset($row['role']) && isset($row['position'])) {
            $row['role'] = $row['position']; 
        }

        // Ensure sensitive fields are handled safely if needed (frontend expects them)
        if (!isset($row['password'])) $row['password'] = '';

        $users[] = $row;
    }
    
    // Sort by created_at if available in PHP
    usort($users, function($a, $b) {
        $t1 = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $t2 = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $t2 - $t1;
    });

    echo json_encode(['status' => 'success', 'users' => $users]);
    exit;
}

if ($action == 'save_user') {
    $data = $input;
    
    // Check valid columns first to prevent crash
    $validCols = [];
    $colQ = $conn->query("SHOW COLUMNS FROM users");
    if ($colQ) {
        while($c = $colQ->fetch_assoc()) $validCols[] = $c['Field'];
    }
    
    $mode = $data['mode']; // 'add' or 'edit'
    $username = $conn->real_escape_string($data['username']);
    $name = $conn->real_escape_string($data['name']);
    $roleVal = isset($data['position']) ? $conn->real_escape_string($data['position']) : '-';
    $placement = isset($data['placement']) ? $conn->real_escape_string($data['placement']) : '-';
    $password = isset($data['password']) && !empty($data['password']) ? $data['password'] : '';

    // Determine Role Column Name
    $roleCol = null;
    if (in_array('role', $validCols)) $roleCol = 'role';
    elseif (in_array('position', $validCols)) $roleCol = 'position';

    if ($mode == 'add') {
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmtCheck->bind_param("s", $username);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
            exit;
        }
        
        $id = time() . rand(100,999);
        if (empty($password)) $password = '123456';
        $passHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Build INSERT Query
        $cols = ["id", "username", "password", "name"];
        $vals = [$id, $username, $passHash, $name];
        $types = "ssss";

        if ($roleCol) {
            $cols[] = $roleCol;
            $vals[] = $roleVal;
            $types .= "s";
        }
        if (in_array('placement', $validCols)) {
            $cols[] = "placement";
            $vals[] = $placement;
            $types .= "s";
        }
        if (in_array('password_plain', $validCols)) {
            $cols[] = "password_plain";
            $vals[] = $password;
            $types .= "s";
        }

        $sql = "INSERT INTO users (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$vals);
        
    } else {
        $id = $data['id'];
        
        // Build UPDATE Query
        $sets = ["username=?", "name=?"];
        $vals = [$username, $name];
        $types = "ss";

        if ($roleCol) {
            $sets[] = "$roleCol=?";
            $vals[] = $roleVal;
            $types .= "s";
        }
        if (in_array('placement', $validCols)) {
            $sets[] = "placement=?";
            $vals[] = $placement;
            $types .= "s";
        }

        if (!empty($password)) {
            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $sets[] = "password=?";
            $vals[] = $passHash;
            $types .= "s";

            if (in_array('password_plain', $validCols)) {
                $sets[] = "password_plain=?";
                $vals[] = $password;
                $types .= "s";
            }
        }
        
        $sql = "UPDATE users SET " . implode(',', $sets) . " WHERE id=?";
        $vals[] = $id;
        $types .= "s";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$vals);
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
    $original_id = isset($input['original_id']) ? $input['original_id'] : '';
    $origin = $input['origin'];
    $destination = $input['destination'];
    $schedules = json_encode($input['schedules']);
    $prices = $input['prices'];

    // Check if ID exists (for new or rename cases)
    if ($original_id === '' || $id !== $original_id) {
        $check = $conn->prepare("SELECT id FROM routes WHERE id = ?");
        $check->bind_param("s", $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kode Rute (ID) sudah digunakan. Gunakan ID lain.']);
            exit;
        }
    }

    if ($original_id !== '') {
        // UPDATE (potentially renaming)
        // If renaming, we rely on standard UPDATE which changes the PK
        $stmt = $conn->prepare("UPDATE routes SET id=?, origin=?, destination=?, price_umum=?, price_pelajar=?, price_dropping=?, price_carter=?, payroll_1_6=?, payroll_full=?, schedules=? WHERE id=?");
        $stmt->bind_param("sssddddddss", 
            $id, $origin, $destination, 
            $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], 
            $prices['payroll_1_6'], $prices['payroll_full'], 
            $schedules,
            $original_id // WHERE clause
        );
    } else {
        // INSERT NEW
        $stmt = $conn->prepare("INSERT INTO routes (id, origin, destination, price_umum, price_pelajar, price_dropping, price_carter, payroll_1_6, payroll_full, schedules) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdddddds", 
            $id, $origin, $destination, 
            $prices['umum'], $prices['pelajar'], $prices['dropping'], $prices['carter'], 
            $prices['payroll_1_6'], $prices['payroll_full'], 
            $schedules
        );
    }

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
