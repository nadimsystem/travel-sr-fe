<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Connected to $database.<br>";

// 1. ITEMS TABLE
$sql = "CREATE TABLE IF NOT EXISTS purchasing_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    unit VARCHAR(50),
    last_price DECIMAL(15,2),
    compatibility VARCHAR(255),
    location VARCHAR(100),
    condition_status VARCHAR(50) DEFAULT 'Baru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_items created.<br>";
else echo "Error creating table purchasing_items: " . $conn->error . "<br>";

// 2. ASSETS TABLE
$sql = "CREATE TABLE IF NOT EXISTS purchasing_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    value DECIMAL(15,2),
    location VARCHAR(100),
    pic VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Active',
    purchase_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_assets created.<br>";
else echo "Error creating table purchasing_assets: " . $conn->error . "<br>";


// 3. REQUESTS TABLES
$sql = "CREATE TABLE IF NOT EXISTS purchasing_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'Pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_requests created.<br>";

$sql = "CREATE TABLE IF NOT EXISTS purchasing_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT,
    item_id INT NULL,
    item_name VARCHAR(255),
    qty INT,
    unit VARCHAR(50),
    urgency VARCHAR(50),
    bus_id VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (request_id) REFERENCES purchasing_requests(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_request_items created.<br>";


// 4. SEED ITEMS (The Massive Catalog)
$items = [
    // ENGINE
    ['ENG-001', 'Filter Oli (Oil Filter) DX', 'Sparepart', 15, 'Pcs', 120000, 'Hino R260 / RK8', 'Rak A-01'],
    ['ENG-002', 'Filter Solar Bawah (Fuel Filter)', 'Sparepart', 20, 'Pcs', 85000, 'Hino R260', 'Rak A-02'],
    ['ENG-003', 'Filter Udara (Air Cleaner)', 'Sparepart', 8, 'Pcs', 450000, 'Hino R260', 'Rak A-03'],
    ['ENG-004', 'V-Belt AC (Tali Kipas)', 'Sparepart', 10, 'Pcs', 150000, 'Denso System', 'Rak A-04'],
    ['ENG-005', 'Packing Set Overhaul (Full Set)', 'Sparepart', 2, 'Set', 3500000, 'Hino J08E', 'Gudang Mesin'],
    ['ENG-006', 'Turbocharger Assembly', 'Sparepart', 1, 'Unit', 15000000, 'Hino R260', 'Gudang Mesin'],
    ['ENG-007', 'Alternator 24V 60A', 'Elektrikal', 2, 'Unit', 2800000, 'Universal Bus', 'Rak E-10'],
    ['ENG-008', 'Motor Starter (Dinamo Starter)', 'Elektrikal', 2, 'Unit', 3200000, 'Hino RK8', 'Rak E-11'],
    
    // CHASSIS
    ['SUS-001', 'Air Suspension Bellow (Balon Udara)', 'Kaki-Kaki & Sasis', 6, 'Pcs', 2500000, 'Hino R260 / Merc', 'Gudang K-01'],
    ['SUS-002', 'Shock Absorber Depan', 'Kaki-Kaki & Sasis', 4, 'Pcs', 1200000, 'Hino RK8', 'Rak K-02'],
    ['BRK-001', 'Kampas Rem Depan (Brake Lining)', 'Kaki-Kaki & Sasis', 12, 'Set', 850000, 'Hino R260', 'Rak K-03'],
    ['BRK-002', 'Tromol Rem (Brake Drum)', 'Kaki-Kaki & Sasis', 2, 'Pcs', 1800000, 'Hino R260', 'Gudang K-02'],
    ['WHL-001', 'Baut Roda (Wheel Stud) Belakang', 'Kaki-Kaki & Sasis', 50, 'Pcs', 45000, 'Universal Hino', 'Rak K-05'],
    ['WHL-002', 'Bearing Roda Luar', 'Kaki-Kaki & Sasis', 8, 'Pcs', 350000, 'Hino RK8', 'Rak K-06'],

    // BODY & ELECTRIC
    ['LGT-001', 'Headlamp Jetbus 3+ (Kanan)', 'Body & Glass', 1, 'Pcs', 3500000, 'Adiputro Jetbus 3', 'Gudang B-01'],
    ['LGT-002', 'Stop Lamp LED Running (Belakang)', 'Body & Glass', 2, 'Set', 1200000, 'Adiputro Jetbus 3', 'Rak B-02'],
    ['EL-001', 'Relay 24V 5 Pin', 'Elektrikal', 30, 'Pcs', 35000, 'Universal', 'Rak E-01'],
    ['EL-002', 'Sikring Tancep (Fuse) 10A-30A', 'Elektrikal', 100, 'Pcs', 2000, 'Universal', 'Rak E-02'],
    ['EL-003', 'Aki N200 (Battery 200Ah)', 'Elektrikal', 4, 'Pcs', 3800000, 'Bus Besar', 'Gudang Aki'],

    // INTERIOR
    ['INT-005', 'Jok Rimba Kencana (Captain Seat)', 'Interior & AC', 0, 'Pcs', 4500000, 'Hiace Luxury', 'Indent'],
    ['INT-006', 'Karpet Lantai Vinyl Kayu (Per Meter)', 'Interior & AC', 15, 'Meter', 120000, 'Universal', 'Gudang I-01'],
    ['AC-005', 'Kompresor AC Denso Bus Big', 'Interior & AC', 1, 'Unit', 8500000, 'Big Bus', 'Gudang AC'],
    ['AC-006', 'Freon R134a (Tabung 13kg)', 'Oli & Kimia', 5, 'Tabung', 1800000, 'Universal AC', 'Gudang Kimia'],

    // OIL & CHEM
    ['OIL-010', 'Oli Mesin Meditran SX 15W-40', 'Oli & Kimia', 40, 'Galon', 320000, 'Diesel Engine', 'Gudang Oli'],
    ['OIL-011', 'Oli Transmisi Rored 90', 'Oli & Kimia', 20, 'Galon', 280000, 'Manual Trans', 'Gudang Oli'],
    ['CHE-001', 'AdBlue (Cairan Exhaust Diesel) 10L', 'Oli & Kimia', 30, 'Jerigen', 150000, 'Euro 4', 'Gudang O-01'],

    // TIRES
    ['TYR-001', 'Ban Bridgestone 11R22.5 (Bus)', 'Ban', 8, 'Pcs', 4200000, 'Big Bus', 'Gudang Ban'],
    ['TYR-002', 'Ban Dunlop 195/R15 (Hiace)', 'Ban', 12, 'Pcs', 1100000, 'Toyota Hiace', 'Gudang Ban'],

    // TOOLS
    ['TOL-001', 'Dongkrak Botol 20 Ton', 'Tools', 4, 'Pcs', 850000, 'Universal', 'Gudang Tools'],
    ['SAF-001', 'APAR 3kg Dry Chemical Powder', 'Safety & Emergency', 12, 'Tabung', 350000, 'Universal', 'Rak S-01']
];

$stmt = $conn->prepare("INSERT IGNORE INTO purchasing_items (code, name, category, stock, unit, last_price, compatibility, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($items as $i) {
    if (!$stmt) { echo "Prepare failed: " . $conn->error; break; }
    $stmt->bind_param("sssissss", $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6], $i[7]);
    $stmt->execute();
}
echo "Seeded Items.<br>";


