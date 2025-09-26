<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is admin
if (!has_role('admin')) {
    header('Location: ../login.php');
    exit;
}

// Predefined locations - match exactly what's in shift reports
$predefined_locations = [
    'Land O\' Lakes',
    'Odessa', 
    'Citrus Park',
    'Tampa Bay',
    'Corporate Office'
];

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
            
            // Check if location already exists
            $stmt = $pdo->prepare("SELECT id FROM shift_report_email_settings WHERE location = ?");
            $stmt->execute([$location]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE shift_report_email_settings SET email_addresses = ?, is_active = ? WHERE location = ?");
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

// Create settings array keyed by location for easy lookup
$settings_by_location = [];
foreach ($settings as $setting) {
    $settings_by_location[$setting['location']] = $setting;
}

// Handle URL parameters for success/error messages
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shift Report Email Settings - Admin</title>
    <link rel="icon" href="/portal/favicon.ico">
    <link rel="stylesheet" href="/portal/assets/css/tailwind.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="admin.php" class="hover:text-gray-700">Admin Tools</a>
                <span>></span>
                <a href="reports.php" class="hover:text-gray-700">Reports</a>
                <span>></span>
                <span class="text-gray-900">Settings</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Shift Report Email Settings</h1>
            <p class="text-gray-600 mt-1">Configure email notifications for each location</p>
        </div>

        <!-- Success/Error Messages -->
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

        <!-- Location Settings -->
        <div class="space-y-6">
            <?php foreach ($predefined_locations as $location): ?>
                <?php $setting = $settings_by_location[$location] ?? null; ?>
                <div class="bg-white rounded-xl border shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($location) ?></h3>
                        <span class="px-3 py-1 text-xs font-medium rounded-full <?= ($setting && $setting['is_active']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ($setting && $setting['is_active']) ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_settings">
                        <input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                            <input type="text" name="emails" 
                                   value="<?= $setting ? htmlspecialchars(implode(', ', json_decode($setting['email_addresses'], true))) : '' ?>"
                                   required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="email1@example.com, email2@example.com">
                            <p class="text-xs text-gray-500 mt-1">Separate multiple emails with commas</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active_<?= md5($location) ?>" 
                                   <?= ($setting && $setting['is_active']) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                            <label for="is_active_<?= md5($location) ?>" class="ml-2 text-sm text-gray-700">
                                Enable email notifications for this location
                            </label>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>