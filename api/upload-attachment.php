<?php
require __DIR__.'/../includes/auth.php';
require_login();
require_role('admin'); // Only admins can upload attachments

// CSRF protection
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];
$announcementId = $_POST['announcement_id'] ?? '';

if (empty($announcementId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Announcement ID is required']);
    exit;
}

// File validation
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedMimeTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
];

$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

// Check file size
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File size exceeds 10MB limit']);
    exit;
}

// Get and validate file extension
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File type not allowed']);
    exit;
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}

// Create directory for this announcement
$attachmentDir = __DIR__.'/../storage/attachments/'.preg_replace('/[^a-zA-Z0-9-_]/', '', $announcementId);
if (!is_dir($attachmentDir)) {
    if (!mkdir($attachmentDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not create storage directory']);
        exit;
    }
}

// Generate safe filename
$originalName = $file['name'];
$safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $originalName);
$safeName = substr($safeName, 0, 100); // Limit filename length

// Add timestamp to prevent conflicts
$timestamp = time();
$finalName = $timestamp . '_' . $safeName;
$filePath = $attachmentDir . '/' . $finalName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not save file']);
    exit;
}

// Return file metadata
echo json_encode([
    'success' => true,
    'file' => [
        'filename' => $finalName,
        'original_name' => $originalName,
        'file_size' => $file['size'],
        'mime_type' => $mimeType,
        'uploaded_date' => date('Y-m-d H:i:s')
    ]
]);
?>