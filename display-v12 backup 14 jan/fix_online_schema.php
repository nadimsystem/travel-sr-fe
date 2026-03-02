<?php
// fix_online_schema.php
// Script untuk memperbaiki database online yang kurang kolom 'unitNumber'
include 'base.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function addColumn($conn, $table, $col, $spec) {
    echo "Checking table <b>$table</b> for column <b>$col</b>...<br>";
    $check = $conn->query("SHOW COLUMNS FROM $table LIKE '$col'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE $table ADD COLUMN $col $spec")) {
            echo "<b style='color:green'>[BERHASIL] Menambahkan kolom $col ke tabel $table.</b><br>";
        } else {
            echo "<b style='color:red'>[GAGAL] " . $conn->error . "</b><br>";
        }
    } else {
        echo "<span style='color:blue'>[OK] Kolom $col sudah ada.</span><br>";
    }
}

echo "<h2>Perbaikan Database Sutan Raya</h2>";
echo "<p>Sedang memeriksa struktur database...</p><hr>";

// 1. Tambah unitNumber ke tabel trips
addColumn($conn, 'trips', 'unitNumber', "INT DEFAULT 1");

// 2. Tambah unitNumber ke tabel schedule_defaults
addColumn($conn, 'schedule_defaults', 'unitNumber', "INT DEFAULT 1");

// 3. Tambah batchNumber ke bookings (jaga-jaga jika belum ada)
addColumn($conn, 'bookings', 'batchNumber', "INT DEFAULT 1");

echo "<hr><p>Selesai. Silakan coba kembali fitur Dispatcher.</p>";
?>
