<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/shift-report-manager.php';
require __DIR__.'/../includes/notification-manager.php';
require __DIR__.'/../includes/shift-report-email-manager.php';
require_login();

try {
    // Handle JSON data for checklist and refunds
    $checklist = $_POST['checklist'] ?? [];
    if (is_string($checklist)) {
        $checklist = json_decode($checklist, true) ?? [];
    }
    
    $refunds = $_POST['refunds'] ?? [];
    if (is_string($refunds)) {
        $refunds = json_decode($refunds, true) ?? [];
    }
    
    // Filter refunds to remove empty entries
    $refunds = array_filter($refunds, function($r) {
        return !empty($r['amount']) || !empty($r['reason']) || !empty($r['customer']) || !empty($r['service']) || !empty($r['notes']);
    });

    // Collect form data
    $data = [
        'shift_date' => $_POST['shift_date'] ?? date('Y-m-d'),
        'shift_type' => $_POST['shift_type'] ?? 'morning',
        'location'   => $_POST['location'] ?? '',
        'checklist'  => $checklist,
        'reviews'    => $_POST['reviews_count'] ?? 0,
        'shipments'  => [
            'status' => $_POST['shipments'] ?? 'no',
            'vendor' => $_POST['shipment_vendor'] ?? '',
            'notes'  => $_POST['shipment_notes'] ?? '',
        ],
        'refunds'    => array_values($refunds),
        'notes'      => $_POST['notes'] ?? ''
    ];

    // Validate required fields
    if (empty($data['shift_date']) || empty($data['shift_type']) || empty($data['location'])) {
        throw new Exception('Missing required fields: shift date, type, or location');
    }

    // Save using ShiftReportManager
    $shiftManager = ShiftReportManager::getInstance();
    $reportId = $shiftManager->saveShiftReport($data);

    if ($reportId) {
        // Get user info for notification
        $user_name = $_SESSION['user_name'] ?? 'Unknown User';
        $location = $data['location'] ?? 'Unknown Location';
        
        // Debug logging
        error_log("Attempting to send notification for shift report ID: $reportId");
        error_log("User name: $user_name, Location: $location");
        
        // Send notification to managers and admins
        $notificationResult = NotificationManager::notify_roles(['admin', 'manager'], [
            'type' => 'shift_report',
            'title' => 'Shift Report Submitted',
            'message' => "$user_name has submitted their shift report @ $location",
            'link_url' => '/portal/reports.php',
            'icon' => 'clipboard-check'
        ]);
        
        error_log("Notification result: " . ($notificationResult ? 'SUCCESS' : 'FAILED'));
        
        // Send email notifications based on location
        $emailManager = ShiftReportEmailManager::getInstance();
        $emailData = array_merge($data, [
            'user_name' => $user_name,
            'report_id' => $reportId
        ]);
        
        $emailResult = $emailManager->sendShiftReportNotifications($emailData);
        error_log("Email notification result: " . ($emailResult ? 'SUCCESS' : 'FAILED'));
        
        // Redirect back to forms page with success flag
        header('Location: /portal/forms.php?ok=1');
        exit;
    } else {
        throw new Exception('Failed to save shift report');
    }

} catch (Exception $e) {
    error_log("Save shift report error: " . $e->getMessage());
    
    // Redirect with error flag
    header('Location: /portal/forms.php?error=' . urlencode('Failed to save shift report. Please try again.'));
    exit;
}