<?php
// Test the notification manager directly
session_start();
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/../public/includes/notification-manager.php';

echo "=== TESTING NOTIFICATION MANAGER DIRECTLY ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

$user_id = 1;
echo "Testing with user ID: $user_id\n\n";

try {
    // Test the notification manager
    $manager = NotificationManager::getInstance();
    echo "✓ NotificationManager instance created\n";
    
    // Test getting notifications
    echo "Calling get_user_notifications($user_id, 10)...\n";
    $notifications = $manager->get_user_notifications($user_id, 10);
    echo "Result: " . count($notifications) . " notifications\n";
    
    if (count($notifications) > 0) {
        echo "First notification:\n";
        $first = $notifications[0];
        echo "  - ID: {$first['id']}\n";
        echo "  - Type: {$first['type']}\n";
        echo "  - Title: {$first['title']}\n";
        echo "  - Message: {$first['message']}\n";
        echo "  - Is Read: " . ($first['is_read'] ? 'Yes' : 'No') . "\n";
    }
    
    // Test getting unread count
    echo "\nCalling get_unread_count($user_id)...\n";
    $unread_count = $manager->get_unread_count($user_id);
    echo "Result: $unread_count unread notifications\n";
    
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
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
