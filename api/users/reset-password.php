<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/user-manager.php';

// Start session and check authentication
session_start();
require_login();

// Only allow administrators to reset passwords
if (!isset($_SESSION['role_level']) || $_SESSION['role_level'] < 5) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate CSRF token
if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Validate required fields
if (!isset($input['user_id']) || !isset($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$userId = intval($input['user_id']);
$newPassword = trim($input['new_password']);

// Validate password strength
if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters long']);
    exit;
}

// Prevent self password reset through this endpoint (admin should use regular password change)
if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot reset your own password through this method']);
    exit;
}

try {
    $userManager = UserManager::getInstance();
    
    // Check if user exists
    $user = $userManager->getUserById($userId);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    
    // Reset the password
    $result = $userManager->resetUserPassword($userId, $newPassword, $_SESSION['user_id']);
    
    if ($result) {
        // Log the password reset action
        error_log("Password reset by admin: User ID {$userId} password reset by admin ID {$_SESSION['user_id']} ({$_SESSION['name']})");
        
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to reset password']);
    }
} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>