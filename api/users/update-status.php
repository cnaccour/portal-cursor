<?php
/**
 * Update User Status API Endpoint
 * Toggle user status between active/inactive (admin only)
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

// Get and validate inputs
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$new_status = trim($_POST['status'] ?? '');

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

if (!in_array($new_status, ['active', 'inactive'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status. Must be active or inactive']);
    exit;
}

// Prevent changing own status
if ($user_id == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot change your own status']);
    exit;
}

// Get user info before update
$user = UserManager::getUserById($user_id);
if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Check if user is deleted
if (isset($user['status']) && $user['status'] === 'deleted') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot change status of deleted user']);
    exit;
}

try {
    // Update user status
    $success = UserManager::updateUserStatus($user_id, $new_status, $_SESSION['user_id']);
    
    if ($success) {
        // Log the IP address for audit trail
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $old_status = $user['status'] ?? 'active';
        error_log("Admin {$_SESSION['user_id']} changed user {$user_id} ({$user['email']}) status from {$old_status} to {$new_status} from IP {$ip_address}");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'User status updated successfully',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'status' => $new_status
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update user status']);
    }
} catch (Exception $e) {
    error_log('User status update error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>