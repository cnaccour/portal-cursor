<?php
require __DIR__.'/../includes/auth.php';
require_login();
require_role('admin'); // Only admins can upload KB images

header('Content-Type: application/json');

// CSRF protection
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No image uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];

// File validation
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedMimeTypes = [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp'
];

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Check file size
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Image size exceeds 5MB limit']);
    exit;
}

// Check MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid image type. Only JPG, PNG, GIF, and WebP are allowed']);
    exit;
}

// Check file extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file extension']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../attached_assets/kb_images/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$filename = uniqid() . '_' . time() . '.' . $extension;
$filePath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded image']);
    exit;
}

// Generate URL for the image
$imageUrl = 'attached_assets/kb_images/' . $filename;

// Return success response
echo json_encode([
    'success' => true,
    'filename' => $filename,
    'url' => $imageUrl,
    'size' => $file['size']
]);
?>