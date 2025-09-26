<?php
require __DIR__.'/includes/auth.php';

// Debug info
echo "<h1>Debug Admin Reports Settings</h1>";
echo "<p>User Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
echo "<p>Is Admin: " . (($_SESSION['role'] ?? '') === 'admin' ? 'Yes' : 'No') . "</p>";

require_once __DIR__ . '/includes/db.php';

// Check if table exists
try {
    $result = $pdo->query("SHOW TABLES LIKE 'shift_report_email_settings'");
    $table_exists = $result->rowCount() > 0;
    echo "<p>Table 'shift_report_email_settings' exists: " . ($table_exists ? 'Yes' : 'No') . "</p>";
    
    if ($table_exists) {
        // Check table structure
        $result = $pdo->query("DESCRIBE shift_report_email_settings");
        $columns = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Table Structure:</h2>";
        echo "<pre>";
        foreach ($columns as $column) {
            echo $column['Field'] . " - " . $column['Type'] . "\n";
        }
        echo "</pre>";
        
        // Check existing data
        $result = $pdo->query("SELECT * FROM shift_report_email_settings");
        $settings = $result->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Existing Data (" . count($settings) . " rows):</h2>";
        echo "<pre>";
        print_r($settings);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Check predefined locations
$predefined_locations = [
    'Land O\' Lakes',
    'Odessa', 
    'Citrus Park',
    'Tampa Bay',
    'Corporate Office'
];

echo "<h2>Predefined Locations:</h2>";
echo "<pre>";
print_r($predefined_locations);
echo "</pre>";

echo "<h2>File Info:</h2>";
echo "<p>admin-reports-settings.php exists: " . (file_exists('admin-reports-settings.php') ? 'Yes' : 'No') . "</p>";
echo "<p>admin-reports-settings.php modified: " . date('Y-m-d H:i:s', filemtime('admin-reports-settings.php')) . "</p>";
?>
