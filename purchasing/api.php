<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    // --- MASTER DATA: FLEET (ARMADA) ---
    case 'get_fleet':
        $sql = "SELECT id, name, plate, status FROM fleet ORDER BY name ASC";
        $result = $conn->query($sql);
        $fleet = [];
        if($result) {
            while ($row = $result->fetch_assoc()) {
                $fleet[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $fleet]);
        break;

    // --- MASTER DATA: ITEMS ---
    case 'get_items':
        $category = isset($_GET['category']) ? $_GET['category'] : 'All';
        $sql = "SELECT i.*, 
                r.name as rack_name, 
                c.name as cabinet_name, 
                rm.name as room_name 
                FROM purchasing_items i 
                LEFT JOIN purchasing_racks r ON i.rack_id = r.id 
                LEFT JOIN purchasing_cabinets c ON r.cabinet_id = c.id 
                LEFT JOIN purchasing_rooms rm ON c.room_id = rm.id 
                ORDER BY i.name ASC";
        
        $result = $conn->query($sql);
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $items]);
        break;

    // --- MASTER DATA: ASSETS ---
    case 'get_assets':
        $sql = "SELECT * FROM purchasing_assets ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $assets = [];
        if ($result){
            while ($row = $result->fetch_assoc()) {
                $assets[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $assets]);
        break;

    // --- PURCHASING: REQUESTS ---
    case 'get_requests':
        $sql = "SELECT pr.*, 
                (SELECT COUNT(*) FROM purchasing_request_items WHERE request_id = pr.id) as item_count 
                FROM purchasing_requests pr 
                ORDER BY pr.request_date DESC";
        $result = $conn->query($sql);
        $requests = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $requests]);
        break;
    
    case 'get_request':
        $id = (int)$_GET['id'];
        $sql = "SELECT * FROM purchasing_requests WHERE id=$id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $request = $result->fetch_assoc();
            
            // Get items
            $sql_items = "SELECT * FROM purchasing_request_items WHERE request_id=$id";
            $items_result = $conn->query($sql_items);
            $items = [];
            while($row = $items_result->fetch_assoc()) {
                $items[] = $row;
            }
            $request['items'] = $items;
            
            echo json_encode(["status" => "success", "data" => $request]);
        } else {
            echo json_encode(["status" => "error", "message" => "Request not found"]);
        }
        break;
    
    case 'create_request':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $requester_id = 1; // Hardcoded user for MVP
        $notes = $conn->real_escape_string($data['notes']);
        
        $sql = "INSERT INTO purchasing_requests (requester_id, notes, status) VALUES ($requester_id, '$notes', 'Pending')";
        if ($conn->query($sql) === TRUE) {
            $pr_id = $conn->insert_id;
            
            foreach ($data['items'] as $item) {
                $item_id = (isset($item['id']) && is_numeric($item['id'])) ? $item['id'] : 'NULL';
                $item_name = $conn->real_escape_string($item['name']);
                $qty = (int)$item['qty'];
                $urgency = $conn->real_escape_string($item['urgency']);
                $bus_id = isset($item['bus_id']) ? $conn->real_escape_string($item['bus_id']) : '';
                
                $sql_item = "INSERT INTO purchasing_request_items (request_id, item_id, item_name, qty, urgency, bus_id) VALUES ($pr_id, $item_id, '$item_name', $qty, '$urgency', '$bus_id')";
                $conn->query($sql_item);
            }
            echo json_encode(["status" => "success", "message" => "Request created", "id" => $pr_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'update_request_status':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        $status = $conn->real_escape_string($data['status']);
        
        $sql = "UPDATE purchasing_requests SET status='$status' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Request status updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- INVENTORY STATS ---
    case 'get_inventory_stats':
        $stats = ['total_items' => 0, 'asset_value' => 0, 'low_stock_count' => 0];
        
        // Count Items
        $res = $conn->query("SELECT COUNT(*) as c FROM purchasing_items");
        if($res) $stats['total_items'] = $res->fetch_assoc()['c'];

        // Sum Asset Value
        $res2 = $conn->query("SELECT SUM(value) as v FROM purchasing_assets");
        if($res2) $stats['asset_value'] = (float)$res2->fetch_assoc()['v'];
        
        // Count Low Stock Items
        $res3 = $conn->query("SELECT COUNT(*) as c FROM purchasing_items WHERE stock < min_stock");
        if($res3) $stats['low_stock_count'] = $res3->fetch_assoc()['c'];

        echo json_encode(["status" => "success", "data" => $stats]);
        break;
        
    // --- SUPPLIERS ---
    case 'get_suppliers':
        $sql = "SELECT * FROM suppliers ORDER BY name ASC";
        $result = $conn->query($sql);
        $suppliers = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $suppliers]);
        break;
    
    case 'create_supplier':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $code = $conn->real_escape_string($data['code']);
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $contact_person = isset($data['contact_person']) ? $conn->real_escape_string($data['contact_person']) : '';
        $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
        $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
        $address = isset($data['address']) ? $conn->real_escape_string($data['address']) : '';
        $city = isset($data['city']) ? $conn->real_escape_string($data['city']) : '';
        $rating = isset($data['rating']) ? (float)$data['rating'] : 0;
        $payment_terms = isset($data['payment_terms']) ? $conn->real_escape_string($data['payment_terms']) : '';
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        
        $sql = "INSERT INTO suppliers (code, name, category, contact_person, phone, email, address, city, rating, payment_terms, notes) 
                VALUES ('$code', '$name', '$category', '$contact_person', '$phone', '$email', '$address', '$city', $rating, '$payment_terms', '$notes')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Supplier created", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'update_supplier':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $contact_person = isset($data['contact_person']) ? $conn->real_escape_string($data['contact_person']) : '';
        $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
        $email = isset($data['email']) ? $conn->real_escape_string($data['email']) : '';
        $address = isset($data['address']) ? $conn->real_escape_string($data['address']) : '';
        $city = isset($data['city']) ? $conn->real_escape_string($data['city']) : '';
        $rating = isset($data['rating']) ? (float)$data['rating'] : 0;
        $payment_terms = isset($data['payment_terms']) ? $conn->real_escape_string($data['payment_terms']) : '';
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'Active';
        
        $sql = "UPDATE suppliers SET name='$name', category='$category', contact_person='$contact_person', 
                phone='$phone', email='$email', address='$address', city='$city', rating=$rating, 
                payment_terms='$payment_terms', notes='$notes', status='$status' WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Supplier updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'delete_supplier':
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM suppliers WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Supplier deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- PURCHASE ORDERS ---
    case 'get_purchase_orders':
        $sql = "SELECT po.*, s.name as supplier_name 
                FROM purchasing_orders po 
                LEFT JOIN suppliers s ON po.supplier_id = s.id 
                ORDER BY po.created_at DESC";
        $result = $conn->query($sql);
        $orders = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $orders]);
        break;
    
    case 'get_purchase_order':
        $id = (int)$_GET['id'];
        $sql = "SELECT po.*, s.name as supplier_name 
                FROM purchasing_orders po 
                LEFT JOIN suppliers s ON po.supplier_id = s.id 
                WHERE po.id=$id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $order = $result->fetch_assoc();
            
            // Get items
            $sql_items = "SELECT * FROM purchasing_order_items WHERE po_id=$id";
            $items_result = $conn->query($sql_items);
            $items = [];
            while($row = $items_result->fetch_assoc()) {
                $items[] = $row;
            }
            $order['items'] = $items;
            
            echo json_encode(["status" => "success", "data" => $order]);
        } else {
            echo json_encode(["status" => "error", "message" => "Order not found"]);
        }
        break;
    
    case 'create_purchase_order':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $po_number = $conn->real_escape_string($data['po_number']);
        $supplier_id = isset($data['supplier_id']) ? (int)$data['supplier_id'] : 'NULL';
        $total_amount = isset($data['total_amount']) ? (float)$data['total_amount'] : 0;
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'Draft';
        $order_date = isset($data['order_date']) ? $conn->real_escape_string($data['order_date']) : date('Y-m-d');
        $expected_delivery = isset($data['expected_delivery']) ? $conn->real_escape_string($data['expected_delivery']) : 'NULL';
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        $created_by = isset($data['created_by']) ? $conn->real_escape_string($data['created_by']) : 'Admin';
        
        $sql = "INSERT INTO purchasing_orders (po_number, supplier_id, total_amount, status, order_date, expected_delivery, notes, created_by) 
                VALUES ('$po_number', $supplier_id, $total_amount, '$status', '$order_date', " . 
                ($expected_delivery === 'NULL' ? 'NULL' : "'$expected_delivery'") . ", '$notes', '$created_by')";
        
        if ($conn->query($sql) === TRUE) {
            $po_id = $conn->insert_id;
            
            // Insert items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $item_id = isset($item['item_id']) && is_numeric($item['item_id']) ? (int)$item['item_id'] : 'NULL';
                    $item_name = $conn->real_escape_string($item['item_name']);
                    $qty = (int)$item['qty'];
                    $unit = isset($item['unit']) ? $conn->real_escape_string($item['unit']) : '';
                    $unit_price = isset($item['unit_price']) ? (float)$item['unit_price'] : 0;
                    $total_price = isset($item['total_price']) ? (float)$item['total_price'] : ($qty * $unit_price);
                    $item_notes = isset($item['notes']) ? $conn->real_escape_string($item['notes']) : '';
                    
                    $sql_item = "INSERT INTO purchasing_order_items (po_id, item_id, item_name, qty, unit, unit_price, total_price, notes) 
                                VALUES ($po_id, $item_id, '$item_name', $qty, '$unit', $unit_price, $total_price, '$item_notes')";
                    $conn->query($sql_item);
                }
            }
            
            echo json_encode(["status" => "success", "message" => "Purchase order created", "id" => $po_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'update_purchase_order':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        
        $status = $conn->real_escape_string($data['status']);
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        
        $sql = "UPDATE purchasing_orders SET status='$status', notes='$notes' WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Purchase order updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'delete_purchase_order':
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM purchasing_orders WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Purchase order deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- ITEMS MANAGEMENT ---
    case 'create_item':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $code = $conn->real_escape_string($data['code']);
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        $min_stock = isset($data['min_stock']) ? (int)$data['min_stock'] : 5;
        $unit = isset($data['unit']) ? $conn->real_escape_string($data['unit']) : '';
        $last_price = isset($data['last_price']) ? (float)$data['last_price'] : 0;
        $compatibility = isset($data['compatibility']) ? $conn->real_escape_string($data['compatibility']) : '';
        $location = isset($data['location']) ? $conn->real_escape_string($data['location']) : '';
        $rack_id = isset($data['rack_id']) && is_numeric($data['rack_id']) ? (int)$data['rack_id'] : 'NULL';
        
        $sql = "INSERT INTO purchasing_items (code, name, category, stock, min_stock, unit, last_price, compatibility, location, rack_id) 
                VALUES ('$code', '$name', '$category', $stock, $min_stock, '$unit', $last_price, '$compatibility', '$location', $rack_id)";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Item created", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'update_item':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        $min_stock = isset($data['min_stock']) ? (int)$data['min_stock'] : 5;
        $unit = isset($data['unit']) ? $conn->real_escape_string($data['unit']) : '';
        $last_price = isset($data['last_price']) ? (float)$data['last_price'] : 0;
        $compatibility = isset($data['compatibility']) ? $conn->real_escape_string($data['compatibility']) : '';
        $location = isset($data['location']) ? $conn->real_escape_string($data['location']) : '';
        $rack_id = isset($data['rack_id']) && is_numeric($data['rack_id']) ? (int)$data['rack_id'] : 'NULL';
        
        $sql = "UPDATE purchasing_items SET name='$name', category='$category', stock=$stock, min_stock=$min_stock, 
                unit='$unit', last_price=$last_price, compatibility='$compatibility', location='$location', rack_id=$rack_id WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Item updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'delete_item':
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM purchasing_items WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Item deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- ASSETS MANAGEMENT ---
    case 'create_asset':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $code = $conn->real_escape_string($data['code']);
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $value = isset($data['value']) ? (float)$data['value'] : 0;
        $location = isset($data['location']) ? $conn->real_escape_string($data['location']) : '';
        $pic = isset($data['pic']) ? $conn->real_escape_string($data['pic']) : '';
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'Active';
        $purchase_date = isset($data['purchase_date']) ? $conn->real_escape_string($data['purchase_date']) : 'NULL';
        
        $sql = "INSERT INTO purchasing_assets (code, name, category, value, location, pic, status, purchase_date) 
                VALUES ('$code', '$name', '$category', $value, '$location', '$pic', '$status', " . 
                ($purchase_date === 'NULL' ? 'NULL' : "'$purchase_date'") . ")";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Asset created", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'update_asset':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int)$data['id'];
        
        $name = $conn->real_escape_string($data['name']);
        $category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';
        $value = isset($data['value']) ? (float)$data['value'] : 0;
        $location = isset($data['location']) ? $conn->real_escape_string($data['location']) : '';
        $pic = isset($data['pic']) ? $conn->real_escape_string($data['pic']) : '';
        $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'Active';
        
        $sql = "UPDATE purchasing_assets SET name='$name', category='$category', value=$value, 
                location='$location', pic='$pic', status='$status' WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Asset updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'delete_asset':
        $id = (int)$_GET['id'];
        $sql = "DELETE FROM purchasing_assets WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Asset deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- DEPLOYMENTS ---
    case 'get_deployments':
        $sql = "SELECT d.*, i.name as item_name, i.code as item_code 
                FROM purchasing_deployments d 
                LEFT JOIN purchasing_items i ON d.item_id = i.id 
                ORDER BY d.deployment_date DESC";
        $result = $conn->query($sql);
        $deployments = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $deployments[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $deployments]);
        break;
    
    case 'create_deployment':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $item_id = (int)$data['item_id'];
        $qty_deployed = (int)$data['qty_deployed'];
        $deployed_to_fleet_id = isset($data['deployed_to_fleet_id']) ? (int)$data['deployed_to_fleet_id'] : 'NULL';
        $deployed_to_name = $conn->real_escape_string($data['deployed_to_name']);
        $deployed_by = isset($data['deployed_by']) ? $conn->real_escape_string($data['deployed_by']) : 'Admin';
        $reason = isset($data['reason']) ? $conn->real_escape_string($data['reason']) : '';
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        
        $sql = "INSERT INTO purchasing_deployments (item_id, qty_deployed, deployed_to_fleet_id, deployed_to_name, deployed_by, reason, notes) 
                VALUES ($item_id, $qty_deployed, $deployed_to_fleet_id, '$deployed_to_name', '$deployed_by', '$reason', '$notes')";
        
        if ($conn->query($sql) === TRUE) {
            // Update stock
            $conn->query("UPDATE purchasing_items SET stock = stock - $qty_deployed WHERE id = $item_id");
            echo json_encode(["status" => "success", "message" => "Deployment recorded", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;
    
    case 'get_receiving':
        $sql = "SELECT r.*, i.name as item_name, i.code as item_code 
                FROM purchasing_receiving r 
                LEFT JOIN purchasing_items i ON r.item_id = i.id 
                ORDER BY r.received_date DESC";
        $result = $conn->query($sql);
        $receiving = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $receiving[] = $row;
            }
        }
        echo json_encode(["status" => "success", "data" => $receiving]);
        break;
    
    case 'create_receiving':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $po_id = isset($data['po_id']) ? (int)$data['po_id'] : 'NULL';
        $item_id = (int)$data['item_id'];
        $qty_received = (int)$data['qty_received'];
        $received_by = isset($data['received_by']) ? $conn->real_escape_string($data['received_by']) : 'Admin';
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        
        $sql = "INSERT INTO purchasing_receiving (po_id, item_id, qty_received, received_by, notes) 
                VALUES ($po_id, $item_id, $qty_received, '$received_by', '$notes')";
        
        if ($conn->query($sql) === TRUE) {
            // Update stock
            $conn->query("UPDATE purchasing_items SET stock = stock + $qty_received WHERE id = $item_id");
            echo json_encode(["status" => "success", "message" => "Receipt recorded", "id" => $conn->insert_id]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- STORAGE MANAGEMENT ---
    
    // ROOMS
    // ROOMS
    case 'get_rooms':
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM purchasing_cabinets c WHERE c.room_id = r.id) as cabinet_count,
                (SELECT COUNT(*) FROM purchasing_items i 
                 JOIN purchasing_racks rack ON i.rack_id = rack.id 
                 JOIN purchasing_cabinets c ON rack.cabinet_id = c.id 
                 WHERE c.room_id = r.id) as item_count
                FROM purchasing_rooms r ORDER BY r.name ASC";
        $result = $conn->query($sql);
        $data = [];
        if($result) while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'create_room':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $conn->real_escape_string($data['name']);
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        $sql = "INSERT INTO purchasing_rooms (name, notes) VALUES ('$name', '$notes')";
        if($conn->query($sql) === TRUE) echo json_encode(["status" => "success", "message" => "Room created", "id" => $conn->insert_id]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;

    case 'delete_room':
        $id = (int)$_GET['id'];
        if($conn->query("DELETE FROM purchasing_rooms WHERE id=$id") === TRUE) echo json_encode(["status" => "success", "message" => "Room deleted"]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;

    // CABINETS
    case 'get_cabinets':
        $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM purchasing_racks r WHERE r.cabinet_id = c.id) as rack_count,
                (SELECT COUNT(*) FROM purchasing_items i 
                 JOIN purchasing_racks r ON i.rack_id = r.id 
                 WHERE r.cabinet_id = c.id) as item_count
                FROM purchasing_cabinets c " . ($room_id ? "WHERE c.room_id=$room_id " : "") . "ORDER BY c.name ASC";
        $result = $conn->query($sql);
        $data = [];
        if($result) while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'create_cabinet':
        $data = json_decode(file_get_contents("php://input"), true);
        $room_id = (int)$data['room_id'];
        $name = $conn->real_escape_string($data['name']);
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        $sql = "INSERT INTO purchasing_cabinets (room_id, name, notes) VALUES ($room_id, '$name', '$notes')";
        if($conn->query($sql) === TRUE) echo json_encode(["status" => "success", "message" => "Cabinet created", "id" => $conn->insert_id]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;
        
    case 'delete_cabinet':
        $id = (int)$_GET['id'];
        if($conn->query("DELETE FROM purchasing_cabinets WHERE id=$id") === TRUE) echo json_encode(["status" => "success", "message" => "Cabinet deleted"]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;

    // RACKS
    case 'get_racks':
        $cabinet_id = isset($_GET['cabinet_id']) ? (int)$_GET['cabinet_id'] : 0;
        $sql = "SELECT r.*,
                (SELECT COUNT(*) FROM purchasing_items i WHERE i.rack_id = r.id) as item_count
                FROM purchasing_racks r " . ($cabinet_id ? "WHERE r.cabinet_id=$cabinet_id " : "") . "ORDER BY r.name ASC";
        $result = $conn->query($sql);
        $data = [];
        if($result) while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'create_rack':
        $data = json_decode(file_get_contents("php://input"), true);
        $cabinet_id = (int)$data['cabinet_id'];
        $name = $conn->real_escape_string($data['name']);
        $notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : '';
        $sql = "INSERT INTO purchasing_racks (cabinet_id, name, notes) VALUES ($cabinet_id, '$name', '$notes')";
        if($conn->query($sql) === TRUE) echo json_encode(["status" => "success", "message" => "Rack created", "id" => $conn->insert_id]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;

    case 'delete_rack':
        $id = (int)$_GET['id'];
        if($conn->query("DELETE FROM purchasing_racks WHERE id=$id") === TRUE) echo json_encode(["status" => "success", "message" => "Rack deleted"]);
        else echo json_encode(["status" => "error", "message" => $conn->error]);
        break;

    case 'get_all_racks_flat':
        $sql = "SELECT r.id, r.name, c.name as cabinet_name, rm.name as room_name,
                CONCAT(rm.name, ' > ', c.name, ' > ', r.name) as full_path
                FROM purchasing_racks r
                JOIN purchasing_cabinets c ON r.cabinet_id = c.id
                JOIN purchasing_rooms rm ON c.room_id = rm.id
                ORDER BY rm.name, c.name, r.name";
        $result = $conn->query($sql);
        $data = [];
        if($result) while($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    // RACK ITEMS MANAGEMENT
    case 'get_items_by_rack':
        $rack_id = (int)$_GET['rack_id'];
        // Get items in this rack
        $sql = "SELECT id, name, code, stock, unit FROM purchasing_items WHERE rack_id=$rack_id ORDER BY name ASC";
        $result = $conn->query($sql);
        $items = [];
        if($result) while($row = $result->fetch_assoc()) $items[] = $row;
        echo json_encode(["status" => "success", "data" => $items]);
        break;

    case 'move_items_to_rack':
        $data = json_decode(file_get_contents("php://input"), true);
        $rack_id = (int)$data['rack_id'];
        $item_ids = $data['item_ids']; // Array of IDs
        
        if(empty($item_ids)) {
            echo json_encode(["status" => "error", "message" => "No items selected"]);
            exit;
        }

        // Get full location path string for text fallback
        $loc_sql = "SELECT r.name as rack, c.name as cabinet, rm.name as room 
                    FROM purchasing_racks r 
                    JOIN purchasing_cabinets c ON r.cabinet_id = c.id 
                    JOIN purchasing_rooms rm ON c.room_id = rm.id 
                    WHERE r.id = $rack_id";
        $loc_res = $conn->query($loc_sql);
        $loc_row = $loc_res->fetch_assoc();
        $location_text = $loc_row['room'] . " > " . $loc_row['cabinet'] . " > " . $loc_row['rack'];
        $location_text = $conn->real_escape_string($location_text);

        $ids_string = implode(",", array_map('intval', $item_ids));
        
        $sql = "UPDATE purchasing_items SET rack_id=$rack_id, location='$location_text' WHERE id IN ($ids_string)";
        
        if($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Items moved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
        break;

    // --- DASHBOARD ---
    case 'get_dashboard_stats':
        $stats = [];

        // 1. Pending Requests
        $res = $conn->query("SELECT COUNT(*) as count FROM purchasing_requests WHERE status = 'Pending'");
        $stats['pending_requests'] = $res ? $res->fetch_assoc()['count'] : 0;

        // 2. Active POs (Sent or Partial)
        $res = $conn->query("SELECT COUNT(*) as count FROM purchasing_orders WHERE status IN ('Sent', 'Partial')");
        $stats['active_pos'] = $res ? $res->fetch_assoc()['count'] : 0;

        // 3. Low Stock Items
        $res = $conn->query("SELECT COUNT(*) as count FROM purchasing_items WHERE stock <= min_stock");
        $stats['low_stock_count'] = $res ? $res->fetch_assoc()['count'] : 0;

        // 4. Total Asset Value
        $res = $conn->query("SELECT SUM(value) as total_value FROM purchasing_assets WHERE status = 'Active'");
        $stats['total_asset_value'] = $res ? $res->fetch_assoc()['total_value'] : 0;

        // 5. Counts
        $res = $conn->query("SELECT COUNT(*) as count FROM purchasing_items");
        $stats['total_items'] = $res ? $res->fetch_assoc()['count'] : 0;

        $res = $conn->query("SELECT COUNT(*) as count FROM purchasing_racks");
        $stats['total_racks'] = $res ? $res->fetch_assoc()['count'] : 0;

        // 6. Recent Low Stock List (Top 5)
        $res = $conn->query("SELECT name, stock, min_stock, unit FROM purchasing_items WHERE stock <= min_stock ORDER BY stock ASC LIMIT 5");
        $low_stock_list = [];
        if($res) {
            while($row = $res->fetch_assoc()) {
                $low_stock_list[] = $row;
            }
        }
        $stats['low_stock_items'] = $low_stock_list;

        echo json_encode(["status" => "success", "data" => $stats]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
