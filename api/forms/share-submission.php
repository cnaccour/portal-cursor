<?php
/**
 * Share Form Submission API
 * POST /api/forms/share-submission.php
 */

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Simple authentication check for API endpoints
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF token
    if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid CSRF token'
        ]);
        exit;
    }
    
    $submission_id = $input['submission_id'] ?? '';
    $email = trim($input['email'] ?? '');
    
    // Validate inputs
    if (empty($submission_id) || empty($email)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Submission ID and email are required'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email address'
        ]);
        exit;
    }
    
    require_once __DIR__ . '/../../includes/db.php';
    
    // Get submission details
    $stmt = $pdo->prepare("SELECT * FROM time_off_requests WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Submission not found'
        ]);
        exit;
    }
    
    // Generate email content
    $subject = "Time Off Request - {$submission['first_name']} {$submission['last_name']}";
    
    $message = "
Time Off Request Submission

Employee: {$submission['first_name']} {$submission['last_name']}
Email: {$submission['email']}
Work Location: {$submission['work_location']}
Date Range: {$submission['date_range']}
Reason: {$submission['reason']}
Status: {$submission['status']}
Submitted: {$submission['submitted_at']}

Additional Information:
{$submission['additional_info']}

Compensation Days Available: " . ($submission['has_compensation'] ? 'Yes' : 'No') . "
Blackout Policy Acknowledged: " . ($submission['understands_blackout'] ? 'Yes' : 'No') . "

---
Shared from JJS Team Portal
";

    // For now, we'll use PHP's mail() function
    // In production, you might want to use a more robust email service
    $headers = "From: noreply@jjosephsalon.com\r\n";
    $headers .= "Reply-To: noreply@jjosephsalon.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    $mail_sent = mail($email, $subject, $message, $headers);
    
    if ($mail_sent) {
        // Log the sharing action
        error_log("Form submission shared: ID {$submission_id} to {$email} by user {$_SESSION['user_id']}");
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Submission shared successfully'
        ]);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    error_log('Share submission API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to share submission'
    ]);
}