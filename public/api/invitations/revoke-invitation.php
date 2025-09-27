<?php
/**
 * Revoke Invitation API Endpoint
 * Allows admins to revoke pending invitations
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/invitation-manager.php';

// Security checks
require_login();
require_role('admin'); // Only admins can revoke invitations

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF protection
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    // Validate input
    $invitation_id = filter_var($_POST['invitation_id'] ?? '', FILTER_VALIDATE_INT);
    
    if (!$invitation_id) {
        throw new InvalidArgumentException('Invalid invitation ID');
    }
    
    // Revoke invitation
    $invitationManager = InvitationManager::getInstance();
    $current_user_id = $_SESSION['user_id'];
    
    $success = $invitationManager->revokeInvitation($invitation_id, $current_user_id);
    
    if ($success) {
        // Temporary debug log
        @file_put_contents(__DIR__ . '/../../admin_debug.log',
            date('Y-m-d H:i:s') . " [INVITE_REVOKE] id={$invitation_id} by=" . ($_SESSION['user_id'] ?? 'unknown') . "\n",
            FILE_APPEND);
        echo json_encode([
            'success' => true,
            'message' => 'Invitation revoked successfully'
        ]);
    } else {
        throw new RuntimeException('Failed to revoke invitation');
    }
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error revoking invitation: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while revoking the invitation']);
}