<?php
/**
 * List Invitations API Endpoint
 * Returns all invitations for admin management
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/invitation-manager.php';

// Security checks
require_login();
require_role('admin'); // Only admins can view invitations

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $invitationManager = InvitationManager::getInstance();
    
    // Clean up expired invitations first
    $invitationManager->cleanupExpiredInvitations();
    
    // Get filter parameter
    $status = $_GET['status'] ?? null;
    
    // Get all invitations
    $invitations = $invitationManager->getAllInvitations($status);
    // Temporary debug log
    @file_put_contents(__DIR__ . '/../../admin_debug.log',
        date('Y-m-d H:i:s') . " [INVITE_LIST] count=" . count($invitations) . " by=" . ($_SESSION['user_id'] ?? 'unknown') . "\n",
        FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'invitations' => $invitations,
        'count' => count($invitations)
    ]);
    
} catch (Exception $e) {
    error_log("Error listing invitations: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading invitations']);
}