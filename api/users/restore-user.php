<?php
/**
 * Restore User API Endpoint
 * Restores a soft-deleted user (admin only)
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/user-manager.php';

header('Content-Type: application/json');

// Ensure user is logged in and is admin
try {
    require_login();
    require_role('admin');
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Get and validate user ID
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

// Get user info before restoration
$user = UserManager::getUserById($user_id);
if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Check if user is actually deleted
if (!isset($user['status']) || $user['status'] !== 'deleted') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User is not deleted']);
    exit;
}

try {
    // Perform restoration
    $success = UserManager::restoreUser($user_id, $_SESSION['user_id']);
    
    if ($success) {
        // Log the IP address for audit trail
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        error_log("Admin {$_SESSION['user_id']} restored user {$user_id} ({$user['email']}) from IP {$ip_address}");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'User restored successfully',
            'restored_user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to restore user']);
    }
} catch (Exception $e) {
    error_log('User restoration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>