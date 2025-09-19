<?php
/**
 * Notifications API Endpoint
 * GET /api/notifications.php
 * Returns user's notifications with unread count
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notification-manager.php';

// Ensure user is logged in
require_login();

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $limit = max(1, min(100, $limit)); // Ensure reasonable limits
    
    // Get notifications and unread count
    $notifications = NotificationManager::get_user_notifications($user_id, $limit);
    $unread_count = NotificationManager::get_unread_count($user_id);
    
    // Function to validate safe internal links
    $validateSafeLink = function($link_url) {
        if (empty($link_url)) {
            return null;
        }
        
        // Only allow relative paths starting with '/'
        if (!preg_match('/^\/[^\/]/', $link_url) || 
            preg_match('/^[a-z]+:/i', $link_url) || 
            strpos($link_url, '//') !== false) {
            return null; // Block unsafe URLs
        }
        
        return $link_url;
    };
    
    // Format response
    $response = [
        'success' => true,
        'unread_count' => $unread_count,
        'notifications' => array_map(function($notification) use ($validateSafeLink) {
            return [
                'id' => $notification['id'],
                'type' => $notification['type'] ?? 'general',
                'title' => htmlspecialchars($notification['title'] ?? ''),
                'message' => htmlspecialchars($notification['message'] ?? ''),
                'link_url' => $validateSafeLink($notification['link_url'] ?? null),
                'icon' => $notification['icon'] ?? 'bell',
                'is_read' => $notification['is_read'] ?? false,
                'created_at' => $notification['created_at'],
                'expires_at' => $notification['expires_at'] ?? null
            ];
        }, $notifications)
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Notifications API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications'
    ]);
}