<?php
// Simple script to check if notification tables exist and have data
require_once __DIR__ . '/../public/includes/db.php';

echo "=== NOTIFICATION TABLES CHECK ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

// Check if notification tables exist
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $notifications_table = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_notifications'");
    $user_notifications_table = $stmt->fetch();
    
    if ($notifications_table) {
        echo "✓ notifications table exists\n";
        
        // Check notifications count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications");
        $result = $stmt->fetch();
        echo "Total notifications: " . $result['count'] . "\n";
        
        if ($result['count'] > 0) {
            $stmt = $pdo->query("SELECT id, type, title, target_roles, created_at FROM notifications ORDER BY created_at DESC LIMIT 3");
            $notifications = $stmt->fetchAll();
            echo "Recent notifications:\n";
            foreach ($notifications as $notif) {
                echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Roles: {$notif['target_roles']}\n";
            }
        }
    } else {
        echo "✗ notifications table missing\n";
    }
    
    if ($user_notifications_table) {
        echo "✓ user_notifications table exists\n";
        
        // Check user_notifications count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_notifications");
        $result = $stmt->fetch();
        echo "Total user_notifications: " . $result['count'] . "\n";
        
        if ($result['count'] > 0) {
            $stmt = $pdo->query("SELECT user_id, notification_id, is_read FROM user_notifications LIMIT 3");
            $user_notifications = $stmt->fetchAll();
            echo "Sample user_notifications:\n";
            foreach ($user_notifications as $un) {
                echo "  - User: {$un['user_id']}, Notification: {$un['notification_id']}, Read: " . ($un['is_read'] ? 'Yes' : 'No') . "\n";
            }
        }
    } else {
        echo "✗ user_notifications table missing\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
?>
