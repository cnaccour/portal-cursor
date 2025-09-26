<?php
require __DIR__.'/includes/auth.php';
require __DIR__.'/includes/header.php';

// Ensure user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

// Debug: Log all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_content = date('Y-m-d H:i:s') . " POST: " . print_r($_POST, true) . "\n";
    file_put_contents(__DIR__ . '/debug.log', $debug_content, FILE_APPEND);
    file_put_contents('/tmp/portal_debug.log', $debug_content, FILE_APPEND);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        try {
            $location = $_POST['location'];
            $emails = $_POST['emails'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validate emails
            $email_array = array_filter(array_map('trim', explode(',', $emails)));
            foreach ($email_array as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email address: $email");
                }
            }
            
            // Check if location exists
            $checkStmt = $pdo->prepare("SELECT id FROM shift_report_email_settings WHERE location = ?");
            $checkStmt->execute([$location]);
            
            if ($checkStmt->fetch()) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE shift_report_email_settings SET email_addresses = ?, is_active = ?, updated_at = NOW() WHERE location = ?");
                $stmt->execute([json_encode($email_array), $is_active, $location]);
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO shift_report_email_settings (location, email_addresses, is_active) VALUES (?, ?, ?)");
                $stmt->execute([$location, json_encode($email_array), $is_active]);
            }
            
            $success_message = "Email settings updated successfully for $location";
            // Don't redirect - show success message on same page like test_email
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            // Don't redirect - show error message on same page like test_email
        }
    } elseif ($_POST['action'] === 'delete_setting') {
        try {
            $location = $_POST['location'];
            $stmt = $pdo->prepare("DELETE FROM shift_report_email_settings WHERE location = ?");
            $stmt->execute([$location]);
            $success_message = "Email settings deleted for $location";
            // Don't redirect - show success message on same page like test_email
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            // Don't redirect - show error message on same page like test_email
        }
    } elseif ($_POST['action'] === 'test_email') {
        file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " ENTERING test_email processing\n", FILE_APPEND);
        
        try {
            $location = $_POST['location'] ?? '';
            $test_email = $_POST['test_email'] ?? '';
            
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Extracted data: location='$location', email='$test_email'\n", FILE_APPEND);
            
            if (empty($location)) {
                throw new Exception("Location is required");
            }
            
            if (empty($test_email)) {
                throw new Exception("Test email address is required");
            }
            
            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid test email address: $test_email");
            }
            
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Validation passed, loading Email class\n", FILE_APPEND);
            
            // Send test email - use same pattern as forgot-password.php
            if (file_exists(__DIR__ . '/lib/Email.php')) {
                require_once __DIR__ . '/lib/Email.php';
                file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Email class loaded from /lib/Email.php\n", FILE_APPEND);
            } elseif (file_exists(__DIR__ . '/../lib/Email.php')) {
                require_once __DIR__ . '/../lib/Email.php';
                file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Email class loaded from /../lib/Email.php\n", FILE_APPEND);
            } else {
                throw new Exception('Email library not found');
            }
            
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Email functions loaded, preparing email\n", FILE_APPEND);
            
            $subject = "Test Email - Shift Report Settings for $location";
            $html_body = generateShiftReportEmailHTML([
                'user_name' => 'Test User',
                'location' => $location,
                'shift_date' => date('Y-m-d'),
                'shift_type' => 'Test Shift',
                'reviews_count' => 5,
                'shipments' => ['vendor' => 'Test Vendor', 'notes' => 'Test shipment notes'],
                'refunds_count' => 2,
                'refunds_amount' => 45.50,
                'refunds' => [
                    ['amount' => 25.00, 'reason' => 'Product defect', 'customer' => 'Jane Smith', 'service' => 'Hair color', 'notes' => 'Color did not match expectations'],
                    ['amount' => 20.50, 'reason' => 'Service issue', 'customer' => 'John Doe', 'service' => 'Haircut', 'notes' => 'Customer not satisfied with length']
                ],
                'checklist_completed' => 8,
                'checklist_total' => 10,
                'checklist' => [
                    ['label' => 'Count your drawer', 'done' => true],
                    ['label' => 'Prepare daily cleaning sheet', 'done' => true],
                    ['label' => 'Clean lobby', 'done' => false],
                    ['label' => 'Check appointment book', 'done' => true]
                ],
                'notes' => 'This is a test email to verify the shift report email settings are working correctly. All systems functioning normally.'
            ]);
            
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Email content generated, attempting to send\n", FILE_APPEND);
            $result = send_smtp_email($test_email, $subject, $html_body);
            file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . " Email send result: " . print_r($result, true) . "\n", FILE_APPEND);
            
            if ($result['success']) {
                $success_message = "Test email sent successfully to $test_email";
                // Log success for debugging
                error_log("Test email sent successfully to $test_email for location $location");
            } else {
                $error_message = "Failed to send test email: " . ($result['error'] ?? 'Unknown error');
                error_log("Failed to send test email to $test_email for location $location: " . ($result['error'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            $error_message = "Error sending test email: " . $e->getMessage();
            error_log("Test email error: " . $e->getMessage());
        }
        
        // Don't redirect for test email - show message on same page
        // Only redirect for other actions to prevent form resubmission
    }
}

// Get all settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT * FROM shift_report_email_settings ORDER BY location");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error_message = "Error loading settings: " . $e->getMessage();
}

