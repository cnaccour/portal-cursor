<?php
require __DIR__.'/../includes/auth.php';
require_login();
require_role('admin'); // Only admins can delete attachments

// CSRF protection
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$announcementId = $_POST['announcement_id'] ?? '';
$filename = $_POST['filename'] ?? '';

if (empty($announcementId) || empty($filename)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Sanitize inputs
$safeAnnouncementId = preg_replace('/[^a-zA-Z0-9-_]/', '', $announcementId);
$safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);

$filePath = __DIR__.'/../storage/attachments/'.$safeAnnouncementId.'/'.$safeFilename;

// Check if file exists and delete it
if (file_exists($filePath)) {
    if (unlink($filePath)) {
        echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not delete file']);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'File not found']);
}
?>