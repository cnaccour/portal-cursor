<?php
// Simple script to check session without requiring login
session_start();

echo "<h2>Session Information</h2>";

if (isset($_SESSION['user_id'])) {
    echo "<p><strong>User ID:</strong> " . $_SESSION['user_id'] . "</p>";
    echo "<p><strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
    echo "<p><strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";
    
    echo "<h3>Test Notifications for User ID " . $_SESSION['user_id'] . ":</h3>";
    
    // Include the notification manager
    require_once __DIR__ . '/includes/notification-manager.php';
    
    try {
        $manager = NotificationManager::getInstance();
        $notifications = $manager->get_user_notifications($_SESSION['user_id'], 10);
        $unread_count = $manager->get_unread_count($_SESSION['user_id']);
        
        echo "<p><strong>Notifications found:</strong> " . count($notifications) . "</p>";
        echo "<p><strong>Unread count:</strong> $unread_count</p>";
        
        if (count($notifications) > 0) {
            echo "<h4>Recent Notifications:</h4>";
            echo "<ul>";
            foreach ($notifications as $notif) {
                echo "<li>ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Read: " . ($notif['is_read'] ? 'Yes' : 'No') . "</li>";
            }
            echo "</ul>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'><strong>No user session found!</strong></p>";
    echo "<p>You need to log in first.</p>";
    echo "<p><a href='/portal/login.php'>Go to Login Page</a></p>";
}

echo "<h3>Full Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session ID:</h3>";
echo "<p>" . session_id() . "</p>";
?>
