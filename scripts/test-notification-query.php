<?php
// Test the exact query that the notification manager uses
require_once __DIR__ . '/../public/includes/db.php';

echo "=== TESTING NOTIFICATION MANAGER QUERY ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

$user_id = 1;
$limit = 20;

echo "Testing with user ID: $user_id, limit: $limit\n\n";

// Test the exact query from notification manager
try {
    $query = "
        SELECT n.*, un.is_read, un.read_at
        FROM notifications n
        JOIN user_notifications un ON n.id = un.notification_id
        WHERE un.user_id = ? AND n.is_active = 1
          AND (n.expires_at IS NULL OR n.expires_at > NOW())
        ORDER BY n.created_at DESC
        LIMIT " . (int)$limit;
    
    echo "Query: $query\n\n";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    
    echo "Results: " . count($notifications) . " notifications found\n";
    
    if (count($notifications) > 0) {
        echo "\nNotifications:\n";
        foreach ($notifications as $notif) {
            echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Active: {$notif['is_active']}, Expires: {$notif['expires_at']}, Read: {$notif['is_read']}\n";
        }
    } else {
        echo "\nNo notifications found. Let's check why...\n";
        
        // Check each condition separately
        echo "\n=== DEBUGGING QUERY CONDITIONS ===\n";
        
        // Check user_notifications
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $count = $stmt->fetchColumn();
        echo "User notifications for user $user_id: $count\n";
        
        // Check notifications with is_active = 1
        $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_active = 1");
        $count = $stmt->fetchColumn();
        echo "Active notifications: $count\n";
        
        // Check notifications without expiration
        $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE expires_at IS NULL OR expires_at > NOW()");
        $count = $stmt->fetchColumn();
        echo "Non-expired notifications: $count\n";
        
        // Check the join
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notifications n
            JOIN user_notifications un ON n.id = un.notification_id
            WHERE un.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $count = $stmt->fetchColumn();
        echo "Joined notifications for user $user_id: $count\n";
        
        // Check if is_active column exists and has the right values
        $stmt = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'is_active'");
        $column = $stmt->fetch();
        if ($column) {
            echo "is_active column exists: {$column['Type']}, Default: {$column['Default']}\n";
            
            $stmt = $pdo->query("SELECT id, is_active FROM notifications LIMIT 5");
            $notifs = $stmt->fetchAll();
            echo "Sample is_active values:\n";
            foreach ($notifs as $notif) {
                echo "  - ID: {$notif['id']}, is_active: {$notif['is_active']}\n";
            }
        } else {
            echo "✗ is_active column does not exist!\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
