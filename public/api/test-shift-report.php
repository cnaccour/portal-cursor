<?php
// Simple test to check what's causing the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include files that start sessions FIRST, before any output
    require __DIR__.'/../includes/auth.php';
    require __DIR__.'/../includes/shift-report-manager.php';
    require __DIR__.'/../includes/notification-manager.php';
    require __DIR__.'/../includes/shift-report-email-manager.php';
    
    // Now safe to output
    echo "Test 1: Basic PHP working\n";
    echo "Test 2: Auth included successfully\n";
    echo "Test 3: Shift report manager included successfully\n";
    echo "Test 4: Notification manager included successfully\n";
    echo "Test 5: Shift report email manager included successfully\n";
    echo "Test 6: All includes successful\n";
    
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
