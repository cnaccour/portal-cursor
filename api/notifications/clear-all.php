<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF protection
if (!hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    require_once __DIR__ . '/../../includes/db.php';
    
    // For now, since we don't have a notifications table, we'll just return success
    // In the future, this would clear all notifications for the user
    // $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
    // $stmt->execute([$_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Clear notifications error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}