<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sutanraya_v11";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "Setting up deployment/implementation tables...<br>";

// 1. DEPLOYMENT TABLE - Track when items are issued/deployed
$sql = "CREATE TABLE IF NOT EXISTS purchasing_deployments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    qty_deployed INT NOT NULL,
    deployed_to_fleet_id INT NULL,
    deployed_to_name VARCHAR(255),
    deployed_by VARCHAR(100),
    reason TEXT,
    deployment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    photo_proof VARCHAR(255),
    notes TEXT,
    status VARCHAR(50) DEFAULT 'Deployed',
    FOREIGN KEY (item_id) REFERENCES purchasing_items(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_deployments created.<br>";
else echo "Error: " . $conn->error . "<br>";

// 2. RECEIVING TABLE - Track when PO items are received with validation
$sql = "CREATE TABLE IF NOT EXISTS purchasing_receiving (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NULL,
    item_id INT NOT NULL,
    qty_received INT NOT NULL,
    received_by VARCHAR(100),
    received_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    receipt_photo VARCHAR(255),
    supplier_invoice VARCHAR(255),
    notes TEXT,
    validated BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (item_id) REFERENCES purchasing_items(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) echo "Table purchasing_receiving created.<br>";
else echo "Error: " . $conn->error . "<br>";

echo "<br>Database setup complete!<br>";
echo "You can now:<br>";
echo "- Receive items with receipt validation (purchasing_receiving)<br>";
echo "- Deploy items to vehicles (purchasing_deployments)<br>";

$conn->close();
?>
