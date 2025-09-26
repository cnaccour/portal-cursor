<?php
// Simple test to check what's causing the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: Basic PHP working\n";

try {
    echo "Test 2: Including auth.php\n";
    require __DIR__.'/../includes/auth.php';
    echo "Test 3: Auth included successfully\n";
    
    echo "Test 4: Including shift-report-manager.php\n";
    require __DIR__.'/../includes/shift-report-manager.php';
    echo "Test 5: Shift report manager included successfully\n";
    
    echo "Test 6: Including notification-manager.php\n";
    require __DIR__.'/../includes/notification-manager.php';
    echo "Test 7: Notification manager included successfully\n";
    
    echo "Test 8: Including shift-report-email-manager.php\n";
    require __DIR__.'/../includes/shift-report-email-manager.php';
    echo "Test 9: Shift report email manager included successfully\n";
    
    echo "Test 10: All includes successful\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
