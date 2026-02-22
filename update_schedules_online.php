<?php
// FILE: update_schedules_online.php
// Script untuk migrasi format JSON schedules lama ke format baru (dengan status hidden toggle)

header("Content-Type: text/html; charset=utf-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Hubungkan ke database (Sesuaikan path jika diperlukan di server online)
// Umumnya di online strukturnya sama dengan lokal.
$dbPath = __DIR__ . '/ops/api/db_config.php';
if (!file_exists($dbPath)) {
    // Coba path alternatif jika ditaruh di dalam folder display-v12 dll
    $dbPath = __DIR__ . '/api/db_config.php'; 
    if(!file_exists($dbPath)) {
        die("<b>Error:</b> Tidak dapat menemukan file konfigurasi database db_config.php");
    }
}
require_once $dbPath;

echo "<h2>Migrasi Format Schedules (Jadwal) Rute</h2>";
echo "<p>Memulai proses pembaruan data format `schedules` di tabel `routes`...</p>";
echo "<hr>";

// 2. Query ke tabel routes
$query = "SELECT id, origin, destination, schedules FROM routes";
$result = $conn->query($query);

if (!$result) {
    die("<b>Error Query:</b> " . $conn->error);
}

if ($result->num_rows === 0) {
    echo "Tidak ada rute di database.<br>";
    exit;
}

$countUpdated = 0;
$countSkipped = 0;
$countErrors = 0;

// 3. Looping semua rute, format ulang datanya
while ($row = $result->fetch_assoc()) {
    $routeId = $row['id'];
    $routeName = $row['origin'] . " - " . $row['destination'];
    
    // Parse schedules yang ada saat ini (JSON String -> Array PHP)
    $schedules = json_decode($row['schedules'], true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
         echo "<span style='color:red;'>[ERROR] Rute <b>{$routeId}</b>: Format schedules rusak/tidak valid. Dilewati.</span><br>";
         $countErrors++;
         continue;
    }
    
    if (!is_array($schedules)) {
        // Jika kosong atau null, ubah ke array kosong []
        $schedules = [];
    }

    $normalizedSchedules = [];
    $needsUpdate = false;

    foreach ($schedules as $sched) {
        // Cek apakah item array ini adalah STRING (format lama, contoh: "05:00")
        if (is_string($sched)) {
            // Konversi ke format Object yang baru!
            $normalizedSchedules[] = [
                "time" => $sched,
                "hidden" => false
            ];
            $needsUpdate = true;
        } 
        // Cek apakah sudah bentuk ARRAY/OBJECT namun flag hidden belum konsisten (opsional safety)
        else if (is_array($sched) && isset($sched['time'])) {
            $normalizedSchedules[] = [
                "time" => $sched['time'],
                "hidden" => isset($sched['hidden']) ? (bool)$sched['hidden'] : false
            ];
            // Jika masuk ke sini logikanya format sudah lumayan benar, tapi kita rapikan saja
            $needsUpdate = true;
        } 
        else {
             $normalizedSchedules[] = $sched;
        }
    }

    // 4. Jika ada perubahan format, update ke database!
    if ($needsUpdate || count($schedules) === 0) {
        // JSON Encode format yang sudah benar
        $newSchedulesJson = json_encode($normalizedSchedules);
        
        $updateQuery = "UPDATE routes SET schedules = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        
        if ($stmt) {
            $stmt->bind_param("ss", $newSchedulesJson, $routeId);
            if ($stmt->execute()) {
                echo "<span style='color:green;'>[SUCCESS] Rute <b>{$routeId}</b> ({$routeName}) berhasil diperbarui ke format Object.</span><br>";
                $countUpdated++;
            } else {
                echo "<span style='color:red;'>[FAILED] Gagal update rute <b>{$routeId}</b>: " . $stmt->error . "</span><br>";
                $countErrors++;
            }
            $stmt->close();
        } else {
            echo "<span style='color:red;'>[FAILED] Prepare Statement error pada rute <b>{$routeId}</b>: " . $conn->error . "</span><br>";
            $countErrors++;
        }
    } else {
        echo "<span style='color:gray;'>[SKIPPED] Rute <b>{$routeId}</b> ({$routeName}) sudah rapi, tidak perlu update.</span><br>";
        $countSkipped++;
    }
}

echo "<hr>";
echo "<h3>Selesai!</h3>";
echo "<ul>";
echo "<li><b>Diperbarui:</b> {$countUpdated} rute</li>";
echo "<li><b>Dilewati (Sudah sesuai):</b> {$countSkipped} rute</li>";
echo "<li><b>Error:</b> {$countErrors} rute</li>";
echo "</ul>";
echo "<p>Silakan HAPUS file <code>update_schedules_online.php</code> ini dari server Anda setelah dijalankan!</p>";

$conn->close();
?>
