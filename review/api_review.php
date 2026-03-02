<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../display-v12/base.php';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->name) && !empty($data->phone) && !empty($data->rating)) {
    
    $name = strip_tags($data->name);
    $phone = strip_tags($data->phone);
    $rating = intval($data->rating);
    $comment = !empty($data->comment) ? strip_tags($data->comment) : "";
    $admin_contact = !empty($data->admin_contact) ? strip_tags($data->admin_contact) : "";

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo json_encode(["status" => "error", "message" => "Rating must be between 1 and 5."]);
        exit();
    }

    // Use Prepared Statements for security (SQL Injection prevention)
    $stmt = $conn->prepare("INSERT INTO testimoni (admin_contact, name, phone, rating, comment) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssis", $admin_contact, $name, $phone, $rating, $comment);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Terima kasih atas masukan Anda!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Terjadi kesalahan saat menyimpan data."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Terjadi kesalahan pada server."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Nama, No HP, dan Rating wajib diisi."]);
}

$conn->close();
?>
