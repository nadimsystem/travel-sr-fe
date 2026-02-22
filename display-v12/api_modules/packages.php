<?php

// --- I. GET PACKAGES ---
if ($action === 'get_packages') {
    $sql = "SELECT * FROM packages ORDER BY createdAt DESC";
    $result = $conn->query($sql);
    $packages = [];
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $row['id'] = (float)$row['id'];
            $row['price'] = (double)$row['price'];
            $packages[] = $row;
        }
    }
    
    echo json_encode(['packages' => $packages]);
    exit;
}

// --- L. CREATE PACKAGE (EXPEDITION STYLE) ---
if ($action === 'create_package') {
    $data = $input['data'];
    
    // Generate Receipt Number (RES-YYYYMMDD-HIS-RAND)
    $receiptNumber = 'RES-' . date('YmdHis') . '-' . rand(100, 999);
    
    $conn->begin_transaction();
    try {
        // 1. Insert Package
        $stmt = $conn->prepare("INSERT INTO packages (receiptNumber, senderName, senderPhone, receiverName, receiverPhone, itemDescription, itemType, category, route, price, paymentMethod, paymentStatus, status, pickupAddress, dropoffAddress, mapLink, bookingDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Sanitize & Default inputs
        $sName = $data['senderName'] ?? '-';
        $sPhone = $data['senderPhone'] ?? '-';
        $rName = $data['receiverName'] ?? '-';
        $rPhone = $data['receiverPhone'] ?? '-';
        $iDesc = $data['itemDescription'] ?? '-';
        $iType = $data['itemType'] ?? 'General';
        $cat = $data['category'] ?? 'Pool to Pool';
        $route = $data['route'] ?? '-';
        $price = (double)($data['price'] ?? 0);
        $payMethod = $data['paymentMethod'] ?? 'Cash';
        $payStatus = $data['paymentStatus'] ?? 'Belum Lunas';
        $status = $data['status'] ?? 'Pending';
        $pickup = $data['pickupAddress'] ?? '';
        $dropoff = $data['dropoffAddress'] ?? '';
        $map = $data['mapLink'] ?? '';
        $bDate = $data['bookingDate'] ?? date('Y-m-d');

        $stmt->bind_param("ssssssssdssssssss", 
            $receiptNumber,
            $sName, 
            $sPhone, 
            $rName, 
            $rPhone, 
            $iDesc, 
            $iType, 
            $cat, 
            $route, 
            $price, 
            $payMethod, 
            $payStatus, 
            $status, 
            $pickup, 
            $dropoff, 
            $map,
            $bDate
        );
        $stmt->execute();
        $packageId = $conn->insert_id;

        // 2. Insert Initial Log
        $logDesc = "Paket berhasil dibuat (No. Resi: $receiptNumber)";
        $adminName = isset($data['adminName']) ? $data['adminName'] : 'Admin';
        
        $stmtLog = $conn->prepare("INSERT INTO package_logs (package_id, status, description, admin_name) VALUES (?, ?, ?, ?)");
        $stmtLog->bind_param("isss", $packageId, $status, $logDesc, $adminName);
        $stmtLog->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'id' => $packageId, 'receiptNumber' => $receiptNumber]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- M. UPDATE PACKAGE STATUS WITH LOG ---
if ($action === 'update_package_status') {
    $id = $input['id'];
    $status = $input['status'];
    $description = isset($input['description']) ? $input['description'] : "Status diubah menjadi $status";
    $adminName = isset($input['adminName']) ? $input['adminName'] : 'Admin';
    $location = isset($input['location']) ? $input['location'] : '-';

    $conn->begin_transaction();
    try {
        // 1. Update Current Status
        $stmt = $conn->prepare("UPDATE packages SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        
        // 2. Insert History Log
        $stmtLog = $conn->prepare("INSERT INTO package_logs (package_id, status, description, location, admin_name) VALUES (?, ?, ?, ?, ?)");
        $stmtLog->bind_param("issss", $id, $status, $description, $location, $adminName);
        $stmtLog->execute();

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Gagal update: ' . $e->getMessage()]);
    }
    exit;
}

// --- N. GET PACKAGE DETAILS & LOGS ---
if ($action === 'get_package_details') {
    $id = $input['id'];
    
    // Get Package
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    
    if (!$pkg) {
        echo json_encode(['status' => 'error', 'message' => 'Paket tidak ditemukan']);
        exit;
    }

    // Get Logs
    $stmtLog = $conn->prepare("SELECT * FROM package_logs WHERE package_id = ? ORDER BY created_at DESC");
    $stmtLog->bind_param("i", $id);
    $stmtLog->execute();
    $resLogs = $stmtLog->get_result();
    
    $logs = [];
    while($row = $resLogs->fetch_assoc()) {
        $logs[] = $row;
    }

    echo json_encode(['status' => 'success', 'package' => $pkg, 'logs' => $logs]);
    exit;
}



?>
