<?php
require __DIR__.'/../includes/auth.php';
require __DIR__.'/../includes/shift-report-manager.php';
require_login();

try {
    // Filter refunds to remove empty entries
    $refunds = $_POST['refunds'] ?? [];
    $refunds = array_filter($refunds, function($r) {
        return !empty($r['amount']) || !empty($r['reason']) || !empty($r['customer']) || !empty($r['service']) || !empty($r['notes']);
    });

    // Collect form data
    $data = [
        'shift_date' => $_POST['shift_date'] ?? date('Y-m-d'),
        'shift_type' => $_POST['shift_type'] ?? 'morning',
        'location'   => $_POST['location'] ?? '',
        'checklist'  => $_POST['checklist'] ?? [],
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
        // Redirect back to dashboard with success flag
        header('Location: /dashboard.php?ok=1');
        exit;
    } else {
        throw new Exception('Failed to save shift report');
    }

} catch (Exception $e) {
    error_log("Save shift report error: " . $e->getMessage());
    
    // Redirect with error flag
    header('Location: /dashboard.php?error=' . urlencode('Failed to save shift report. Please try again.'));
    exit;
}