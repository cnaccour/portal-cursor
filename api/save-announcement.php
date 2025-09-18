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

// Get the mode (add or edit)
$mode = $_POST['mode'] ?? '';
if (!in_array($mode, ['add', 'edit'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid mode']);
    exit;
}

// Validate required fields
$requiredFields = ['title', 'content', 'category'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
        exit;
    }
}

// Validate category
$validCategories = ['general', 'system', 'training', 'schedule', 'policy', 'events', 'safety'];
if (!in_array($_POST['category'], $validCategories)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid category']);
    exit;
}

// Validate content length
if (strlen($_POST['title']) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Title too long (max 255 characters)']);
    exit;
}

if (strlen($_POST['content']) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Content too long (max 5000 characters)']);
    exit;
}

// Validate expiration date
if (!empty($_POST['expiration_date'])) {
    $dateCheck = DateTime::createFromFormat('Y-m-d', $_POST['expiration_date']);
    if (!$dateCheck || $dateCheck->format('Y-m-d') !== $_POST['expiration_date']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid expiration date format']);
        exit;
    }
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
    'date_modified' => date('Y-m-d H:i:s')
];

if ($mode === 'add') {
    // Generate new ID for dynamic announcements
    $announcementData['id'] = 'dynamic-' . uniqid();
    $announcementData['date_created'] = date('Y-m-d');
    
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
            
            // Update the announcement
            $announcement = $announcementData;
            $found = true;
            break;
        }
    }
    unset($announcement); // Break reference
    
    if (!$found) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Announcement not found']);
        exit;
    }
}

// Ensure data directory exists
$dataDir = dirname($dynamicFile);
if (!is_dir($dataDir)) {
    if (!mkdir($dataDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not create data directory']);
        exit;
    }
}

// Save dynamic announcements with file locking
$tempFile = $dynamicFile . '.tmp';
$jsonData = json_encode($dynamicAnnouncements, JSON_PRETTY_PRINT);

if (file_put_contents($tempFile, $jsonData, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save announcement']);
    exit;
}

// Atomic move to final location
if (!rename($tempFile, $dynamicFile)) {
    unlink($tempFile); // Clean up temp file
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save announcement']);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => $mode === 'add' ? 'Announcement created successfully' : 'Announcement updated successfully',
    'announcement' => $announcementData
]);
?>