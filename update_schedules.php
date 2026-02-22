<?php
require 'api/db_config.php';

$res = $conn->query("SELECT id, schedules FROM routes");
$count = 0;

while($row = $res->fetch_assoc()) {
    $schedules = json_decode($row['schedules'], true);
    if(is_array($schedules)) {
        $normalized = [];
        $needsUpdate = false;
        
        foreach($schedules as $s) {
            // Check if it's still a simple string like "05:00"
            if(is_string($s)) {
                $normalized[] = ["time" => $s, "hidden" => false];
                $needsUpdate = true;
            } else {
                $normalized[] = $s;
            }
        }
        
        if($needsUpdate) {
            $newSchedules = json_encode($normalized);
            $stmt = $conn->prepare("UPDATE routes SET schedules = ? WHERE id = ?");
            $stmt->bind_param("ss", $newSchedules, $row['id']);
            $stmt->execute();
            $count++;
            echo "Updated route: " . $row['id'] . "\n";
        }
    }
}

echo "Total updated: " . $count . "\n";
?>
