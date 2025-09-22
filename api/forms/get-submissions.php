<?php
/**
 * Get Form Submissions API
 * GET /api/forms/get-submissions.php?form_key=time_off_request
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../includes/auth.php';

// Ensure user is logged in and has admin access
require_login();
require_role('admin');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../includes/db.php';
    
    $form_key = $_GET['form_key'] ?? '';
    $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 50;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
    
    if (empty($form_key)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Form key is required']);
        exit;
    }
    
    $submissions = [];
    
    // Handle different form types
    if ($form_key === 'time_off_request') {
        // Ensure time_off_requests table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS time_off_requests (
                id SERIAL PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                work_location VARCHAR(100) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                date_range VARCHAR(255),
                reason VARCHAR(100) NOT NULL,
                additional_info TEXT,
                has_compensation BOOLEAN DEFAULT FALSE,
                understands_blackout BOOLEAN DEFAULT FALSE,
                status VARCHAR(50) DEFAULT 'pending',
                submitted_by INTEGER,
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                reviewed_at TIMESTAMP NULL,
                reviewed_by INTEGER NULL
            )
        ");
        
        $stmt = $pdo->prepare("
            SELECT * FROM time_off_requests 
            ORDER BY submitted_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format submissions for display
        $submissions = array_map(function($submission) {
            return [
                'id' => $submission['id'],
                'first_name' => htmlspecialchars($submission['first_name']),
                'last_name' => htmlspecialchars($submission['last_name']),
                'email' => htmlspecialchars($submission['email']),
                'work_location' => htmlspecialchars($submission['work_location']),
                'date_range' => htmlspecialchars($submission['date_range'] ?: 
                    ($submission['start_date'] . ' to ' . $submission['end_date'])),
                'reason' => htmlspecialchars($submission['reason']),
                'additional_info' => htmlspecialchars($submission['additional_info'] ?: ''),
                'status' => htmlspecialchars($submission['status']),
                'submitted_at' => $submission['submitted_at'],
                'has_compensation' => $submission['has_compensation'],
                'understands_blackout' => $submission['understands_blackout']
            ];
        }, $submissions);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'submissions' => $submissions,
        'total' => count($submissions),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    error_log('Get submissions API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}