// Predefined locations - match exactly what's in shift reports
$predefined_locations = [
    'Land O\' Lakes',
    'Odessa', 
    'Citrus Park',
    'Tampa Bay',
    'Corporate Office'
];

// Debug: show what we have
if (isset($_GET['debug'])) {
    echo '<pre>Predefined: '; print_r($predefined_locations); echo '</pre>';
    echo '<pre>Existing: '; print_r($existing_locations); echo '</pre>';
    echo '<pre>Available: '; print_r($available_locations); echo '</pre>';
}

// Get locations that already have settings
$existing_locations = [];
try {
    $stmt = $pdo->query("SELECT location FROM shift_report_email_settings ORDER BY location");
    $existing_locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Ignore error, will use empty array
}

// Available locations for dropdown (not yet configured)
$available_locations = array_diff($predefined_locations, $existing_locations);

// Always show debug info for now to troubleshoot
echo '<div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">';
echo '<h3 class="font-semibold text-yellow-800 mb-2">Debug Info:</h3>';
echo '<p><strong>Predefined locations:</strong> ' . implode(', ', $predefined_locations) . '</p>';
echo '<p><strong>Existing locations:</strong> ' . implode(', ', $existing_locations) . '</p>';
echo '<p><strong>Available locations:</strong> ' . implode(', ', $available_locations) . '</p>';
echo '<p><strong>Count:</strong> ' . count($available_locations) . ' available</p>';
echo '</div>';

// Handle URL parameters for success/error messages
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}

