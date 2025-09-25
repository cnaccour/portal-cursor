<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/api-helpers.php';
require __DIR__.'/../includes/announcement-helpers.php';
require __DIR__.'/../includes/notification-manager.php';

require_login();
require_role('admin'); // Only admins can manage announcements

// Set content type to JSON
header('Content-Type: application/json');

// Validate request
requirePostMethod();
validateCSRFToken();

// Get and validate mode
$mode = $_POST['mode'] ?? '';
validateFieldInArray('mode', $mode, ['add', 'edit']);

// Validate required fields
validateRequiredFields(['title', 'content', 'category']);

// Validate category
$validCategories = ['general', 'system', 'training', 'schedule', 'policy', 'events', 'safety'];
validateFieldInArray('category', $_POST['category'], $validCategories);

// Validate field lengths
validateStringLength('title', $_POST['title'], 255);
validateStringLength('content', $_POST['content'], 5000);

// Validate expiration date if provided
if (!empty($_POST['expiration_date'])) {
    validateDateFormat('expiration date', $_POST['expiration_date']);
}

// Load existing dynamic announcements
$dynamicFile = __DIR__.'/../storage/dynamic-announcements.json';
$dynamicAnnouncements = loadJSONFile($dynamicFile, true);

// Prepare announcement data
$announcementData = [
    'title' => trim($_POST['title']),
    'content' => trim($_POST['content']),
    'category' => $_POST['category'],
    'author' => $_SESSION['name'] ?? 'Unknown',
    'location_specific' => false,
    'pinned' => isset($_POST['pinned']) && $_POST['pinned'] === '1',
    'priority' => isset($_POST['pinned']) && $_POST['pinned'] === '1' ? 1 : 2,
    'expiration_date' => !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null,
    'date_modified' => date('Y-m-d H:i:s'),
    'attachments' => []
];

if ($mode === 'add') {
    // Generate new ID for dynamic announcements
    $announcementData['id'] = 'dynamic-' . uniqid();
    $announcementData['date_created'] = date('Y-m-d');
    
    // Load existing attachments using helper function
    $announcementData['attachments'] = loadAnnouncementAttachments($announcementData['id']);
    
    // Add to dynamic announcements
    $dynamicAnnouncements[] = $announcementData;
    
} elseif ($mode === 'edit') {
    $announcementId = $_POST['announcement_id'] ?? '';
    
    // For now, all announcements are editable (we'll add static ones later)
    // Future: Add check for truly static announcements here
    
    // Find and update the announcement
    $found = false;
    foreach ($dynamicAnnouncements as &$announcement) {
        if ($announcement['id'] === $announcementId) {
            // Preserve original creation date and ID
            $announcementData['id'] = $announcement['id'];
            $announcementData['date_created'] = $announcement['date_created'];
            
            // Load current attachments using helper function
            $announcementData['attachments'] = loadAnnouncementAttachments($announcementId);
            
            // Update the announcement
            $announcement = $announcementData;
            $found = true;
            break;
        }
    }
    unset($announcement); // Break reference
    
    if (!$found) {
        sendErrorResponse(404, 'Announcement not found');
    }
}

// Save updated announcements using helper function
$successMessage = $mode === 'add' ? 'Announcement created successfully' : 'Announcement updated successfully';
saveJSONFile($dynamicFile, $dynamicAnnouncements, 'Could not save announcement');

// Send notifications to relevant users
try {
    if ($mode === 'add') {
        // Notify all users about new announcements
        NotificationManager::notify_roles(['admin', 'manager', 'support', 'staff', 'viewer'], [
            'type' => 'announcement',
            'title' => 'New Announcement: ' . $announcementData['title'],
            'message' => 'A new announcement has been posted. Click to read more.',
            'link_url' => '/announcements.php#announcement-' . $announcementData['id'],
            'icon' => 'announcement'
        ]);
    } elseif ($mode === 'edit') {
        // Notify users about announcement updates (except viewers to reduce noise)
        NotificationManager::notify_roles(['admin', 'manager', 'support', 'staff'], [
            'type' => 'announcement',
            'title' => 'Updated: ' . $announcementData['title'],
            'message' => 'An announcement has been updated. Click to see the changes.',
            'link_url' => '/announcements.php#announcement-' . $announcementData['id'],
            'icon' => 'announcement'
        ]);
    }
} catch (Exception $e) {
    // Log notification error but don't fail the announcement save
    error_log('Failed to send announcement notification: ' . $e->getMessage());
}

sendSuccessResponse(['announcement' => $announcementData], $successMessage);
?>