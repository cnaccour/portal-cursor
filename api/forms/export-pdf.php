<?php
/**
 * Export Form Submissions as PDF-ready HTML
 * GET /api/forms/export-pdf.php?form_key=time_off_request
 */

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Simple authentication check for API endpoints
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    header('Content-Type: text/html');
    echo '<h1>Unauthorized</h1><p>Admin access required</p>';
    exit;
}

try {
    require_once __DIR__ . '/../../includes/db.php';
    
    $form_key = $_GET['form_key'] ?? '';
    
    if (empty($form_key)) {
        throw new Exception('Form key is required');
    }
    
    // Get form configuration
    $stmt = $pdo->prepare("SELECT * FROM forms_config WHERE form_key = ?");
    $stmt->execute([$form_key]);
    $form_config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$form_config) {
        throw new Exception('Form configuration not found');
    }
    
    // Get submissions
    $submissions = [];
    if ($form_key === 'time_off_request') {
        $stmt = $pdo->query("SELECT * FROM time_off_requests ORDER BY submitted_at DESC");
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Set headers for PDF download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $form_config['title'] . '_submissions_' . date('Y-m-d') . '.html"');
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($form_config['title']) ?> - Submissions Report</title>
    <style>
        @media print {
            body { font-size: 12px; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            color: #AF831A;
            font-size: 28px;
        }
        
        .header .subtitle {
            color: #666;
            margin-top: 5px;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .submission {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background: white;
        }
        
        .submission-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .employee-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .status-pending {
            background: #FEF3C7;
            color: #92400E;
        }
        
        .status-approved {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .status-denied {
            background: #FEE2E2;
            color: #991B1B;
        }
        
        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .additional-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: #AF831A;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .print-button:hover {
            background: #92400E;
        }
        
        @media (max-width: 768px) {
            .details {
                grid-template-columns: 1fr;
            }
            
            .print-button {
                position: static;
                width: 100%;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">📄 Print as PDF</button>
    
    <div class="header">
        <h1>J. Joseph Salon - Team Portal</h1>
        <div class="subtitle"><?= htmlspecialchars($form_config['title']) ?> Submissions Report</div>
        <div class="subtitle">Generated on <?= date('F j, Y \a\t g:i A') ?></div>
    </div>
    
    <div class="summary">
        <strong>Summary:</strong> <?= count($submissions) ?> total submissions
        <?php if (!empty($submissions)): ?>
            • Latest: <?= date('M j, Y', strtotime($submissions[0]['submitted_at'])) ?>
            • Oldest: <?= date('M j, Y', strtotime(end($submissions)['submitted_at'])) ?>
        <?php endif; ?>
    </div>
    
    <?php if (empty($submissions)): ?>
        <div class="submission">
            <p style="text-align: center; color: #666; font-style: italic;">No submissions found for this form.</p>
        </div>
    <?php else: ?>
        <?php foreach ($submissions as $index => $submission): ?>
            <div class="submission <?= $index > 0 ? 'page-break' : '' ?>">
                <div class="submission-header">
                    <span class="employee-name">
                        <?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?>
                    </span>
                    <span class="status status-<?= $submission['status'] ?>">
                        <?= ucfirst($submission['status']) ?>
                    </span>
                </div>
                
                <div class="details">
                    <div class="detail-item">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value"><?= htmlspecialchars($submission['email']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Work Location</div>
                        <div class="detail-value"><?= htmlspecialchars($submission['work_location']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Date Range</div>
                        <div class="detail-value"><?= htmlspecialchars($submission['date_range']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Reason</div>
                        <div class="detail-value"><?= htmlspecialchars($submission['reason']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Compensation Available</div>
                        <div class="detail-value"><?= $submission['has_compensation'] ? 'Yes' : 'No' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Policy Acknowledged</div>
                        <div class="detail-value"><?= $submission['understands_blackout'] ? 'Yes' : 'No' ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Submitted Date</div>
                        <div class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['submitted_at'])) ?></div>
                    </div>
                    
                    <?php if (!empty($submission['reviewed_at'])): ?>
                        <div class="detail-item">
                            <div class="detail-label">Reviewed Date</div>
                            <div class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['reviewed_at'])) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($submission['additional_info'])): ?>
                    <div class="additional-info">
                        <div class="detail-label">Additional Information</div>
                        <div class="detail-value"><?= nl2br(htmlspecialchars($submission['additional_info'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px;">
        <p>This report was generated automatically by the JJS Team Portal</p>
        <p>For questions or support, contact your system administrator</p>
    </div>
</body>
</html>