// 5. SEED ASSETS
$assets = [
    ['AST-VH-001', 'Toyota Hiace Luxury B 7789 SDA', 'Kendaraan', 650000000, 'Pool Utama', 'Budi (Driver)', 'Active'],
    ['AST-VH-002', 'Hino R260 Jetbus 3+ HDD', 'Kendaraan', 1850000000, 'Pool Utama', 'Operational Mgr', 'Maintenance'],
    ['AST-EQ-010', 'Genset Silent 50kVA', 'Mesin', 120000000, 'Gudang Teknik', 'Kepala Mekanik', 'Active'],
    ['AST-IT-005', 'Laptop Admin Purchasing', 'Elektronik', 15000000, 'Kantor Lt. 2', 'Admin Purchasing', 'Active'],
    ['AST-PR-001', 'Gedung Workshop & Mess', 'Properti', 2500000000, 'Jl. Raya Bogor', 'Direktur', 'Active'],
    ['AST-EQ-015', 'Hydraulic Bus Lift', 'Mesin', 85000000, 'Workshop', 'Kepala Mekanik', 'Broken']
];

$stmt = $conn->prepare("INSERT IGNORE INTO purchasing_assets (code, name, category, value, location, pic, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($assets as $a) {
    if (!$stmt) { echo "Prepare failed: " . $conn->error; break; }
    $stmt->bind_param("sssdsss", $a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6]);
    $stmt->execute();
}
echo "Seeded Assets.<br>";


// 6. SUPPLIERS TABLE
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
if ($conn->query($sql) === TRUE) echo "Table suppliers created.<br>";
else echo "Error creating table suppliers: " . $conn->error . "<br>";

// 7. SEED SUPPLIERS
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
foreach ($suppliers as $s) {
    if (!$stmt) { echo "Prepare failed: " . $conn->error; break; }
    $stmt->bind_param("ssssssssds", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9]);
    $stmt->execute();
}
echo "Seeded Suppliers.<br>";

// 8. PURCHASE ORDERS TABLE
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
if ($conn->query($sql) === TRUE) echo "Table purchasing_orders created.<br>";
else echo "Error creating table purchasing_orders: " . $conn->error . "<br>";

// 9. PURCHASE ORDER ITEMS TABLE
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
if ($conn->query($sql) === TRUE) echo "Table purchasing_order_items created.<br>";
else echo "Error creating table purchasing_order_items: " . $conn->error . "<br>";

echo "<br><strong>Setup completed successfully!</strong><br>";
echo "All tables have been created and seeded with sample data.<br>";
echo "<a href='index.php'>Go to Purchasing Dashboard</a>";

$conn->close();
?>
