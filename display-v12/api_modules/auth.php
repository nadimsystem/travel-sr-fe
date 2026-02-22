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
        if (isset($input['remember']) && $input['remember'] === true) {
            $cookie_name = session_name();
            $cookie_value = session_id();
            $cookie_lifetime = 86400 * 30; // 30 days
            setcookie($cookie_name, $cookie_value, time() + $cookie_lifetime, "/");
        }
        
        echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
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
