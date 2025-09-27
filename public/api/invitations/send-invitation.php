<?php
/**
 * Send Invitation API Endpoint
 * Handles creating and sending user invitations
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/invitation-manager.php';
require_once __DIR__ . '/../../includes/email-templates.php';

// Security checks
require_login();
require_role('admin'); // Only admins can send invitations

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
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $role = $_POST['role'] ?? '';
    
    if (!$email) {
        throw new InvalidArgumentException('Please enter a valid email address');
    }
    
    if (empty($role)) {
        throw new InvalidArgumentException('Please select a role');
    }
    
    // Validate role
    $valid_roles = ['admin', 'manager', 'support', 'staff', 'viewer'];
    if (!in_array($role, $valid_roles)) {
        throw new InvalidArgumentException('Invalid role selected');
    }
    
    // Create invitation
    $invitationManager = InvitationManager::getInstance();
    $current_user_id = $_SESSION['user_id'];
    
    $invitation = $invitationManager->createInvitation($email, $role, $current_user_id);
    
    // Prepare email data
    $invitation_data = [
        'email' => $invitation['email'],
        'role' => $invitation['role'],
        'token' => $invitation['token'],
        'expires_at' => $invitation['expires_at'],
        'invited_by_name' => $_SESSION['user_name'] ?? 'Administrator'
    ];
    
    // Send invitation email
    $email_sent = EmailTemplates::sendInvitationEmail($invitation_data);
    
    if (!$email_sent) {
        // Log warning but don't fail - invitation was created
        error_log("Warning: Failed to send invitation email to {$email}");
    }
    
    // Temporary debug log (to be removed before real invites)
    @file_put_contents(__DIR__ . '/../../admin_debug.log',
        date('Y-m-d H:i:s') . " [INVITE] to={$email} role={$role} sent=" . ($email_sent ? '1' : '0') . " by=" . ($_SESSION['user_id'] ?? 'unknown') . "\n",
        FILE_APPEND);

    echo json_encode([
        'success' => true,
        'message' => "Invitation sent successfully to {$email}",
        'invitation' => [
            'id' => $invitation['id'],
            'email' => $invitation['email'],
            'role' => $invitation['role'],
            'expires_at' => $invitation['expires_at'],
            'status' => $invitation['status']
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error sending invitation: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending the invitation']);
}