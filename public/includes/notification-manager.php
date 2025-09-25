<?php
/**
 * Notification Manager Class
 * Handles creation, delivery, and management of role-based notifications
 */

class NotificationManager {
    private static $instance = null;
    private $use_mock = true; // Switch to false when real database is available
    
    private function __construct() {
        // Check if we should use real database or mock
        $this->use_mock = !$this->isDatabaseAvailable();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new NotificationManager();
        }
        return self::$instance;
    }
    
    /**
     * Check if real database is available
     */
    private function isDatabaseAvailable() {
        // Check for database connection and required tables
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            if (!$pdo) {
                return false;
            }
            
            // Check if required tables exist and are accessible
            $stmt = $pdo->prepare("SELECT 1 FROM notifications LIMIT 1");
            $stmt->execute();
            $notificationsExists = true;
            
            $stmt = $pdo->prepare("SELECT 1 FROM user_notifications LIMIT 1");
            $stmt->execute();
            $userNotificationsExists = true;
            
            return $notificationsExists && $userNotificationsExists;
        } catch (Exception $e) {
            error_log('Database availability check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notify users by their roles
     * @param array $roles Array of role names ['admin', 'manager', etc.]
     * @param array $notification_data Notification content
     */
    public static function notify_roles(array $roles, array $notification_data) {
        $instance = self::getInstance();
        return $instance->_notify_roles($roles, $notification_data);
    }
    
    /**
     * Notify specific users by their IDs
     * @param array $user_ids Array of user IDs
     * @param array $notification_data Notification content
     */
    public static function notify_users(array $user_ids, array $notification_data) {
        $instance = self::getInstance();
        return $instance->_notify_users($user_ids, $notification_data);
    }
    
    /**
     * Notify all active users
     * @param array $notification_data Notification content
     */
    public static function notify_all(array $notification_data) {
        $instance = self::getInstance();
        return $instance->_notify_all($notification_data);
    }
    
    /**
     * Get notifications for a specific user
     * @param int $user_id User ID
     * @param int $limit Maximum number of notifications to return
     * @return array
     */
    public static function get_user_notifications(int $user_id, int $limit = 20) {
        $instance = self::getInstance();
        return $instance->_get_user_notifications($user_id, $limit);
    }
    
    /**
     * Get unread notification count for a user
     * @param int $user_id User ID
     * @return int
     */
    public static function get_unread_count(int $user_id) {
        $instance = self::getInstance();
        return $instance->_get_unread_count($user_id);
    }
    
    /**
     * Mark notification as read for a user
     * @param int $user_id User ID
     * @param int $notification_id Notification ID
     * @return bool
     */
    public static function mark_as_read(int $user_id, $notification_id) {
        $instance = self::getInstance();
        return $instance->_mark_as_read($user_id, $notification_id);
    }
    
    /**
     * Mark all notifications as read for a user
     * @param int $user_id User ID
     * @return bool
     */
    public static function mark_all_read(int $user_id) {
        $instance = self::getInstance();
        return $instance->_mark_all_read($user_id);
    }
    
    // Internal implementation methods
    
    private function _notify_roles(array $roles, array $notification_data) {
        // Validate and sanitize link_url for security
        if (isset($notification_data['link_url'])) {
            $notification_data['link_url'] = $this->validateInternalLink($notification_data['link_url']);
        }
        
        if ($this->use_mock) {
            return $this->mockNotifyRoles($roles, $notification_data);
        }
        
        return $this->databaseNotifyRoles($roles, $notification_data);
    }
    
    private function _notify_users(array $user_ids, array $notification_data) {
        // Validate and sanitize link_url for security
        if (isset($notification_data['link_url'])) {
            $notification_data['link_url'] = $this->validateInternalLink($notification_data['link_url']);
        }
        
        if ($this->use_mock) {
            return $this->mockNotifyUsers($user_ids, $notification_data);
        }
        
        return $this->databaseNotifyUsers($user_ids, $notification_data);
    }
    
    private function _notify_all(array $notification_data) {
        // Validate and sanitize link_url for security
        if (isset($notification_data['link_url'])) {
            $notification_data['link_url'] = $this->validateInternalLink($notification_data['link_url']);
        }
        
        if ($this->use_mock) {
            return $this->mockNotifyAll($notification_data);
        }
        
        return $this->databaseNotifyAll($notification_data);
    }
    
    private function _get_user_notifications(int $user_id, int $limit) {
        if ($this->use_mock) {
            return $this->mockGetUserNotifications($user_id, $limit);
        }
        
        return $this->databaseGetUserNotifications($user_id, $limit);
    }
    
    private function _get_unread_count(int $user_id) {
        if ($this->use_mock) {
            return $this->mockGetUnreadCount($user_id);
        }
        
        return $this->databaseGetUnreadCount($user_id);
    }
    
    private function _mark_as_read(int $user_id, $notification_id) {
        // Convert to int if numeric for database operations
        if (is_string($notification_id) && is_numeric($notification_id)) {
            $notification_id = (int)$notification_id;
        }
        if ($this->use_mock) {
            return $this->mockMarkAsRead($user_id, $notification_id);
        }
        
        return $this->databaseMarkAsRead($user_id, $notification_id);
    }
    
    private function _mark_all_read(int $user_id) {
        if ($this->use_mock) {
            return $this->mockMarkAllRead($user_id);
        }
        
        return $this->databaseMarkAllRead($user_id);
    }
    
    // Mock implementations for development
    
    private function mockNotifyRoles(array $roles, array $notification_data) {
        $notification_data['target_roles'] = $roles;
        $notification_data['id'] = uniqid();
        $notification_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->saveToMockStorage($notification_data);
        return true;
    }
    
    private function mockNotifyUsers(array $user_ids, array $notification_data) {
        $notification_data['target_users'] = $user_ids;
        $notification_data['id'] = uniqid();
        $notification_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->saveToMockStorage($notification_data);
        return true;
    }
    
    private function mockNotifyAll(array $notification_data) {
        $notification_data['target_all'] = true;
        $notification_data['id'] = uniqid();
        $notification_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->saveToMockStorage($notification_data);
        return true;
    }
    
    private function mockGetUserNotifications(int $user_id, int $limit) {
        $user_role = $this->getUserRole($user_id);
        $notifications = $this->loadFromMockStorage();
        $user_notifications = [];
        
        foreach ($notifications as $notification) {
            $should_include = false;
            
            // Check if notification targets this user
            if (isset($notification['target_all']) && $notification['target_all']) {
                $should_include = true;
            } elseif (isset($notification['target_users']) && in_array($user_id, $notification['target_users'])) {
                $should_include = true;
            } elseif (isset($notification['target_roles']) && in_array($user_role, $notification['target_roles'])) {
                $should_include = true;
            }
            
            if ($should_include) {
                $notification['is_read'] = $this->mockIsRead($user_id, $notification['id']);
                $user_notifications[] = $notification;
            }
        }
        
        // Sort by created_at desc and limit
        usort($user_notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($user_notifications, 0, $limit);
    }
    
    private function mockGetUnreadCount(int $user_id) {
        $notifications = $this->mockGetUserNotifications($user_id, 100);
        return count(array_filter($notifications, function($n) {
            return !$n['is_read'];
        }));
    }
    
    private function mockMarkAsRead(int $user_id, $notification_id) {
        $read_data = $this->loadMockReadData();
        $read_data[$user_id][$notification_id] = date('Y-m-d H:i:s');
        $this->saveMockReadData($read_data);
        return true;
    }
    
    private function mockMarkAllRead(int $user_id) {
        $notifications = $this->mockGetUserNotifications($user_id, 100);
        $read_data = $this->loadMockReadData();
        
        foreach ($notifications as $notification) {
            $read_data[$user_id][$notification['id']] = date('Y-m-d H:i:s');
        }
        
        $this->saveMockReadData($read_data);
        return true;
    }
    
    // Helper methods for mock storage
    
    private function saveToMockStorage($notification_data) {
        $storage_dir = __DIR__ . '/../storage/notifications';
        if (!is_dir($storage_dir)) {
            mkdir($storage_dir, 0755, true);
        }
        
        $notifications = $this->loadFromMockStorage();
        $notifications[] = $notification_data;
        
        file_put_contents($storage_dir . '/notifications.json', json_encode($notifications, JSON_PRETTY_PRINT));
    }
    
    private function loadFromMockStorage() {
        $storage_file = __DIR__ . '/../storage/notifications/notifications.json';
        if (!file_exists($storage_file)) {
            return [];
        }
        
        $content = file_get_contents($storage_file);
        return json_decode($content, true) ?: [];
    }
    
    private function loadMockReadData() {
        $storage_file = __DIR__ . '/../storage/notifications/read_status.json';
        if (!file_exists($storage_file)) {
            return [];
        }
        
        $content = file_get_contents($storage_file);
        return json_decode($content, true) ?: [];
    }
    
    private function saveMockReadData($read_data) {
        $storage_dir = __DIR__ . '/../storage/notifications';
        if (!is_dir($storage_dir)) {
            mkdir($storage_dir, 0755, true);
        }
        
        file_put_contents($storage_dir . '/read_status.json', json_encode($read_data, JSON_PRETTY_PRINT));
    }
    
    private function mockIsRead(int $user_id, $notification_id) {
        $read_data = $this->loadMockReadData();
        return isset($read_data[$user_id][$notification_id]);
    }
    
    private function getUserRole(int $user_id) {
        // Get user role from session or mock data
        if (isset($_SESSION['role'])) {
            return $_SESSION['role'];
        }
        
        // Fallback to mock users
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as $user) {
            if ($user['id'] == $user_id) {
                return $user['role'];
            }
        }
        
        return 'viewer'; // Default role
    }
    
    // Security validation for link URLs
    
    private function validateInternalLink($link_url) {
        if (empty($link_url)) {
            return null;
        }
        
        // Only allow relative paths starting with '/'
        if (!preg_match('/^\/[^\/]/', $link_url)) {
            error_log('Invalid link_url blocked: ' . $link_url);
            return null;
        }
        
        // Block any protocol schemes or external references
        if (preg_match('/^[a-z]+:/i', $link_url) || strpos($link_url, '//') !== false) {
            error_log('External link_url blocked: ' . $link_url);
            return null;
        }
        
        return $link_url;
    }
    
    // Database implementations (for production)
    
    private function databaseNotifyRoles(array $roles, array $notification_data) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Insert notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (type, title, message, link_url, icon, target_roles, created_by, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $notification_data['type'] ?? 'general',
                $notification_data['title'],
                $notification_data['message'] ?? '',
                $notification_data['link_url'] ?? null,
                $notification_data['icon'] ?? 'bell',
                json_encode($roles),
                $_SESSION['user_id'] ?? null,
                $notification_data['expires_at'] ?? null
            ]);
            
            $notification_id = $pdo->lastInsertId();
            
            // Get users with these roles and create user_notifications
            $placeholders = str_repeat('?,', count($roles) - 1) . '?';
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role IN ($placeholders)");
            $stmt->execute($roles);
            $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($user_ids)) {
                $this->createUserNotifications($notification_id, $user_ids);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Database notify_roles error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseNotifyUsers(array $user_ids, array $notification_data) {
        if (empty($user_ids)) {
            error_log('notify_users called with empty user_ids array');
            return false;
        }
        
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Insert notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (type, title, message, link_url, icon, target_roles, created_by, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $notification_data['type'] ?? 'general',
                $notification_data['title'],
                $notification_data['message'] ?? '',
                $notification_data['link_url'] ?? null,
                $notification_data['icon'] ?? 'bell',
                null, // No role targeting for specific users
                $_SESSION['user_id'] ?? null,
                $notification_data['expires_at'] ?? null
            ]);
            
            $notification_id = $pdo->lastInsertId();
            $this->createUserNotifications($notification_id, $user_ids);
            
            return true;
        } catch (Exception $e) {
            error_log('Database notify_users error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseNotifyAll(array $notification_data) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Insert notification
            $stmt = $pdo->prepare("
                INSERT INTO notifications (type, title, message, link_url, icon, target_roles, created_by, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $notification_data['type'] ?? 'general',
                $notification_data['title'],
                $notification_data['message'] ?? '',
                $notification_data['link_url'] ?? null,
                $notification_data['icon'] ?? 'bell',
                json_encode(['admin', 'manager', 'support', 'staff', 'viewer']), // All roles
                $_SESSION['user_id'] ?? null,
                $notification_data['expires_at'] ?? null
            ]);
            
            $notification_id = $pdo->lastInsertId();
            
            // Get all users
            $stmt = $pdo->query("SELECT id FROM users");
            $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($user_ids)) {
                $this->createUserNotifications($notification_id, $user_ids);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Database notify_all error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseGetUserNotifications(int $user_id, int $limit) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT n.*, un.is_read, un.read_at
                FROM notifications n
                JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.user_id = ? AND n.is_active = 1
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
                ORDER BY n.created_at DESC
                LIMIT " . (int)$limit . "
            ");
            
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Database get_user_notifications error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function databaseGetUnreadCount(int $user_id) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM notifications n
                JOIN user_notifications un ON n.id = un.notification_id
                WHERE un.user_id = ? AND un.is_read = 0 AND n.is_active = 1
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
            ");
            
            $stmt->execute([$user_id]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('Database get_unread_count error: ' . $e->getMessage());
            return 0;
        }
    }
    
    private function databaseMarkAsRead(int $user_id, int $notification_id) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                UPDATE user_notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND notification_id = ?
            ");
            
            return $stmt->execute([$user_id, $notification_id]);
        } catch (Exception $e) {
            error_log('Database mark_as_read error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseMarkAllRead(int $user_id) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                UPDATE user_notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0
            ");
            
            return $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log('Database mark_all_read error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Helper method for creating user notification records
    private function createUserNotifications(int $notification_id, array $user_ids) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $placeholders = str_repeat('(?, ?),', count($user_ids));
            $placeholders = rtrim($placeholders, ',');
            
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO user_notifications (notification_id, user_id)
                VALUES $placeholders
            ");
            
            $values = [];
            foreach ($user_ids as $user_id) {
                $values[] = $notification_id;
                $values[] = $user_id;
            }
            
            return $stmt->execute($values);
        } catch (Exception $e) {
            error_log('Create user_notifications error: ' . $e->getMessage());
            return false;
        }
    }
}