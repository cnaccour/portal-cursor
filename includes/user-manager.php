<?php
/**
 * User Management Class
 * Handles user CRUD operations, role management, and audit logging
 */

class UserManager {
    private static $instance = null;
    private $use_mock = true; // Switch to false when database is available
    
    private function __construct() {
        $this->use_mock = !$this->isDatabaseAvailable();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new UserManager();
        }
        return self::$instance;
    }
    
    /**
     * Check if currently using mock mode
     */
    public function isUsingMockMode() {
        return $this->use_mock;
    }
    
    /**
     * Check if database is available for user management
     */
    private function isDatabaseAvailable() {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            if (!$pdo) {
                return false;
            }
            
            // Check if users table exists
            $stmt = $pdo->prepare("SELECT 1 FROM users LIMIT 1");
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log('User database availability check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with optional filtering
     */
    public static function getAllUsers($include_deleted = false) {
        $instance = self::getInstance();
        return $instance->_getAllUsers($include_deleted);
    }
    
    /**
     * Get user by ID
     */
    public static function getUserById($user_id) {
        $instance = self::getInstance();
        return $instance->_getUserById($user_id);
    }
    
    /**
     * Get user by email
     */
    public static function getUserByEmail($email) {
        $instance = self::getInstance();
        return $instance->_getUserByEmail($email);
    }
    
    /**
     * Update user role
     */
    public static function updateUserRole($user_id, $new_role, $performed_by) {
        $instance = self::getInstance();
        return $instance->_updateUserRole($user_id, $new_role, $performed_by);
    }
    
    /**
     * Soft delete user
     */
    public static function deleteUser($user_id, $performed_by) {
        $instance = self::getInstance();
        return $instance->_deleteUser($user_id, $performed_by);
    }
    
    /**
     * Restore soft deleted user
     */
    public static function restoreUser($user_id, $performed_by) {
        $instance = self::getInstance();
        return $instance->_restoreUser($user_id, $performed_by);
    }
    
    /**
     * Update user status (active/inactive)
     */
    public static function updateUserStatus($user_id, $new_status, $performed_by) {
        $instance = self::getInstance();
        return $instance->_updateUserStatus($user_id, $new_status, $performed_by);
    }
    
    /**
     * Create audit log entry
     */
    public static function logUserAction($user_id, $action, $old_value, $new_value, $performed_by) {
        $instance = self::getInstance();
        return $instance->_logUserAction($user_id, $action, $old_value, $new_value, $performed_by);
    }
    
    // Implementation methods
    
    private function _getAllUsers($include_deleted) {
        if ($this->use_mock) {
            return $this->mockGetAllUsers($include_deleted);
        }
        return $this->databaseGetAllUsers($include_deleted);
    }
    
    private function _getUserById($user_id) {
        if ($this->use_mock) {
            return $this->mockGetUserById($user_id);
        }
        return $this->databaseGetUserById($user_id);
    }
    
    private function _getUserByEmail($email) {
        if ($this->use_mock) {
            return $this->mockGetUserByEmail($email);
        }
        return $this->databaseGetUserByEmail($email);
    }
    
    private function _updateUserRole($user_id, $new_role, $performed_by) {
        if ($this->use_mock) {
            return $this->mockUpdateUserRole($user_id, $new_role, $performed_by);
        }
        return $this->databaseUpdateUserRole($user_id, $new_role, $performed_by);
    }
    
    private function _deleteUser($user_id, $performed_by) {
        if ($this->use_mock) {
            return $this->mockDeleteUser($user_id, $performed_by);
        }
        return $this->databaseDeleteUser($user_id, $performed_by);
    }
    
    private function _restoreUser($user_id, $performed_by) {
        if ($this->use_mock) {
            return $this->mockRestoreUser($user_id, $performed_by);
        }
        return $this->databaseRestoreUser($user_id, $performed_by);
    }
    
    private function _logUserAction($user_id, $action, $old_value, $new_value, $performed_by) {
        if ($this->use_mock) {
            return $this->mockLogUserAction($user_id, $action, $old_value, $new_value, $performed_by);
        }
        return $this->databaseLogUserAction($user_id, $action, $old_value, $new_value, $performed_by);
    }
    
    private function _updateUserStatus($user_id, $new_status, $performed_by) {
        if ($this->use_mock) {
            return $this->mockUpdateUserStatus($user_id, $new_status, $performed_by);
        }
        return $this->databaseUpdateUserStatus($user_id, $new_status, $performed_by);
    }
    
    // Mock implementations (using existing mock_users)
    
    private function mockGetAllUsers($include_deleted) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        // Initialize status field for mock users if not present
        foreach ($mock_users as &$user) {
            if (!isset($user['status'])) {
                $user['status'] = 'active';
            }
        }
        unset($user); // Break reference to avoid side effects
        
        // Filter based on deleted status if needed
        if (!$include_deleted) {
            return array_filter($mock_users, fn($user) => $user['status'] !== 'deleted');
        }
        
        return $mock_users;
    }
    
    private function mockGetUserById($user_id) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as $user) {
            if ($user['id'] == $user_id) {
                return $user;
            }
        }
        return null;
    }
    
    private function mockGetUserByEmail($email) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
    
    private function mockUpdateUserRole($user_id, $new_role, $performed_by) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as &$user) {
            if ($user['id'] == $user_id) {
                $old_role = $user['role'];
                $user['role'] = $new_role;
                
                // Mock audit log with IP address
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                error_log("Mock Audit: User {$user_id} role changed from {$old_role} to {$new_role} by user {$performed_by} from IP {$ip_address} UA: {$user_agent}");
                return true;
            }
        }
        unset($user); // Break reference
        return false;
    }
    
    private function mockDeleteUser($user_id, $performed_by) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as &$user) {
            if ($user['id'] == $user_id) {
                $old_status = $user['status'] ?? 'active';
                $user['status'] = 'deleted';
                
                // Comprehensive audit log
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                error_log("Mock Audit: User {$user_id} ({$user['email']}) deleted by user {$performed_by} from IP {$ip_address} UA: {$user_agent}");
                return true;
            }
        }
        unset($user); // Break reference
        return false;
    }
    
    private function mockRestoreUser($user_id, $performed_by) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as &$user) {
            if ($user['id'] == $user_id) {
                $old_status = $user['status'] ?? 'deleted';
                $user['status'] = 'active';
                
                // Comprehensive audit log
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                error_log("Mock Audit: User {$user_id} ({$user['email']}) restored by user {$performed_by} from IP {$ip_address} UA: {$user_agent}");
                return true;
            }
        }
        unset($user); // Break reference
        return false;
    }
    
    private function mockLogUserAction($user_id, $action, $old_value, $new_value, $performed_by) {
        // Comprehensive mock audit log
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $old_json = json_encode($old_value);
        $new_json = json_encode($new_value);
        error_log("Mock Audit: User {$user_id} - {$action} by user {$performed_by} from IP {$ip_address} | Old: {$old_json} | New: {$new_json} | UA: {$user_agent}");
        return true;
    }
    
    private function mockUpdateUserStatus($user_id, $new_status, $performed_by) {
        require_once __DIR__ . '/db.php';
        global $mock_users;
        
        foreach ($mock_users as &$user) {
            if ($user['id'] == $user_id) {
                $old_status = $user['status'] ?? 'active';
                $user['status'] = $new_status;
                
                // Comprehensive audit log
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                error_log("Mock Audit: User {$user_id} ({$user['email']}) status changed from {$old_status} to {$new_status} by user {$performed_by} from IP {$ip_address} UA: {$user_agent}");
                return true;
            }
        }
        unset($user); // Break reference
        return false;
    }
    
    // Database implementations (for production)
    
    private function databaseGetAllUsers($include_deleted) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $where_clause = $include_deleted ? "" : "WHERE status != 'deleted'";
            $stmt = $pdo->prepare("SELECT * FROM users {$where_clause} ORDER BY created_at DESC");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Database getAllUsers error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function databaseGetUserById($user_id) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Database getUserById error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function databaseGetUserByEmail($email) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Database getUserByEmail error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function databaseUpdateUserRole($user_id, $new_role, $performed_by) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Get current user data for audit log
            $current_user = $this->databaseGetUserById($user_id);
            if (!$current_user) {
                return false;
            }
            
            // Update role
            $stmt = $pdo->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$new_role, $user_id]);
            
            if ($success) {
                // Log the action
                $this->databaseLogUserAction(
                    $user_id, 
                    'role_changed', 
                    ['role' => $current_user['role']], 
                    ['role' => $new_role], 
                    $performed_by
                );
            }
            
            return $success;
        } catch (Exception $e) {
            error_log('Database updateUserRole error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseDeleteUser($user_id, $performed_by) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Get current user data for audit log
            $current_user = $this->databaseGetUserById($user_id);
            if (!$current_user) {
                return false;
            }
            
            // Soft delete
            $stmt = $pdo->prepare("UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$user_id]);
            
            if ($success) {
                // Log the action
                $this->databaseLogUserAction(
                    $user_id, 
                    'deleted', 
                    ['status' => $current_user['status']], 
                    ['status' => 'deleted'], 
                    $performed_by
                );
            }
            
            return $success;
        } catch (Exception $e) {
            error_log('Database deleteUser error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseRestoreUser($user_id, $performed_by) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Restore user
            $stmt = $pdo->prepare("UPDATE users SET status = 'active', deleted_at = NULL WHERE id = ?");
            $success = $stmt->execute([$user_id]);
            
            if ($success) {
                // Log the action
                $this->databaseLogUserAction(
                    $user_id, 
                    'restored', 
                    ['status' => 'deleted'], 
                    ['status' => 'active'], 
                    $performed_by
                );
            }
            
            return $success;
        } catch (Exception $e) {
            error_log('Database restoreUser error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseLogUserAction($user_id, $action, $old_value, $new_value, $performed_by) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $stmt = $pdo->prepare("
                INSERT INTO user_audit_log (user_id, action, old_value, new_value, performed_by, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $user_id,
                $action,
                json_encode($old_value),
                json_encode($new_value),
                $performed_by,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log('Database logUserAction error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function databaseUpdateUserStatus($user_id, $new_status, $performed_by) {
        try {
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Get current user data for audit log
            $current_user = $this->databaseGetUserById($user_id);
            if (!$current_user) {
                return false;
            }
            
            // Update status
            $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?");
            $success = $stmt->execute([$new_status, $user_id]);
            
            if ($success) {
                // Log the action
                $this->databaseLogUserAction(
                    $user_id, 
                    'status_changed', 
                    ['status' => $current_user['status']], 
                    ['status' => $new_status], 
                    $performed_by
                );
            }
            
            return $success;
        } catch (Exception $e) {
            error_log('Database updateUserStatus error: ' . $e->getMessage());
            return false;
        }
    }
}