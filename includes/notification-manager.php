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
        // For now, always use mock in development
        // Change this logic when deploying to production
        return false;
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
    public static function mark_as_read(int $user_id, int $notification_id) {
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
        if ($this->use_mock) {
            return $this->mockNotifyRoles($roles, $notification_data);
        }
        
        // Real database implementation would go here
        return $this->databaseNotifyRoles($roles, $notification_data);
    }
    
    private function _notify_users(array $user_ids, array $notification_data) {
        if ($this->use_mock) {
            return $this->mockNotifyUsers($user_ids, $notification_data);
        }
        
        return $this->databaseNotifyUsers($user_ids, $notification_data);
    }
    
    private function _notify_all(array $notification_data) {
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
    
    private function _mark_as_read(int $user_id, int $notification_id) {
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
    
    // Database implementations (for production)
    
    private function databaseNotifyRoles(array $roles, array $notification_data) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseNotifyUsers(array $user_ids, array $notification_data) {
        // TODO: Implement when real database is available  
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseNotifyAll(array $notification_data) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseGetUserNotifications(int $user_id, int $limit) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseGetUnreadCount(int $user_id) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseMarkAsRead(int $user_id, int $notification_id) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
    
    private function databaseMarkAllRead(int $user_id) {
        // TODO: Implement when real database is available
        throw new Exception('Database implementation not yet available');
    }
}