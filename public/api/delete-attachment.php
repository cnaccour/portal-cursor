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

$filePath = __DIR__.'/../attached_assets/announcements/'.$safeAnnouncementId.'/'.$safeFilename;

// Check if file exists and delete it
if (file_exists($filePath)) {
    if (unlink($filePath)) {
        // Update announcement metadata to reflect file deletion
        $dynamicFile = __DIR__.'/../storage/dynamic-announcements.json';
        if (file_exists($dynamicFile)) {
            $content = file_get_contents($dynamicFile);
            if ($content) {
                $dynamicAnnouncements = json_decode($content, true) ?: [];
                
                // Find the announcement and update its attachments
                foreach ($dynamicAnnouncements as &$announcement) {
                    if ($announcement['id'] === $announcementId) {
                        // Rebuild attachments list by scanning directory
                        $attachmentDir = __DIR__.'/../attached_assets/announcements/'.$announcementId;
                        $attachments = [];
                        if (is_dir($attachmentDir)) {
                            $files = scandir($attachmentDir);
                            foreach ($files as $file) {
                                if ($file !== '.' && $file !== '..' && is_file($attachmentDir.'/'.$file)) {
                                    $parts = explode('_', $file, 2);
                                    $originalName = isset($parts[1]) ? $parts[1] : $file;
                                    $filePath = $attachmentDir.'/'.$file;
                                    
                                    $attachments[] = [
                                        'filename' => $file,
                                        'original_name' => $originalName,
                                        'file_size' => filesize($filePath),
                                        'mime_type' => mime_content_type($filePath),
                                        'upload_date' => date('Y-m-d H:i:s', filemtime($filePath))
                                    ];
                                }
                            }
                        }
                        $announcement['attachments'] = $attachments;
                        break;
                    }
                }
                unset($announcement);
                
                // Save updated announcements
                file_put_contents($dynamicFile, json_encode($dynamicAnnouncements, JSON_PRETTY_PRINT), LOCK_EX);
            }
        }
        
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