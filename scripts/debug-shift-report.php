<?php
/**
 * Debug script to check shift report data
 */

// Load database connection
$dbIncluded = false;
$dbPaths = [
    __DIR__ . '/../public/includes/db.php', // cPanel/public entrypoint creds
    __DIR__ . '/../includes/db.php',        // local dev creds
];
foreach ($dbPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $dbIncluded = true;
        break;
    }
}
if (!$dbIncluded) {
    fwrite(STDERR, "Unable to load database configuration.\n");
    exit(1);
}

try {
    // Get the latest shift report
    $stmt = $pdo->query("SELECT id, checklist_data, refunds_data FROM shift_reports ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        echo "Latest Shift Report ID: " . $row['id'] . "\n\n";
        
        echo "Checklist Data (raw JSON):\n";
        echo $row['checklist_data'] . "\n\n";
        
        echo "Checklist Data (decoded):\n";
        $checklist = json_decode($row['checklist_data'], true);
        print_r($checklist);
        echo "\n";
        
        echo "Refunds Data (raw JSON):\n";
        echo $row['refunds_data'] . "\n\n";
        
        echo "Refunds Data (decoded):\n";
        $refunds = json_decode($row['refunds_data'], true);
        print_r($refunds);
        echo "\n";
        
    } else {
        echo "No shift reports found.\n";
    }
    
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
