<?php
// Shift Report Email Settings (Admin)
// Simplified view: always show the 5 predefined locations with inline editing

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Require admin access
if (!has_role('admin')) {
    header('Location: ../login.php');
    exit;
}

$predefined_locations = [
    "Land O' Lakes",
    'Odessa',
    'Citrus Park',
    'Tampa Bay',
    'Corporate Office',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_settings') {
    try {
        $location = $_POST['location'] ?? '';
        $emails = $_POST['emails'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!in_array($location, $predefined_locations, true)) {
            throw new Exception('Invalid location selected.');
        }

        // Validate and normalize email list
        $raw_emails = array_filter(array_map('trim', explode(',', $emails)));
        foreach ($raw_emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address: $email");
            }
        }

        // Ensure table exists
        $pdo->exec('CREATE TABLE IF NOT EXISTS shift_report_email_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            location VARCHAR(255) NOT NULL UNIQUE,
            email_addresses JSON NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        // Upsert settings for the location
        $stmt = $pdo->prepare('INSERT INTO shift_report_email_settings (location, email_addresses, is_active)
            VALUES (:location, :emails, :is_active)
            ON DUPLICATE KEY UPDATE email_addresses = VALUES(email_addresses), is_active = VALUES(is_active)');
        $stmt->execute([
            ':location'   => $location,
            ':emails'     => json_encode(array_values($raw_emails), JSON_UNESCAPED_SLASHES),
            ':is_active'  => $is_active,
        ]);

        $success_message = "Email settings updated successfully for $location";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Load existing settings keyed by location
$settings_by_location = [];
try {
    $stmt = $pdo->query('SELECT * FROM shift_report_email_settings');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settings_by_location[$row['location']] = $row;
    }
} catch (Exception $e) {
    $error_message = $error_message ?? ('Error loading settings: ' . $e->getMessage());
}

include __DIR__ . '/includes/header.php';
?>

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
        <p class="text-gray-600 mt-1">Manage notification recipients for each salon location.</p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-800 font-medium"><?= htmlspecialchars($success_message) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-800 font-medium"><?= htmlspecialchars($error_message) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <?php foreach ($predefined_locations as $location): ?>
            <?php
                $setting = $settings_by_location[$location] ?? null;
                $decoded_emails = [];

                if ($setting) {
                    $raw = $setting['email_addresses'] ?? '';
                    $decoded = json_decode($raw, true);

                    if (is_array($decoded)) {
                        $decoded_emails = array_filter(array_map('trim', $decoded));
                    } elseif (is_string($raw) && $raw !== '') {
                        // Fallback for legacy comma-separated strings
                        $decoded_emails = array_filter(array_map('trim', explode(',', $raw)));
                    }
                }

                $emails_value = htmlspecialchars(implode(', ', $decoded_emails));
                $is_active = $setting ? (int)$setting['is_active'] === 1 : true;
            ?>
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($location) ?></h3>
                    <span class="px-3 py-1 text-xs font-medium rounded-full <?= $is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                        <?= $is_active ? 'Active' : 'Inactive' ?>
                    </span>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update_settings">
                    <input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                        <input type="text" name="emails"
                               value="<?= $emails_value ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="email1@example.com, email2@example.com">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple emails with commas</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active_<?= md5($location) ?>"
                               <?= $is_active ? 'checked' : '' ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>