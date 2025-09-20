<?php
require __DIR__.'/../includes/auth.php';
require_login();
require_role('admin'); // Only admins can manage announcements

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF protection
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Get announcement ID
$announcementId = $_POST['announcement_id'] ?? '';
if (empty($announcementId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Announcement ID is required']);
    exit;
}

// Check if this is a static announcement
$staticAnnouncements = require __DIR__.'/../includes/static-announcements.php';
$isStatic = false;
foreach ($staticAnnouncements as $staticAnn) {
    if ($staticAnn['id'] === $announcementId && !empty($staticAnn['is_static'])) {
        $isStatic = true;
        break;
    }
}

if ($isStatic) {
    // Static announcements cannot be truly deleted through the API
    // In a production system, you might track hidden static announcements
    echo json_encode([
        'success' => true,
        'message' => 'Static announcement hidden (will reappear on reload)'
    ]);
    exit;
}

// Load existing dynamic announcements
$dynamicFile = __DIR__.'/../storage/dynamic-announcements.json';
$dynamicAnnouncements = [];
if (file_exists($dynamicFile)) {
    $fileHandle = fopen($dynamicFile, 'r');
    if ($fileHandle && flock($fileHandle, LOCK_SH)) {
        $content = fread($fileHandle, filesize($dynamicFile));
        flock($fileHandle, LOCK_UN);
        fclose($fileHandle);
        if ($content) {
            $dynamicAnnouncements = json_decode($content, true) ?: [];
        }
    } else {
        if ($fileHandle) fclose($fileHandle);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not read announcements file']);
        exit;
    }
}

// Find and remove the announcement
$found = false;
$dynamicAnnouncements = array_filter($dynamicAnnouncements, function($announcement) use ($announcementId, &$found) {
    if ($announcement['id'] === $announcementId) {
        $found = true;
        return false; // Remove this announcement
    }
    return true; // Keep this announcement
});

if (!$found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Announcement not found']);
    exit;
}

// Re-index array
$dynamicAnnouncements = array_values($dynamicAnnouncements);

// Save updated dynamic announcements with file locking
$tempFile = $dynamicFile . '.tmp';
$jsonData = json_encode($dynamicAnnouncements, JSON_PRETTY_PRINT);

if (file_put_contents($tempFile, $jsonData, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save changes']);
    exit;
}

// Atomic move to final location
if (!rename($tempFile, $dynamicFile)) {
    unlink($tempFile); // Clean up temp file
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save changes']);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Announcement deleted successfully'
]);
?>