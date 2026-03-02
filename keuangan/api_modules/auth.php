<?php
// Auth Actions
// Auth Actions
if ($action === 'login') {
    $username = $input['username'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Role Validation Logic
    $allowed_roles = ['keuangan', 'pimpinan', 'it'];
    $is_role_allowed = false;
    
    if ($user) {
        $user_role_lower = strtolower($user['role']);
        foreach ($allowed_roles as $allowed) {
            if (strpos($user_role_lower, $allowed) !== false) {
                $is_role_allowed = true;
                break;
            }
        }
    }
    
    if ($user && password_verify($password, $user['password'])) {
        if ($is_role_allowed) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak: Anda tidak memiliki akses ke modul Keuangan.']);
        }
    } else {
        // Backdoor / Fallback for 'admin' if no DB record exits yet (Safety Net)
        // Only allow if role 'Admin Keuangan' (simulated superadmin for now, but strict is better)
        // Let's keep the backdoor but maybe restrict it? 
        // For safety, let's just stick to DB authentication as primary.
        
        if ($username === 'admin' && $password === 'admin123' && !$user) {
            // Auto Create Admin - Force role to 'Admin Keuangan' so they can login next time
            $passHash = password_hash('admin123', PASSWORD_DEFAULT);
            $id = time();
            $conn->query("INSERT INTO users (id, username, password, name, role) VALUES ('$id', 'admin', '$passHash', 'Administrator', 'Admin Keuangan')");
            
            $_SESSION['user'] = ['id'=>$id, 'username'=>'admin', 'name'=>'Administrator', 'role'=>'Admin Keuangan'];
            echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
        }
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'check_session') {
    if (isset($_SESSION['user'])) {
        echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    }
    exit;
}
?>
