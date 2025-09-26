<?php
// Test the notifications API with a specific user ID
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/../public/includes/notification-manager.php';

echo "=== TESTING NOTIFICATIONS API ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

// Test with user ID 1 (the user who has notifications)
$test_user_id = 1;
echo "Testing with user ID: $test_user_id\n\n";

try {
    $manager = NotificationManager::getInstance();
    echo "✓ NotificationManager instance created\n";
    
    // Test getting notifications
    $notifications = $manager->get_user_notifications($test_user_id, 10);
    echo "Notifications found: " . count($notifications) . "\n";
    
    if (count($notifications) > 0) {
        echo "Sample notification:\n";
        $first = $notifications[0];
        echo "  - ID: {$first['id']}\n";
        echo "  - Type: {$first['type']}\n";
        echo "  - Title: {$first['title']}\n";
        echo "  - Message: {$first['message']}\n";
        echo "  - Is Read: " . ($first['is_read'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test getting unread count
    $unread_count = $manager->get_unread_count($test_user_id);
    echo "Unread count: $unread_count\n";
    
    // Test the API response format
    echo "\n=== API RESPONSE FORMAT ===\n";
    $response = [
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => array_map(function($notification) {
            return [
                'id' => $notification['id'],
                'type' => $notification['type'] ?? 'general',
                'title' => htmlspecialchars($notification['title'] ?? ''),
                'message' => htmlspecialchars($notification['message'] ?? ''),
                'link_url' => $notification['link_url'] ?? null,
                'icon' => $notification['icon'] ?? 'bell',
                'is_read' => $notification['is_read'] ?? false,
                'created_at' => $notification['created_at'],
                'expires_at' => $notification['expires_at'] ?? null
            ];
        }, $notifications)
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "✗ Error testing notification manager: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
