<?php
// Script to fix user_notifications table by linking existing notifications to users
require_once __DIR__ . '/../public/includes/db.php';

echo "=== FIXING USER NOTIFICATIONS ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

try {
    // Get all users with admin or manager role
    $stmt = $pdo->query("SELECT id, name, role FROM users WHERE role IN ('admin', 'manager')");
    $admin_users = $stmt->fetchAll();
    echo "Found " . count($admin_users) . " admin/manager users:\n";
    foreach ($admin_users as $user) {
        echo "  - User ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}\n";
    }
    echo "\n";
    
    // Get all notifications targeting admin/manager roles
    $stmt = $pdo->query("SELECT id, target_roles FROM notifications WHERE target_roles LIKE '%admin%' OR target_roles LIKE '%manager%'");
    $notifications = $stmt->fetchAll();
    echo "Found " . count($notifications) . " notifications targeting admin/manager roles:\n";
    foreach ($notifications as $notif) {
        echo "  - Notification ID: {$notif['id']}, Roles: {$notif['target_roles']}\n";
    }
    echo "\n";
    
    // Link notifications to users
    $linked_count = 0;
    foreach ($admin_users as $user) {
        $user_id = $user['id'];
        $user_role = $user['role'];
        
        foreach ($notifications as $notif) {
            $notification_id = $notif['id'];
            $target_roles = json_decode($notif['target_roles'], true);
            
            // Check if this notification targets this user's role
            if (in_array($user_role, $target_roles)) {
                // Check if this link already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND notification_id = ?");
                $stmt->execute([$user_id, $notification_id]);
                $exists = $stmt->fetchColumn();
                
                if (!$exists) {
                    // Create the link
                    $stmt = $pdo->prepare("INSERT INTO user_notifications (user_id, notification_id, is_read, created_at) VALUES (?, ?, 0, NOW())");
                    $stmt->execute([$user_id, $notification_id]);
                    $linked_count++;
                    echo "✓ Linked notification {$notification_id} to user {$user_id} ({$user['name']})\n";
                } else {
                    echo "- Notification {$notification_id} already linked to user {$user_id}\n";
                }
            }
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Linked $linked_count new user-notification relationships\n";
    
    // Verify the fix
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_notifications");
    $total_links = $stmt->fetchColumn();
    echo "Total user_notifications entries: $total_links\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";
?>
