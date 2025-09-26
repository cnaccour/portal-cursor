<?php
// Simple test of the notifications API logic
session_start();

echo "<h2>API Test</h2>";

// Check session
if (isset($_SESSION['user_id'])) {
    echo "<p>✓ User ID: " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p>✗ No user_id in session</p>";
    echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
    exit;
}

// Include the notification manager
require_once __DIR__ . '/includes/notification-manager.php';

try {
    $user_id = $_SESSION['user_id'];
    $limit = 20;
    
    echo "<p>Getting notifications for user ID: $user_id</p>";
    
    // Get notifications and unread count
    $notifications = NotificationManager::get_user_notifications($user_id, $limit);
    $unread_count = NotificationManager::get_unread_count($user_id);
    
    echo "<p>✓ Notifications found: " . count($notifications) . "</p>";
    echo "<p>✓ Unread count: $unread_count</p>";
    
    // Format response
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
    
    echo "<h3>Response:</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
