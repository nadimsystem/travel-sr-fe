<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h2>Creating Suppliers Table...</h2>";

// Create suppliers table
$sql = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    rating DECIMAL(3,2) DEFAULT 0.00,
    payment_terms VARCHAR(100),
    notes TEXT,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'suppliers' created successfully!<br><br>";
    
    // Insert sample data
    $suppliers = [
        ['SUP-001', 'Toko Maju Jaya', 'Sparepart', 'Pak Budi', '081234567890', 'budimaju@example.com', 'Jl. Raya Jakarta No. 123', 'Jakarta', 4.5, '30 hari'],
        ['SUP-002', 'CV Sentosa Parts', 'Sparepart', 'Bu Ani', '081298765432', 'sentosa.parts@example.com', 'Jl. Sudirman No. 45', 'Bandung', 4.8, 'COD'],
        ['SUP-003', 'PT Oli Nusantara', 'Oli & Kimia', 'Pak Hendra', '081212341234', 'hendra.oli@example.com', 'Jl. Basuki Rahmat 78', 'Surabaya', 4.3, '14 hari'],
        ['SUP-004', 'Bengkel Ban Jaya', 'Ban', 'Pak Agus', '081298761234', 'banjaya@example.com', 'Jl. Gatot Subroto 90', 'Jakarta', 4.6, 'COD'],
        ['SUP-005', 'Toko Elektrik Motor', 'Elektrikal', 'Bu Siti', '081234123456', 'elektrik.motor@example.com', 'Jl. Pajajaran 12', 'Bogor', 4.4, '30 hari'],
        ['SUP-006', 'CV Karoseri Indah', 'Body & Glass', 'Pak Rudi', '081245678901', 'karoseri.indah@example.com', 'Jl. Raya Bekasi Km 20', 'Bekasi', 4.7, '45 hari'],
        ['SUP-007', 'AC Solution Indonesia', 'Interior & AC', 'Pak Dedi', '081267890123', 'acsolution@example.com', 'Jl. BSD Raya 34', 'Tangerang', 4.5, '30 hari'],
        ['SUP-008', 'Hino Parts Official', 'Sparepart', 'Customer Service', '02112345678', 'parts@hino.co.id', 'Jl. TB Simatupang 88', 'Jakarta', 5.0, 'NET 60']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO suppliers (code, name, category, contact_person, phone, email, address, city, rating, payment_terms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $count = 0;
    foreach ($suppliers as $s) {
        if (!$stmt) { 
            echo "❌ Prepare failed: " . $conn->error; 
            break; 
        }
        $stmt->bind_param("ssssssssds", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9]);
        if ($stmt->execute()) {
            $count++;
        }
    }
    echo "✅ Inserted $count sample suppliers!<br><br>";
    
    // Create purchase orders tables
    echo "<h3>Creating Purchase Orders Tables...</h3>";
    
    $sql = "CREATE TABLE IF NOT EXISTS purchasing_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_number VARCHAR(50) UNIQUE NOT NULL,
        supplier_id INT,
        total_amount DECIMAL(15,2) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'Draft',
        order_date DATE,
        expected_delivery DATE,
        notes TEXT,
        created_by VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table 'purchasing_orders' created!<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS purchasing_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_id INT NOT NULL,
        item_id INT NULL,
        item_name VARCHAR(255) NOT NULL,
        qty INT NOT NULL,
        unit VARCHAR(50),
        unit_price DECIMAL(15,2),
        total_price DECIMAL(15,2),
        notes TEXT,
        FOREIGN KEY (po_id) REFERENCES purchasing_orders(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES purchasing_items(id) ON DELETE SET NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table 'purchasing_order_items' created!<br><br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ ALL DONE!</h2>";
    echo "<p>Tabel suppliers dan purchase orders sudah dibuat!</p>";
    echo "<p><a href='suppliers.php' style='display: inline-block; background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Suppliers Page</a></p>";
    
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
}

$conn->close();
?>