function generateShiftReportEmailHTML($data) {
    // Build checklist HTML
    $checklistHTML = '';
    if (!empty($data['checklist'])) {
        foreach ($data['checklist'] as $item) {
            $status = $item['done'] ? '✓' : '✗';
            $statusClass = $item['done'] ? 'done' : 'pending';
            $checklistHTML .= '<div class="checklist-item ' . $statusClass . '">' . $status . ' ' . htmlspecialchars($item['label']) . '</div>';
        }
    }
    
    // Build refunds HTML
    $refundsHTML = '';
    if (!empty($data['refunds'])) {
        foreach ($data['refunds'] as $refund) {
            $refundsHTML .= '<div class="refund-item">
                <div class="refund-header">$' . number_format($refund['amount'], 2) . ' - ' . htmlspecialchars($refund['reason']) . '</div>
                <div class="refund-details">Customer: ' . htmlspecialchars($refund['customer']) . ' | Service: ' . htmlspecialchars($refund['service']) . '</div>';
            if (!empty($refund['notes'])) {
                $refundsHTML .= '<div class="refund-notes">Notes: ' . htmlspecialchars($refund['notes']) . '</div>';
            }
            $refundsHTML .= '</div>';
        }
    }
    
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shift Report - ' . htmlspecialchars($data['location']) . '</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.5; color: #374151; margin: 0; padding: 20px; background-color: #f9fafb; }
            .container { max-width: 700px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
            .header { background: #111827; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
            .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
            .header p { margin: 5px 0 0 0; opacity: 0.8; font-size: 14px; }
            .content { padding: 20px; }
            .section { margin-bottom: 25px; }
            .section-title { font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 12px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
            .info-item { padding: 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
            .info-label { font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 2px; }
            .info-value { font-size: 14px; font-weight: 500; color: #111827; }
            .stats-row { display: flex; gap: 15px; margin-bottom: 15px; }
            .stat-item { flex: 1; text-align: center; padding: 15px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
            .stat-number { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 2px; }
            .stat-label { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 500; }
            .checklist-item { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
            .checklist-item.done { color: #059669; }
            .checklist-item.pending { color: #dc2626; }
            .refund-item { padding: 12px; background: #fef3c7; border-radius: 6px; margin-bottom: 10px; border: 1px solid #f59e0b; }
            .refund-header { font-weight: 600; color: #92400e; margin-bottom: 4px; }
            .refund-details { font-size: 13px; color: #78350f; margin-bottom: 4px; }
            .refund-notes { font-size: 12px; color: #78350f; font-style: italic; }
            .shipment-item { padding: 12px; background: #e0f2fe; border-radius: 6px; border: 1px solid #0284c7; }
            .shipment-label { font-weight: 600; color: #0c4a6e; margin-bottom: 4px; }
            .shipment-content { font-size: 14px; color: #0c4a6e; }
            .notes-section { padding: 15px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
            .notes-content { color: #374151; line-height: 1.4; }
            .footer { padding: 15px 20px; text-align: center; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 8px 8px; }
            .footer p { margin: 0; font-size: 12px; color: #6b7280; }
            @media (max-width: 600px) {
                .info-grid { grid-template-columns: 1fr; }
                .stats-row { flex-direction: column; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Shift Report Submitted</h1>
                <p>' . htmlspecialchars($data['location']) . ' - ' . htmlspecialchars($data['shift_date']) . '</p>
            </div>
            
            <div class="content">
                <div class="section">
                    <div class="section-title">Shift Information</div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Employee</div>
                            <div class="info-value">' . htmlspecialchars($data['user_name']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Shift Type</div>
                            <div class="info-value">' . htmlspecialchars($data['shift_type']) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">Summary</div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-number">' . ($data['checklist_completed'] ?? 0) . '/' . ($data['checklist_total'] ?? 0) . '</div>
                            <div class="stat-label">Tasks Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">' . ($data['reviews_count'] ?? 0) . '</div>
                            <div class="stat-label">Customer Reviews</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">' . ($data['refunds_count'] ?? 0) . '</div>
                            <div class="stat-label">Refunds</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">$' . number_format($data['refunds_amount'] ?? 0, 2) . '</div>
                            <div class="stat-label">Refund Amount</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <div class="section-title">Checklist</div>
                    <div class="checklist-section">
                        ' . ($checklistHTML ?: '<div class="info-value">No checklist items</div>') . '
                    </div>
                </div>
                
                ' . (!empty($data['shipments']['vendor']) || !empty($data['shipments']['notes']) ? '
                <div class="section">
                    <div class="section-title">Shipments & Deliveries</div>
                    <div class="shipment-item">
                        ' . (!empty($data['shipments']['vendor']) ? '<div class="shipment-label">Vendor: <span class="shipment-content">' . htmlspecialchars($data['shipments']['vendor']) . '</span></div>' : '') . '
                        ' . (!empty($data['shipments']['notes']) ? '<div class="shipment-label">Notes: <span class="shipment-content">' . htmlspecialchars($data['shipments']['notes']) . '</span></div>' : '') . '
                    </div>
                </div>
                ' : '') . '
                
                ' . (!empty($refundsHTML) ? '
                <div class="section">
                    <div class="section-title">Refunds & Returns</div>
                    ' . $refundsHTML . '
                </div>
                ' : '') . '
                
                <div class="section">
                    <div class="section-title">Shift Notes</div>
                    <div class="notes-section">
                        <div class="notes-content">' . htmlspecialchars($data['notes'] ?? 'No additional notes') . '</div>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>J. Joseph Salon Portal - Automated Notification</p>
            </div>
        </div>
    </body>
    </html>';
}
?>

<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="admin.php" class="hover:text-gray-700">Admin Tools</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <a href="reports.php" class="hover:text-gray-700">Reports</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-900">Settings</span>
    </div>
    <h1 class="text-2xl font-semibold">Reports Settings</h1>
    <p class="text-gray-600 mt-2">Configure automatic email notifications for shift reports by location.</p>
</div>

<?php if (isset($success_message)): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success_message) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-red-800 font-medium"><?= htmlspecialchars($error_message) ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Add New Setting -->
<div class="bg-white rounded-xl border shadow-sm p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New Location Setting</h2>
    <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="update_settings">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <?php if (!empty($available_locations)): ?>
                    <select name="location" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">Select a location...</option>
                        <?php foreach ($available_locations as $location): ?>
                            <option value="<?= htmlspecialchars($location) ?>"><?= htmlspecialchars($location) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                        All locations have been configured
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                <input type="text" name="emails" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                       placeholder="email1@example.com, email2@example.com">
                <p class="text-xs text-gray-500 mt-1">Separate multiple emails with commas</p>
            </div>
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" checked 
                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 text-sm text-gray-700">Enable email notifications for this location</label>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" 
                    <?php if (empty($available_locations)): ?>disabled<?php endif; ?>
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 <?= empty($available_locations) ? 'bg-gray-400 cursor-not-allowed' : 'bg-gray-900 hover:bg-gray-800' ?> text-white rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <?= empty($available_locations) ? 'All Locations Configured' : 'Add Setting' ?>
            </button>
        </div>
    </form>
</div>

<!-- Existing Settings -->
<div class="bg-white rounded-xl border shadow-sm">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">Current Settings</h2>
    </div>
    
    <div class="p-6">
        <?php if (empty($settings)): ?>
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p>No email settings configured yet</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($settings as $setting): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($setting['location']) ?></h3>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?= $setting['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $setting['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Test Email Button -->
                                <button class="test-email-btn px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 border border-blue-300 rounded hover:bg-blue-50 transition-colors"
                                        data-location="<?= htmlspecialchars($setting['location']) ?>">
                                    Test Email
                                </button>
                                
                                <!-- Edit Button -->
                                <button class="edit-btn px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800 border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                                        data-location="<?= htmlspecialchars($setting['location']) ?>"
                                        data-emails="<?= htmlspecialchars($setting['email_addresses']) ?>"
                                        data-active="<?= $setting['is_active'] ? '1' : '0' ?>">
                                    Edit
                                </button>
                                
                                <!-- Delete Button -->
                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete email settings for <?= htmlspecialchars($setting['location']) ?>?')">
                                    <input type="hidden" name="action" value="delete_setting">
                                    <input type="hidden" name="location" value="<?= htmlspecialchars($setting['location']) ?>">
                                    <button type="submit" 
                                            class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800 border border-red-300 rounded hover:bg-red-50 transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <strong>Email Addresses:</strong>
                            <?php 
                            $emails = json_decode($setting['email_addresses'], true) ?: [];
                            echo htmlspecialchars(implode(', ', $emails));
                            ?>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-2">
                            Last updated: <?= date('M j, Y g:i A', strtotime($setting['updated_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

        <!-- Edit Modal -->
        <div id="editModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Email Settings</h3>
                <form method="POST" action="admin-reports-settings.php" id="editForm">
                    <input type="hidden" name="action" value="update_settings">
                    <input type="hidden" name="location" id="editLocation">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                            <input type="text" name="emails" id="editEmails" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="email1@example.com, email2@example.com">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="editIsActive" 
                                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <label for="editIsActive" class="ml-2 text-sm text-gray-700">Enable email notifications</label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

        <!-- Test Email Modal -->
        <div id="testModal" class="fixed inset-0 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Send Test Email</h3>
                <form method="POST" action="admin-reports-settings.php" id="testForm">
                    <input type="hidden" name="action" value="test_email">
                    <input type="hidden" name="location" id="testLocation">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Email Address</label>
                            <input type="email" name="test_email" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="test@example.com">
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            A test shift report email will be sent to verify the settings are working correctly.
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeTestModal()" 
                                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Send Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditModal(location, emails, isActive) {
    document.getElementById('editLocation').value = location;
    // Parse JSON emails and convert back to comma-separated string
    try {
        const emailArray = JSON.parse(emails);
        document.getElementById('editEmails').value = Array.isArray(emailArray) ? emailArray.join(', ') : emails;
    } catch (e) {
        document.getElementById('editEmails').value = emails;
    }
    document.getElementById('editIsActive').checked = (isActive === true || isActive === '1' || isActive === 1);
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function openTestModal(location) {
    document.getElementById('testLocation').value = location;
    document.getElementById('testModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}

// Add event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Test email buttons
    document.querySelectorAll('.test-email-btn').forEach(button => {
        button.addEventListener('click', function() {
            const location = this.getAttribute('data-location');
            openTestModal(location);
        });
    });
    
    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const location = this.getAttribute('data-location');
            const emails = this.getAttribute('data-emails');
            const isActive = this.getAttribute('data-active') === '1';
            openEditModal(location, emails, isActive);
        });
    });
    
    // Close modals when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    document.getElementById('testModal').addEventListener('click', function(e) {
        if (e.target === this) closeTestModal();
    });
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>
