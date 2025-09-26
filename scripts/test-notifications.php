<?php
/**
 * Test notification system
 */

// Load database connection
$dbIncluded = false;
$dbPaths = [
    __DIR__ . '/../public/includes/db.php', // cPanel/public entrypoint creds
    __DIR__ . '/../includes/db.php',        // local dev creds
];
foreach ($dbPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        $dbIncluded = true;
        break;
    }
}
if (!$dbIncluded) {
    fwrite(STDERR, "Unable to load database configuration.\n");
    exit(1);
}

// Load notification manager
require_once __DIR__ . '/../public/includes/notification-manager.php';

try {
    echo "Testing notification system...\n\n";
    
    // Check if notification tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $notificationsExists = $stmt->fetchColumn();
    echo "Notifications table exists: " . ($notificationsExists ? "YES" : "NO") . "\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_notifications'");
    $userNotificationsExists = $stmt->fetchColumn();
    echo "User notifications table exists: " . ($userNotificationsExists ? "YES" : "NO") . "\n\n";
    
    if (!$notificationsExists || !$userNotificationsExists) {
        echo "Creating notification tables...\n";
        require_once __DIR__ . '/setup-notifications.php';
        echo "\n";
    }
    
    // Check if there are any users with admin/manager roles
    $stmt = $pdo->query("SELECT id, name, role FROM users WHERE role IN ('admin', 'manager')");
    $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Admin/Manager users found: " . count($adminUsers) . "\n";
    foreach ($adminUsers as $user) {
        echo "  - {$user['name']} ({$user['role']})\n";
    }
    echo "\n";
    
    if (empty($adminUsers)) {
        echo "No admin/manager users found. Creating a test admin user...\n";
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, ?)");
        $stmt->execute(['Test Admin', 'admin@test.com', 'admin']);
        $adminId = $pdo->lastInsertId();
        echo "Created test admin user with ID: $adminId\n\n";
    }
    
    // Test creating a notification
    echo "Creating test notification...\n";
    $result = NotificationManager::notify_roles(['admin', 'manager'], [
        'type' => 'test',
        'title' => 'Test Notification',
        'message' => 'This is a test notification to verify the system works',
        'link_url' => '/portal/reports.php',
        'icon' => 'bell'
    ]);
    
    echo "Notification creation result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";
    
    // Check if notification was created
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications");
    $notificationCount = $stmt->fetchColumn();
    echo "Total notifications in database: $notificationCount\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_notifications");
    $userNotificationCount = $stmt->fetchColumn();
    echo "Total user notifications in database: $userNotificationCount\n";
    
    if ($notificationCount > 0) {
        echo "\nLatest notification:\n";
        $stmt = $pdo->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($latest);
    }
    
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
