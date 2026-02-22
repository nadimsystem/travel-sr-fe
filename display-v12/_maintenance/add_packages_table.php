<?php
// Migration script to create packages table
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'sutanraya_v11';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `packages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `senderName` varchar(100) DEFAULT NULL,
  `senderPhone` varchar(50) DEFAULT NULL,
  `receiverName` varchar(100) DEFAULT NULL,
  `receiverPhone` varchar(50) DEFAULT NULL,
  `itemDescription` varchar(255) DEFAULT NULL,
  `itemType` varchar(50) DEFAULT NULL, -- Surat/Dokumen, Kardus, Big Size
  `category` varchar(50) DEFAULT NULL, -- Pool to Pool, Door to Door
  `route` varchar(100) DEFAULT NULL, -- e.g. Padang - Bukittinggi
  `price` double DEFAULT 0,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `paymentStatus` varchar(50) DEFAULT 'Menunggu Pembayaran',
  `status` varchar(50) DEFAULT 'Pending', -- Pending, Dikirim, Sampai di Pool, Diambil/Diterima
  `pickupAddress` text DEFAULT NULL,
  `dropoffAddress` text DEFAULT NULL,
  `bookingDate` date DEFAULT NULL,
  `createdAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'packages' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
echo "Migration complete.\n";
?>
