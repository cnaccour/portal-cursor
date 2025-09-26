<?php
// Debug script to check notification system
session_start();

// Include the database connection
require_once __DIR__ . '/../public/includes/db.php';

echo "=== NOTIFICATION DEBUG ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection in GLOBALS\n";
    exit(1);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "✗ No user session found\n";
    echo "Available session data: " . print_r($_SESSION, true) . "\n";
    exit(1);
}

$user_id = $_SESSION['user_id'];
echo "✓ User ID: $user_id\n";

// Check if notification tables exist
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $notifications_table = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_notifications'");
    $user_notifications_table = $stmt->fetch();
    
    if ($notifications_table) {
        echo "✓ notifications table exists\n";
    } else {
        echo "✗ notifications table missing\n";
    }
    
    if ($user_notifications_table) {
        echo "✓ user_notifications table exists\n";
    } else {
        echo "✗ user_notifications table missing\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking tables: " . $e->getMessage() . "\n";
}

// Check notifications in database
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications");
    $result = $stmt->fetch();
    echo "Total notifications in database: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, type, title, target_roles, created_at FROM notifications ORDER BY created_at DESC LIMIT 5");
        $notifications = $stmt->fetchAll();
        echo "Recent notifications:\n";
        foreach ($notifications as $notif) {
            echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Roles: {$notif['target_roles']}, Created: {$notif['created_at']}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking notifications: " . $e->getMessage() . "\n";
}

// Check user_notifications
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    echo "User notifications for user $user_id: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->prepare("
            SELECT n.id, n.type, n.title, un.is_read, un.read_at 
            FROM notifications n 
            JOIN user_notifications un ON n.id = un.notification_id 
            WHERE un.user_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $user_notifications = $stmt->fetchAll();
        echo "User's notifications:\n";
        foreach ($user_notifications as $notif) {
            echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Read: " . ($notif['is_read'] ? 'Yes' : 'No') . "\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking user notifications: " . $e->getMessage() . "\n";
}

// Test the notification manager directly
echo "\n=== TESTING NOTIFICATION MANAGER ===\n";
require_once __DIR__ . '/../public/includes/notification-manager.php';

try {
    $manager = NotificationManager::getInstance();
    echo "✓ NotificationManager instance created\n";
    
    $notifications = $manager->get_user_notifications($user_id, 10);
    echo "Notifications from manager: " . count($notifications) . "\n";
    
    $unread_count = $manager->get_unread_count($user_id);
    echo "Unread count from manager: $unread_count\n";
    
} catch (Exception $e) {
    echo "✗ Error testing notification manager: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>
