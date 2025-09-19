<?php
/**
 * Cleanup Expired Invitations
 * This can be run as a cron job or called manually to clean up expired invitations
 */

require_once __DIR__ . '/../../includes/invitation-manager.php';

// If called via web, require admin authentication
if (isset($_SERVER['HTTP_HOST'])) {
    require_once __DIR__ . '/../../includes/auth.php';
    require_login();
    require_role('admin');
}

try {
    $invitationManager = InvitationManager::getInstance();
    $expired_count = $invitationManager->cleanupExpiredInvitations();
    
    $message = "Cleanup completed. {$expired_count} expired invitations were updated.";
    
    if (isset($_SERVER['HTTP_HOST'])) {
        // Web request - return JSON
        echo json_encode([
            'success' => true,
            'message' => $message,
            'expired_count' => $expired_count
        ]);
    } else {
        // CLI request - print message
        echo $message . "\n";
    }
    
} catch (Exception $e) {
    $error = "Error during cleanup: " . $e->getMessage();
    error_log($error);
    
    if (isset($_SERVER['HTTP_HOST'])) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $error]);
    } else {
        echo $error . "\n";
        exit(1);
    }
}