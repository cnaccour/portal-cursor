<?php
/**
 * Setup notification tables if they don't exist
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

try {
    // Check if notifications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $notificationsExists = $stmt->fetchColumn();
    
    if (!$notificationsExists) {
        echo "Creating notifications table...\n";
        $pdo->exec("
            CREATE TABLE notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT,
                link_url VARCHAR(255),
                icon VARCHAR(50) DEFAULT 'bell',
                target_roles JSON,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_active TINYINT(1) DEFAULT 1,
                INDEX idx_active_notifications (is_active, created_at),
                INDEX idx_notification_type (type),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ notifications table created\n";
    } else {
        echo "✓ notifications table already exists\n";
    }
    
    // Check if user_notifications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_notifications'");
    $userNotificationsExists = $stmt->fetchColumn();
    
    if (!$userNotificationsExists) {
        echo "Creating user_notifications table...\n";
        $pdo->exec("
            CREATE TABLE user_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                notification_id INT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                read_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_notification (user_id, notification_id),
                INDEX idx_user_unread (user_id, is_read, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ user_notifications table created\n";
    } else {
        echo "✓ user_notifications table already exists\n";
    }
    
    echo "\nNotification system setup complete!\n";
    
} catch (Exception $e) {
    fwrite(STDERR, "Error setting up notifications: " . $e->getMessage() . "\n");
    exit(1);
}
