<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowed_roles = ['keuangan', 'pimpinan', 'it'];
$is_role_allowed = false;

if (isset($_SESSION['user'])) {
    $user_role_lower = strtolower($_SESSION['user']['role']);
    foreach ($allowed_roles as $allowed) {
        if (strpos($user_role_lower, $allowed) !== false) {
            $is_role_allowed = true;
            break;
        }
    }
}

if (!isset($_SESSION['user'])) {
    // If AJAX request, return JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    // Otherwise redirect to login
    header("Location: login.php");
    exit;
}

if (!$is_role_allowed) {
     if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Access Denied: Role not authorized']);
        exit;
    }
    // Access Denied Page
    echo '<!DOCTYPE html><html><head><title>Akses Ditolak</title><script src="https://cdn.tailwindcss.com"></script></head>
    <body class="h-screen flex items-center justify-center bg-slate-50">
        <div class="text-center p-8 bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md">
            <div class="text-5xl mb-4">🚫</div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Akses Ditolak</h1>
            <p class="text-slate-600 mb-6">Akun anda <b>' . htmlspecialchars($_SESSION['user']['username']) . '</b> dengan role <b>' . htmlspecialchars($_SESSION['user']['role']) . '</b> tidak memiliki izin mengakses halaman ini.</p>
            <a href="../display-v12/booking_management.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Kembali ke Operasional</a>
            <br/><br/>
            <a href="logout.php" class="text-red-600 font-bold hover:underline">Login dengan Akun Lain</a>
        </div>
    </body></html>';
    exit;
}
?>
