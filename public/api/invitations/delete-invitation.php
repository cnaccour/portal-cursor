<?php
/**
 * Delete Invitation API Endpoint
 * Permanently deletes an invitation (admin only)
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/invitation-manager.php';

require_login();
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $invitation_id = filter_var($_POST['invitation_id'] ?? '', FILTER_VALIDATE_INT);
    if (!$invitation_id) {
        throw new InvalidArgumentException('Invalid invitation ID');
    }

    $invitationManager = InvitationManager::getInstance();
    $ok = $invitationManager->deleteInvitation($invitation_id, $_SESSION['user_id']);

    if ($ok) {
        @file_put_contents(__DIR__ . '/../../admin_debug.log',
            date('Y-m-d H:i:s') . " [INVITE_DELETE] id={$invitation_id} by=" . ($_SESSION['user_id'] ?? 'unknown') . "\n",
            FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Invitation deleted']);
    } else {
        throw new RuntimeException('Failed to delete invitation');
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('Error deleting invitation: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the invitation']);
}


