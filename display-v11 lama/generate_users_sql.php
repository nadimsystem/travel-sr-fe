<?php
// Script to generate SQL for adding users
$password = '10101010';
$hash = password_hash($password, PASSWORD_DEFAULT);

$users = [
    ['username' => 'irma',  'name' => 'Irma',  'role' => 'Admin Keuangan'],
    ['username' => 'bella', 'name' => 'Bella', 'role' => 'Admin Bus'],
    ['username' => 'havid', 'name' => 'Havid', 'role' => 'Admin Travel Padang'],
    ['username' => 'salma', 'name' => 'Salma', 'role' => 'Admin Travel Padang'],
    ['username' => 'fanny', 'name' => 'Fanny', 'role' => 'Admin Travel Bukittinggi'],
    ['username' => 'ervan', 'name' => 'Ervan', 'role' => 'Admin Travel Payakumbuh'],
];

$sql = "-- SQL untuk menambahkan user baru (Password: 10101010)\n";
$sql .= "-- Struktur tabel sesuai instruksi: id, username, password, name, role\n";
$sql .= "-- Silakan copy dan jalankan query di bawah ini\n\n";

foreach ($users as $index => $u) {
    // Generate ID unik (timestamp + index to avoid collision in same second) . suffix
    // Using simple timestamp + random suffix to ensure uniqueness and reasonably short ID
    // User image showed 10 digit ID (seconds). 
    // api.php uses time() . rand(100,999) ~ 13 digits. We will stick to 13 digits to be safe with api.php logic.
    $id = (time() + $index) . rand(100,999); 
    
    $username = $u['username'];
    $name = $u['name'];
    $role = $u['role'];
    
    $sql .= "INSERT INTO users (id, username, password, name, role) VALUES ('$id', '$username', '$hash', '$name', '$role');\n";
}

echo $sql;
?>
