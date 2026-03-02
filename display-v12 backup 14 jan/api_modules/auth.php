<?php
// Auth Actions
if ($action === 'login') {
    $username = $input['username'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role']
        ];
        echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
    } else {
        // Backdoor / Fallback for 'admin' if no DB record exits yet (Safety Net)
        if ($username === 'admin' && $password === 'admin123' && !$user) {
            // Auto Create Admin
            $passHash = password_hash('admin123', PASSWORD_DEFAULT);
            $id = time();
            $conn->query("INSERT INTO users (id, username, password, name, role) VALUES ('$id', 'admin', '$passHash', 'Administrator', 'Admin')");
            
            $_SESSION['user'] = ['id'=>$id, 'username'=>'admin', 'name'=>'Administrator', 'role'=>'Admin'];
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
