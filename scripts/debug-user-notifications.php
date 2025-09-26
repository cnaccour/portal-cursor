<?php
// Debug script to check user_notifications table
require_once __DIR__ . '/../public/includes/db.php';

echo "=== USER NOTIFICATIONS DEBUG ===\n";

// Check if we have a database connection
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
    echo "✓ Database connection available\n";
    $pdo = $GLOBALS['pdo'];
} else {
    echo "✗ No database connection\n";
    exit(1);
}

$user_id = 1;
echo "Checking notifications for user ID: $user_id\n\n";

// Check all notifications
try {
    $stmt = $pdo->query("SELECT id, type, title, target_roles, created_at FROM notifications ORDER BY created_at DESC");
    $all_notifications = $stmt->fetchAll();
    echo "All notifications in database (" . count($all_notifications) . "):\n";
    foreach ($all_notifications as $notif) {
        echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Roles: {$notif['target_roles']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error getting all notifications: " . $e->getMessage() . "\n";
}

// Check user_notifications for user 1
try {
    $stmt = $pdo->prepare("SELECT notification_id, is_read, read_at FROM user_notifications WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_notifications = $stmt->fetchAll();
    echo "User notifications for user $user_id (" . count($user_notifications) . "):\n";
    foreach ($user_notifications as $un) {
        echo "  - Notification ID: {$un['notification_id']}, Read: " . ($un['is_read'] ? 'Yes' : 'No') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error getting user notifications: " . $e->getMessage() . "\n";
}

// Check if user 1 has admin/manager role
try {
    $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        echo "User $user_id details:\n";
        echo "  - Name: {$user['name']}\n";
        echo "  - Role: {$user['role']}\n";
    } else {
        echo "✗ User $user_id not found in users table\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error getting user details: " . $e->getMessage() . "\n";
}

// Check which notifications should be visible to user 1 (admin role)
try {
    $stmt = $pdo->query("SELECT id, type, title, target_roles FROM notifications WHERE target_roles LIKE '%admin%' OR target_roles LIKE '%manager%'");
    $targeted_notifications = $stmt->fetchAll();
    echo "Notifications targeting admin/manager roles (" . count($targeted_notifications) . "):\n";
    foreach ($targeted_notifications as $notif) {
        echo "  - ID: {$notif['id']}, Type: {$notif['type']}, Title: {$notif['title']}, Roles: {$notif['target_roles']}\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Error getting targeted notifications: " . $e->getMessage() . "\n";
}

echo "=== DEBUG COMPLETE ===\n";
?>
