<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sutanraya';

// $host = 'localhost';
// $user = 'sutanray_admin';
// $pass = '@adminpass1998';
// $db   = 'sutanray_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$input = json_decode(file_get_contents('php://input'), true);

// Security: Sanitize Input
function sanitize_input($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize_input($value);
        }
    } else {
        $data = htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

if ($input) {
    $input = sanitize_input($input);
}
if ($_GET) {
    $_GET = sanitize_input($_GET);
    $action = $_GET['action'] ?? ''; // Re-assign action after sanitization
}

session_start();

session_start();

// 1. Setup Table (Coupons Only - Users removed)
if ($action === 'setup_table') {
    // Table Coupons
    $sql1 = "CREATE TABLE IF NOT EXISTS doorprize_coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coupon_number VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        departure_date DATE,
        is_winner TINYINT(1) DEFAULT 0,
        deleted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->query($sql1);

    // Check if deleted_at exists (for migration)
    $checkCol = $conn->query("SHOW COLUMNS FROM doorprize_coupons LIKE 'deleted_at'");
    if ($checkCol->num_rows == 0) {
        $conn->query("ALTER TABLE doorprize_coupons ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
    }

    echo "Tables ready.";
    exit;
}

// AUTHENTICATION (Hardcoded but Hidden)
// Configuration for Users
$auth_config = [
    'admindoorprz' => [
        'password' => '@srp4sshadiah@', // Admin Password
        'role' => 'admin'
    ],
    'pimpinan' => [
        'password' => '@srp4sspimpinan@', // Pimpinan Password
        'role' => 'pimpinan'
    ]
];

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        exit;
    }
    
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'admin';

    // Check against config
    if (isset($auth_config[$username])) {
        $user_data = $auth_config[$username];
        
        // Verify Password (Direct comparison for stability)
        if ($password === $user_data['password']) {
            // Verify Role
            if ($user_data['role'] === $role) {
                $_SESSION['role'] = $role;
                $_SESSION['logged_in'] = true;
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Role tidak sesuai']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password salah']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'check_auth') {
    $role = $_GET['role'] ?? 'admin';
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['role']) && $_SESSION['role'] === $role) {
        echo json_encode(['status' => 'success', 'role' => $_SESSION['role']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    }
    exit;
}

// PROTECTED ENDPOINTS (Require Admin)

// 2. Save Coupon (Create/Update)
if ($action === 'save_coupon') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        exit;
    }
    
    // Validate input
    if (!isset($input['coupon_number']) || !isset($input['name']) || !isset($input['phone'])) {
        echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
        exit;
    }

    $coupon_number = str_pad($input['coupon_number'], 6, '0', STR_PAD_LEFT);
    $name = $input['name'];
    $phone = $input['phone'];
    $departure_date = isset($input['departure_date']) ? $input['departure_date'] : null;

    // Check if exists
    $check = $conn->prepare("SELECT id FROM doorprize_coupons WHERE coupon_number = ?");
    $check->bind_param("s", $coupon_number);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update (and Restore if deleted)
        $stmt = $conn->prepare("UPDATE doorprize_coupons SET name=?, phone=?, departure_date=?, deleted_at=NULL WHERE coupon_number=?");
        $stmt->bind_param("ssss", $name, $phone, $departure_date, $coupon_number);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO doorprize_coupons (coupon_number, name, phone, departure_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $coupon_number, $name, $phone, $departure_date);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// 3. Get Coupons (Active Only)
if ($action === 'get_coupons') {
    $sql = "SELECT * FROM doorprize_coupons WHERE deleted_at IS NULL ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 3b. Get Trash (Deleted Only)
if ($action === 'get_trash') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $sql = "SELECT * FROM doorprize_coupons WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
    $result = $conn->query($sql);
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 4. Draw Winner (Require Pimpinan)
if ($action === 'draw_winner') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pimpinan') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Butuh login Pimpinan.']);
        exit;
    }

    // Select random winner who hasn't won yet AND is not deleted
    $sql = "SELECT * FROM doorprize_coupons WHERE is_winner = 0 AND deleted_at IS NULL ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $winner = $result->fetch_assoc();
        
        // Mark as winner
        $update = $conn->prepare("UPDATE doorprize_coupons SET is_winner = 1 WHERE id = ?");
        $update->bind_param("i", $winner['id']);
        $update->execute();

        echo json_encode(['status' => 'success', 'winner' => $winner]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No eligible participants left']);
    }
    exit;
}

// 5. Reset Draw
if ($action === 'reset_draw') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
     $conn->query("UPDATE doorprize_coupons SET is_winner = 0 WHERE deleted_at IS NULL");
     echo json_encode(['status' => 'success', 'message' => 'Draw reset']);
     exit;
}

// 6. Delete Coupon (Soft Delete)
if ($action === 'delete_coupon') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        exit;
    }
    $id = $input['id'];
    // Soft Delete: Set deleted_at to NOW()
    $stmt = $conn->prepare("UPDATE doorprize_coupons SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// 9. Restore Coupon
if ($action === 'restore_coupon') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        exit;
    }
    $id = $input['id'];
    $stmt = $conn->prepare("UPDATE doorprize_coupons SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// 10. Force Delete Coupon (Permanent)
if ($action === 'force_delete_coupon') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
        exit;
    }
    $id = $input['id'];
    $stmt = $conn->prepare("DELETE FROM doorprize_coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

// 7. Get Leaderboard (Active Only)
if ($action === 'get_leaderboard') {
    $sql = "SELECT name, phone, COUNT(*) as total_coupons 
            FROM doorprize_coupons 
            WHERE deleted_at IS NULL
            GROUP BY name, phone 
            ORDER BY total_coupons DESC, name ASC 
            LIMIT 10";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// 8. Search Participant (Active Only)
if ($action === 'search_participant') {
    $phone = isset($_GET['phone']) ? $_GET['phone'] : '';
    if (empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Nomor HP wajib diisi']);
        exit;
    }

    // Get basic info and count
    $stmt = $conn->prepare("SELECT name, phone, COUNT(*) as total_coupons FROM doorprize_coupons WHERE phone = ? AND deleted_at IS NULL GROUP BY name, phone");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Get list of coupons
        $stmt2 = $conn->prepare("SELECT coupon_number, is_winner FROM doorprize_coupons WHERE phone = ? AND deleted_at IS NULL");
        $stmt2->bind_param("s", $phone);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $coupons = [];
        while ($c = $res2->fetch_assoc()) {
            $coupons[] = $c;
        }
        $row['coupons'] = $coupons;
        
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
    }
    exit;
}

// 11. Get Stats (Public)
if ($action === 'get_stats') {
    // Count Total Coupons
    $sqlCoupons = "SELECT COUNT(*) as total FROM doorprize_coupons WHERE deleted_at IS NULL";
    $resCoupons = $conn->query($sqlCoupons);
    $totalCoupons = $resCoupons->fetch_assoc()['total'];

    // Count Total Participants (Unique Phone Numbers)
    $sqlPeople = "SELECT COUNT(DISTINCT phone) as total FROM doorprize_coupons WHERE deleted_at IS NULL";
    $resPeople = $conn->query($sqlPeople);
    $totalPeople = $resPeople->fetch_assoc()['total'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_coupons' => $totalCoupons,
            'total_participants' => $totalPeople
        ]
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
