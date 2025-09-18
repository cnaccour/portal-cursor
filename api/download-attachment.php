<?php
require __DIR__.'/../includes/auth.php';
require_login(); // Users must be logged in to download attachments

$announcementId = $_GET['announcement_id'] ?? '';
$filename = $_GET['filename'] ?? '';

if (empty($announcementId) || empty($filename)) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

// Sanitize inputs
$safeAnnouncementId = preg_replace('/[^a-zA-Z0-9-_]/', '', $announcementId);
$safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);

$filePath = __DIR__.'/../storage/attachments/'.$safeAnnouncementId.'/'.$safeFilename;

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Verify the announcement exists and user has access
$dynamicFile = __DIR__.'/../storage/dynamic-announcements.json';
$staticAnnouncements = include __DIR__.'/../includes/static-announcements.php';
$dynamicAnnouncements = [];

if (file_exists($dynamicFile)) {
    $content = file_get_contents($dynamicFile);
    if ($content) {
        $dynamicAnnouncements = json_decode($content, true) ?: [];
    }
}

$allAnnouncements = array_merge($staticAnnouncements, $dynamicAnnouncements);
$announcementFound = false;

foreach ($allAnnouncements as $announcement) {
    if ($announcement['id'] === $announcementId) {
        $announcementFound = true;
        break;
    }
}

if (!$announcementFound) {
    http_response_code(404);
    echo 'Announcement not found';
    exit;
}

// Get file info
$fileSize = filesize($filePath);
$mimeType = mime_content_type($filePath);

// Extract original filename from stored filename (remove timestamp prefix)
$parts = explode('_', $safeFilename, 2);
$originalName = isset($parts[1]) ? $parts[1] : $safeFilename;

// Set headers for file download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $originalName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($filePath);
exit;
?>