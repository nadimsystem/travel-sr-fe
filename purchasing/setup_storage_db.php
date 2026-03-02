<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create Rooms Table
$sql = "CREATE TABLE IF NOT EXISTS purchasing_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'purchasing_rooms' created successfully.<br>";
} else {
    echo "Error creating table 'purchasing_rooms': " . $conn->error . "<br>";
}

// 2. Create Cabinets Table
$sql = "CREATE TABLE IF NOT EXISTS purchasing_cabinets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES purchasing_rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'purchasing_cabinets' created successfully.<br>";
} else {
    echo "Error creating table 'purchasing_cabinets': " . $conn->error . "<br>";
}

// 3. Create Racks Table
$sql = "CREATE TABLE IF NOT EXISTS purchasing_racks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabinet_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cabinet_id) REFERENCES purchasing_cabinets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'purchasing_racks' created successfully.<br>";
} else {
    echo "Error creating table 'purchasing_racks': " . $conn->error . "<br>";
}

// 4. Alter Items Table to add rack_id
// Check if column exists first to avoid error
$check = $conn->query("SHOW COLUMNS FROM purchasing_items LIKE 'rack_id'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE purchasing_items ADD COLUMN rack_id INT NULL, ADD FOREIGN KEY (rack_id) REFERENCES purchasing_racks(id) ON DELETE SET NULL;";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'rack_id' added to 'purchasing_items'.<br>";
    } else {
        echo "Error adding column 'rack_id': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'rack_id' already exists in 'purchasing_items'.<br>";
}

// 5. Seed Initial Data (Optional - specific request "update based on places")
// Let's create a default structure: Gudang Utama -> Lemari A -> Rak 1
$conn->query("INSERT IGNORE INTO purchasing_rooms (id, name, notes) VALUES (1, 'Gudang Utama', 'Penyimpanan utama')");
$conn->query("INSERT IGNORE INTO purchasing_cabinets (id, room_id, name, notes) VALUES (1, 1, 'Lemari Besi A', 'Sparepart kecil')");
$conn->query("INSERT IGNORE INTO purchasing_racks (id, cabinet_id, name, notes) VALUES (1, 1, 'Rak 1 (Atas)', 'Filter dan Busi')");

echo "<br>Setup Completed!";
$conn->close();
?>
