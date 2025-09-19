<?php
/**
 * Mark Notification as Read API Endpoint
 * POST /api/notifications/mark-read.php
 * Marks a specific notification as read for the current user
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
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
    
    // Validate notification ID
    if (!isset($input['notification_id']) || !$input['notification_id']) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Notification ID is required'
        ]);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $notification_id = $input['notification_id'];
    
    // Convert notification_id to int if it's numeric, otherwise keep as string for UUID
    if (is_numeric($notification_id)) {
        $notification_id = (int)$notification_id;
    }
    
    // Mark notification as read
    $success = NotificationManager::mark_as_read($user_id, $notification_id);
    
    if ($success) {
        // Get updated unread count
        $unread_count = NotificationManager::get_unread_count($user_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read',
            'unread_count' => $unread_count
        ]);
    } else {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Failed to mark notification as read'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Mark notification read API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}