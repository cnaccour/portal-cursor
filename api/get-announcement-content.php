<?php
/**
 * Get Announcement Content API
 * Returns rendered content for announcements, handling special cases like education schedule
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// Get announcement ID from request
$announcementId = $_GET['id'] ?? '';

if (empty($announcementId)) {
    echo json_encode(['success' => false, 'error' => 'Announcement ID required']);
    exit;
}

// Load announcements
require_once __DIR__.'/../includes/announcement-helpers.php';
$allAnnouncements = loadAllAnnouncements();

// Find the announcement
$announcement = null;
foreach ($allAnnouncements as $ann) {
    if ($ann['id'] === $announcementId) {
        $announcement = $ann;
        break;
    }
}

if (!$announcement) {
    echo json_encode(['success' => false, 'error' => 'Announcement not found']);
    exit;
}

// Check if this is the education schedule
if ($announcement['id'] === 'static-education-2025' && isset($announcement['education_data'])) {
    require_once __DIR__.'/../includes/education-schedule-renderer.php';
    $content = renderEducationSchedule($announcement['education_data']);
} else {
    $content = $announcement['content'];
}

echo json_encode([
    'success' => true,
    'content' => $content
]);
?>