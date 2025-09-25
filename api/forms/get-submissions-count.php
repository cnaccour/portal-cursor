<?php
/**
 * Get Form Submissions Count API
 * GET /api/forms/get-submissions-count.php?form_key=time_off_request
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
    
    if (empty($form_key)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Form key is required']);
        exit;
    }
    
    $count = 0;
    
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
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM time_off_requests");
        $count = $stmt->fetchColumn();
    } elseif ($form_key === 'bi_weekly_report') {
        // Ensure bi_weekly_reports table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bi_weekly_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                work_location VARCHAR(100) NOT NULL,
                role VARCHAR(150) NOT NULL,
                period_start_date DATE NOT NULL,
                period_end_date DATE NOT NULL,
                date_range VARCHAR(255),
                hours_worked DECIMAL(8,2) DEFAULT 0,
                satisfaction TINYINT NOT NULL,
                kpi_appointments INT DEFAULT 0,
                kpi_service_sales DECIMAL(10,2) DEFAULT 0,
                kpi_retail_sales DECIMAL(10,2) DEFAULT 0,
                achievements TEXT,
                challenges TEXT,
                client_feedback TEXT,
                training_completed TEXT,
                goals_next TEXT,
                supplies_needed TEXT,
                support_needed TEXT,
                status VARCHAR(50) DEFAULT 'submitted',
                submitted_by INT NULL,
                submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $stmt = $pdo->query("SELECT COUNT(*) FROM bi_weekly_reports");
        $count = $stmt->fetchColumn();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => (int)$count
    ]);
    
} catch (Exception $e) {
    error_log('Get submissions count API error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}