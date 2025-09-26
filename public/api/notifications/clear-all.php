<?php
/**
 * Clear All Notifications API Endpoint
 * POST /api/notifications/clear-all.php
 * Marks all notifications as read for the current user
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notification-manager.php';

// Ensure user is logged in
require_login();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF token
    if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Mark all notifications as read using NotificationManager
    $success = NotificationManager::mark_all_read($user_id);
    
    if ($success) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'All notifications cleared successfully',
            'unread_count' => 0
        ]);
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Failed to clear notifications'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Clear all notifications API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}