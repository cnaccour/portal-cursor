<?php
require __DIR__.'/includes/auth.php';
require __DIR__.'/includes/header.php';

// Ensure user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_settings') {
        try {
            $location = $_POST['location'];
            $emails = $_POST['emails'];
            $is_active = isset($_POST['is_enabled']) ? 1 : 0;
            
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
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete_setting') {
        try {
            $location = $_POST['location'];
            $stmt = $pdo->prepare("DELETE FROM shift_report_email_settings WHERE location = ?");
            $stmt->execute([$location]);
            $success_message = "Email settings deleted for $location";
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    } elseif ($_POST['action'] === 'test_email') {
        try {
            $location = $_POST['location'];
            $test_email = $_POST['test_email'];
            
            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid test email address");
            }
            
            // Send test email
            require_once __DIR__ . '/lib/Email.php';
            $email = new Email();
            
            $subject = "Test Email - Shift Report Settings for $location";
            $html_body = generateShiftReportEmailHTML([
                'user_name' => 'Test User',
                'location' => $location,
                'shift_date' => date('Y-m-d'),
                'shift_type' => 'Test Shift',
                'refunds_count' => 2,
                'refunds_amount' => 45.50,
                'checklist_completed' => 8,
                'checklist_total' => 10,
                'notes' => 'This is a test email to verify the shift report email settings are working correctly.'
            ]);
            
            $result = $email->send_smtp_email($test_email, $subject, $html_body);
            
            if ($result) {
                $success_message = "Test email sent successfully to $test_email";
            } else {
                $error_message = "Failed to send test email";
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
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

// Predefined locations
$predefined_locations = [
    'Land O\' Lakes',
    'Odessa', 
    'Citrus Park',
    'Tampa Bay',
    'Corporate Office'
];

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

function generateShiftReportEmailHTML($data) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shift Report - ' . htmlspecialchars($data['location']) . '</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: linear-gradient(135deg, #AF831A 0%, #8B6914 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
            .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 16px; }
            .content { padding: 30px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
            .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #AF831A; }
            .info-label { font-size: 12px; text-transform: uppercase; font-weight: 600; color: #6b7280; margin-bottom: 4px; }
            .info-value { font-size: 16px; font-weight: 500; color: #111827; }
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 25px; }
            .stat-item { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; }
            .stat-number { font-size: 24px; font-weight: 700; color: #AF831A; margin-bottom: 4px; }
            .stat-label { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 500; }
            .notes-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
            .notes-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
            .notes-content { color: #6b7280; line-height: 1.5; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; }
            .footer p { margin: 0; font-size: 14px; color: #6b7280; }
            .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
            .badge-success { background: #d1fae5; color: #065f46; }
            .badge-warning { background: #fef3c7; color: #92400e; }
            @media (max-width: 600px) {
                .info-grid { grid-template-columns: 1fr; }
                .stats-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📋 Shift Report Submitted</h1>
                <p>' . htmlspecialchars($data['location']) . ' • ' . htmlspecialchars($data['shift_date']) . '</p>
            </div>
            
            <div class="content">
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
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">' . $data['checklist_completed'] . '/' . $data['checklist_total'] . '</div>
                        <div class="stat-label">Tasks Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">' . $data['refunds_count'] . '</div>
                        <div class="stat-label">Refunds</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">$' . number_format($data['refunds_amount'], 2) . '</div>
                        <div class="stat-label">Refund Amount</div>
                    </div>
                </div>
                
                <div class="notes-section">
                    <div class="notes-label">📝 Shift Notes</div>
                    <div class="notes-content">' . htmlspecialchars($data['notes']) . '</div>
                </div>
            </div>
            
            <div class="footer">
                <p>This is an automated notification from the J. Joseph Salon Portal</p>
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
            <input type="checkbox" name="is_enabled" id="is_enabled" checked 
                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
            <label for="is_enabled" class="ml-2 text-sm text-gray-700">Enable email notifications for this location</label>
        </div>
        
        <?php if (!empty($available_locations)): ?>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Setting
            </button>
        <?php endif; ?>
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
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($setting['location']) ?></h3>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?= $setting['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $setting['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <!-- Test Email Button -->
                                <button onclick="openTestModal('<?= htmlspecialchars($setting['location']) ?>')" 
                                        class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 border border-blue-300 rounded hover:bg-blue-50 transition-colors">
                                    Test Email
                                </button>
                                
                                <!-- Edit Button -->
                                <button onclick="openEditModal('<?= htmlspecialchars($setting['location']) ?>', '<?= htmlspecialchars($setting['email_addresses']) ?>', <?= $setting['is_active'] ? 'true' : 'false' ?>)" 
                                        class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
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
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Email Settings</h3>
                <form method="POST" id="editForm">
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
                            <input type="checkbox" name="is_enabled" id="editIsActive" 
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
                                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Send Test Email</h3>
                <form method="POST" id="testForm">
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
    document.getElementById('editIsActive').checked = isActive;
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

// Close modals when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

document.getElementById('testModal').addEventListener('click', function(e) {
    if (e.target === this) closeTestModal();
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>
