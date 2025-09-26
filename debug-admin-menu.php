<?php
require __DIR__.'/includes/auth.php';
require __DIR__.'/includes/header.php';

echo "<h1>Admin Menu Debug</h1>";
echo "<p>User Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Is Admin: " . (has_role('admin') ? 'Yes' : 'No') . "</p>";

echo "<h2>File Check</h2>";
echo "<p>admin-reports-settings.php exists: " . (file_exists('admin-reports-settings.php') ? 'Yes' : 'No') . "</p>";
echo "<p>admin-reports-settings.php path: " . realpath('admin-reports-settings.php') . "</p>";

echo "<h2>Database Check</h2>";
try {
    require_once __DIR__ . '/includes/db.php';
    $stmt = $pdo->query("SHOW TABLES LIKE 'shift_report_email_settings'");
    $table_exists = $stmt->fetch();
    echo "<p>shift_report_email_settings table exists: " . ($table_exists ? 'Yes' : 'No') . "</p>";
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Navigation Test</h2>";
echo '<a href="admin-reports-settings.php">Direct Link to Reports Settings</a>';
?>
