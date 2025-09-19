<?php
/**
 * Invitation Management System
 * Handles invitation creation, validation, and user signup via invitations
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

class InvitationManager {
    private static $instance = null;
    private $use_mock;
    
    private function __construct() {
        // Check if we're in development mode
        $dev_mode = getenv('DEV_MODE') === 'true' || file_exists(__DIR__ . '/../.dev_mode');
        
        if ($dev_mode) {
            $this->use_mock = !$this->isDatabaseAvailable();
        } else {
            // Production mode - database must be available
            if (!$this->isDatabaseAvailable()) {
                throw new RuntimeException('Database not available. Please run migrations and ensure database is properly configured.');
            }
            $this->use_mock = false;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if database tables are available
     */
    private function isDatabaseAvailable() {
        try {
            // First check if getPDO function exists
            if (!function_exists('getPDO')) {
                return false;
            }
            
            $pdo = getPDO();
            $stmt = $pdo->query("SHOW TABLES LIKE 'invitations'");
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
    
    public function isUsingMockMode() {
        return $this->use_mock;
    }
    
    /**
     * Create a new invitation
     */
    public function createInvitation($email, $role, $invited_by_id, $expires_in_days = 7) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        // Check if user already exists
        require_once __DIR__ . '/user-manager.php';
        $userManager = UserManager::getInstance();
        $existing_user = $userManager->getUserByEmail($email);
        if ($existing_user) {
            throw new InvalidArgumentException('A user with this email already exists');
        }
        
        // Check if there's already a pending invitation
        $existing_invitation = $this->getInvitationByEmail($email);
        if ($existing_invitation && $existing_invitation['status'] === 'pending') {
            throw new InvalidArgumentException('A pending invitation already exists for this email');
        }
        
        // Generate secure token
        $token = $this->generateSecureToken();
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_in_days} days"));
        
        if ($this->use_mock) {
            return $this->mockCreateInvitation($email, $token, $role, $invited_by_id, $expires_at);
        } else {
            return $this->databaseCreateInvitation($email, $token, $role, $invited_by_id, $expires_at);
        }
    }
    
    /**
     * Get invitation by token
     */
    public function getInvitationByToken($token) {
        if ($this->use_mock) {
            return $this->mockGetInvitationByToken($token);
        } else {
            return $this->databaseGetInvitationByToken($token);
        }
    }
    
    /**
     * Get invitation by email
     */
    public function getInvitationByEmail($email) {
        if ($this->use_mock) {
            return $this->mockGetInvitationByEmail($email);
        } else {
            return $this->databaseGetInvitationByEmail($email);
        }
    }
    
    /**
     * Get all invitations with optional filtering
     */
    public function getAllInvitations($status = null) {
        if ($this->use_mock) {
            return $this->mockGetAllInvitations($status);
        } else {
            return $this->databaseGetAllInvitations($status);
        }
    }
    
    /**
     * Accept an invitation and create user account
     */
    public function acceptInvitation($token, $name, $password) {
        $invitation = $this->getInvitationByToken($token);
        
        if (!$invitation) {
            throw new InvalidArgumentException('Invalid invitation token');
        }
        
        if ($invitation['status'] !== 'pending') {
            throw new InvalidArgumentException('Invitation is no longer active');
        }
        
        if (strtotime($invitation['expires_at']) < time()) {
            // Mark as expired
            $this->updateInvitationStatus($invitation['id'], 'expired');
            throw new InvalidArgumentException('Invitation has expired');
        }
        
        // Create user account
        require_once __DIR__ . '/user-manager.php';
        $userManager = UserManager::getInstance();
        
        try {
            $user_id = $userManager->createUser(
                $invitation['email'],
                $name,
                $password,
                $invitation['role']
            );
            
            // Mark invitation as accepted
            $this->updateInvitationStatus($invitation['id'], 'accepted');
            
            // Log the action
            $this->logInvitationAction($invitation['id'], 'accepted', 'pending', 'accepted', $user_id);
            
            return $user_id;
            
        } catch (Exception $e) {
            error_log("Failed to accept invitation {$token}: " . $e->getMessage());
            throw new RuntimeException('Failed to create user account');
        }
    }
    
    /**
     * Revoke an invitation
     */
    public function revokeInvitation($invitation_id, $revoked_by_id) {
        $invitation = $this->getInvitationById($invitation_id);
        
        if (!$invitation) {
            throw new InvalidArgumentException('Invitation not found');
        }
        
        if ($invitation['status'] !== 'pending') {
            throw new InvalidArgumentException('Can only revoke pending invitations');
        }
        
        $old_status = $invitation['status'];
        $this->updateInvitationStatus($invitation_id, 'revoked');
        $this->logInvitationAction($invitation_id, 'revoked', $old_status, 'revoked', $revoked_by_id);
        
        return true;
    }
    
    /**
     * Clean up expired invitations
     */
    public function cleanupExpiredInvitations() {
        $expired_invitations = $this->getAllInvitations('pending');
        $expired_count = 0;
        
        foreach ($expired_invitations as $invitation) {
            if (strtotime($invitation['expires_at']) < time()) {
                $this->updateInvitationStatus($invitation['id'], 'expired');
                $expired_count++;
            }
        }
        
        return $expired_count;
    }
    
    /**
     * Generate secure invitation token
     */
    private function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Update invitation status
     */
    private function updateInvitationStatus($invitation_id, $new_status) {
        if ($this->use_mock) {
            return $this->mockUpdateInvitationStatus($invitation_id, $new_status);
        } else {
            return $this->databaseUpdateInvitationStatus($invitation_id, $new_status);
        }
    }
    
    /**
     * Get invitation by ID
     */
    private function getInvitationById($invitation_id) {
        if ($this->use_mock) {
            return $this->mockGetInvitationById($invitation_id);
        } else {
            return $this->databaseGetInvitationById($invitation_id);
        }
    }
    
    /**
     * Log invitation-related actions
     */
    private function logInvitationAction($invitation_id, $action, $old_value, $new_value, $performed_by) {
        if ($this->use_mock) {
            return $this->mockLogInvitationAction($invitation_id, $action, $old_value, $new_value, $performed_by);
        } else {
            return $this->databaseLogInvitationAction($invitation_id, $action, $old_value, $new_value, $performed_by);
        }
    }
    
    // Mock implementations for development
    private function mockCreateInvitation($email, $token, $role, $invited_by_id, $expires_at) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            $mock_invitations = [];
        }
        
        $invitation = [
            'id' => count($mock_invitations) + 1,
            'email' => $email,
            'token' => $token,
            'role' => $role,
            'invited_by' => $invited_by_id,
            'status' => 'pending',
            'expires_at' => $expires_at,
            'accepted_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $mock_invitations[] = $invitation;
        
        // Log action
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        error_log("Mock Audit: Invitation created for {$email} with role {$role} by user {$invited_by_id} from IP {$ip_address} UA: {$user_agent}");
        
        return $invitation;
    }
    
    private function mockGetInvitationByToken($token) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            return null;
        }
        
        foreach ($mock_invitations as $invitation) {
            if ($invitation['token'] === $token) {
                return $invitation;
            }
        }
        
        return null;
    }
    
    private function mockGetInvitationByEmail($email) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            return null;
        }
        
        foreach ($mock_invitations as $invitation) {
            if ($invitation['email'] === $email && $invitation['status'] === 'pending') {
                return $invitation;
            }
        }
        
        return null;
    }
    
    private function mockGetAllInvitations($status = null) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            return [];
        }
        
        if ($status === null) {
            return $mock_invitations;
        }
        
        return array_filter($mock_invitations, fn($inv) => $inv['status'] === $status);
    }
    
    private function mockGetInvitationById($invitation_id) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            return null;
        }
        
        foreach ($mock_invitations as $invitation) {
            if ($invitation['id'] == $invitation_id) {
                return $invitation;
            }
        }
        
        return null;
    }
    
    private function mockUpdateInvitationStatus($invitation_id, $new_status) {
        require_once __DIR__ . '/db.php';
        global $mock_invitations;
        
        if (!isset($mock_invitations)) {
            return false;
        }
        
        foreach ($mock_invitations as &$invitation) {
            if ($invitation['id'] == $invitation_id) {
                $old_status = $invitation['status'];
                $invitation['status'] = $new_status;
                $invitation['updated_at'] = date('Y-m-d H:i:s');
                
                if ($new_status === 'accepted') {
                    $invitation['accepted_at'] = date('Y-m-d H:i:s');
                }
                
                // Log action
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                error_log("Mock Audit: Invitation {$invitation_id} status changed from {$old_status} to {$new_status} from IP {$ip_address} UA: {$user_agent}");
                
                return true;
            }
        }
        unset($invitation); // Break reference
        
        return false;
    }
    
    private function mockLogInvitationAction($invitation_id, $action, $old_value, $new_value, $performed_by) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $old_json = json_encode($old_value);
        $new_json = json_encode($new_value);
        error_log("Mock Audit: Invitation {$invitation_id} - {$action} by user {$performed_by} from IP {$ip_address} | Old: {$old_json} | New: {$new_json} | UA: {$user_agent}");
        return true;
    }
    
    // Database implementations (for production)
    private function databaseCreateInvitation($email, $token, $role, $invited_by_id, $expires_at) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("
                INSERT INTO invitations (email, token, role, invited_by, expires_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$email, $token, $role, $invited_by_id, $expires_at]);
            
            $invitation_id = $pdo->lastInsertId();
            
            // Log the action
            $this->databaseLogInvitationAction($invitation_id, 'created', null, [
                'email' => $email,
                'role' => $role,
                'expires_at' => $expires_at
            ], $invited_by_id);
            
            // Return the created invitation
            return $this->databaseGetInvitationById($invitation_id);
            
        } catch (PDOException $e) {
            error_log("Database error creating invitation: " . $e->getMessage());
            throw new RuntimeException('Failed to create invitation');
        }
    }
    
    private function databaseGetInvitationByToken($token) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM invitations WHERE token = ?");
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Database error getting invitation by token: " . $e->getMessage());
            return null;
        }
    }
    
    private function databaseGetInvitationByEmail($email) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM invitations WHERE email = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Database error getting invitation by email: " . $e->getMessage());
            return null;
        }
    }
    
    private function databaseGetAllInvitations($status = null) {
        try {
            $pdo = getPDO();
            
            if ($status) {
                $stmt = $pdo->prepare("
                    SELECT i.*, u.name as invited_by_name, u.email as invited_by_email 
                    FROM invitations i 
                    JOIN users u ON i.invited_by = u.id 
                    WHERE i.status = ? 
                    ORDER BY i.created_at DESC
                ");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT i.*, u.name as invited_by_name, u.email as invited_by_email 
                    FROM invitations i 
                    JOIN users u ON i.invited_by = u.id 
                    ORDER BY i.created_at DESC
                ");
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error getting invitations: " . $e->getMessage());
            return [];
        }
    }
    
    private function databaseGetInvitationById($invitation_id) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM invitations WHERE id = ?");
            $stmt->execute([$invitation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Database error getting invitation by ID: " . $e->getMessage());
            return null;
        }
    }
    
    private function databaseUpdateInvitationStatus($invitation_id, $new_status) {
        try {
            $pdo = getPDO();
            
            if ($new_status === 'accepted') {
                $stmt = $pdo->prepare("UPDATE invitations SET status = ?, accepted_at = NOW() WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE invitations SET status = ? WHERE id = ?");
            }
            
            $stmt->execute([$new_status, $invitation_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database error updating invitation status: " . $e->getMessage());
            return false;
        }
    }
    
    private function databaseLogInvitationAction($invitation_id, $action, $old_value, $new_value, $performed_by) {
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("
                INSERT INTO user_audit_log (user_id, action, old_value, new_value, performed_by, ip_address, user_agent, invitation_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $stmt->execute([
                null, // user_id is null for invitation actions
                "invitation_{$action}",
                json_encode($old_value),
                json_encode($new_value),
                $performed_by,
                $ip_address,
                $user_agent,
                $invitation_id
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Database error logging invitation action: " . $e->getMessage());
            return false;
        }
    }
}