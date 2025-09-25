<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin');

// Handle form settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        require_once __DIR__.'/includes/db.php';
        
        if ($_POST['action'] === 'update_form_settings') {
            // Create forms_config table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS forms_config (
                    id SERIAL PRIMARY KEY,
                    form_key VARCHAR(100) UNIQUE NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    is_active BOOLEAN DEFAULT TRUE,
                    notification_emails JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $form_key = $_POST['form_key'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $notification_emails = array_filter(array_map('trim', explode(',', $_POST['notification_emails'] ?? '')));
            
            // Insert or update form configuration
            // MySQL upsert (cPanel-compatible)
            $stmt = $pdo->prepare("
                INSERT INTO forms_config (form_key, title, description, is_active, notification_emails, updated_at)
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    description = VALUES(description),
                    is_active = VALUES(is_active),
                    notification_emails = VALUES(notification_emails),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$form_key, $title, $description, $is_active, json_encode($notification_emails)]);
            
            $success = "Form settings updated successfully!";
        }
        
    } catch (Exception $e) {
        error_log('Admin forms error: ' . $e->getMessage());
        $error = 'An error occurred while updating form settings.';
    }
}

// Get form configurations
$forms_config = [];
try {
    require_once __DIR__.'/includes/db.php';
    
    // Ensure forms_config table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS forms_config (
            id SERIAL PRIMARY KEY,
            form_key VARCHAR(100) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            notification_emails JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $stmt = $pdo->query("SELECT * FROM forms_config ORDER BY created_at ASC");
    $forms_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default forms if not exists
    $time_off_exists = false;
    $biweekly_exists = false;
    foreach ($forms_config as $config) {
        if ($config['form_key'] === 'time_off_request') {
            $time_off_exists = true;
        }
        if ($config['form_key'] === 'bi_weekly_report') {
            $biweekly_exists = true;
        }
    }
    
    if (!$time_off_exists) {
        $stmt = $pdo->prepare("
            INSERT INTO forms_config (form_key, title, description, is_active, notification_emails) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'time_off_request',
            'Time Off Request',
            'Submit requests for vacation, personal days, sick leave, or other time off needs.',
            true,
            json_encode(['bfernandez@jjosephsalon.com'])
        ]);
        
        // Refresh configs
        $stmt = $pdo->query("SELECT * FROM forms_config ORDER BY created_at ASC");
        $forms_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (!$biweekly_exists) {
        $stmt = $pdo->prepare("\n            INSERT INTO forms_config (form_key, title, description, is_active, notification_emails) \n            VALUES (?, ?, ?, ?, ?)\n        ");
        $stmt->execute([
            'bi_weekly_report',
            'Bi-Weekly Report',
            'Summarize your last two weeks: KPIs, wins, challenges, goals, and requests.',
            true,
            json_encode(['bfernandez@jjosephsalon.com'])
        ]);
        
        // Refresh configs
        $stmt = $pdo->query("SELECT * FROM forms_config ORDER BY created_at ASC");
        $forms_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (Exception $e) {
    error_log('Database error in admin forms: ' . $e->getMessage());
    $forms_config = [];
}

require __DIR__.'/includes/header.php';
?>

<style>
.form-card {
    transition: all 0.2s ease;
}
.form-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
}
.status-active {
    background-color: #D1FAE5;
    color: #065F46;
}
.status-inactive {
    background-color: #FEE2E2;
    color: #991B1B;
}
.gold-focus:focus {
    outline: none;
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
</style>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Forms Administration</h1>
    <p class="text-gray-600">Manage form settings, view submissions, and configure email notifications.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"></path>
            </svg>
            <span class="text-red-800 font-medium"><?= htmlspecialchars($error) ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Forms Management Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <?php foreach ($forms_config as $form): ?>
        <?php 
        $notification_emails = json_decode($form['notification_emails'] ?? '[]', true) ?: [];
        ?>
        <div class="form-card bg-white rounded-xl border border-gray-200 shadow-sm" 
             id="form-card-<?= $form['form_key'] ?>"
             x-data="{ 
            editMode: false,
            submissionsCount: 0
        }">
            <!-- Form Header -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($form['title']) ?></h3>
                        <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($form['description']) ?></p>
                    </div>
                    <span class="status-badge <?= $form['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $form['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span><?= count($notification_emails) ?> email recipient<?= count($notification_emails) !== 1 ? 's' : '' ?></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span x-text="submissionsCount + ' submissions'">Loading...</span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="p-6 bg-gray-50 flex flex-wrap gap-3">
                <button @click="editMode = !editMode" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span x-text="editMode ? 'Cancel' : 'Edit Settings'"></span>
                </button>
                
                <a href="form-submissions.php?form=<?= $form['form_key'] ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View Submissions
                </a>
                
                <?php 
                    $preview_path = '#';
                    if ($form['form_key'] === 'time_off_request') { $preview_path = 'forms/time-off-request.php'; }
                    elseif ($form['form_key'] === 'bi_weekly_report') { $preview_path = 'forms/bi-weekly-report.php'; }
                ?>
                <a href="<?= $preview_path ?>" target="_blank" 
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Preview Form
                </a>
            </div>
            
            <!-- Edit Form Settings -->
            <div x-show="editMode" x-transition class="border-t border-gray-200 p-6">
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="update_form_settings">
                    <input type="hidden" name="form_key" value="<?= htmlspecialchars($form['form_key']) ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Form Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($form['title']) ?>" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 gold-focus">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" <?= $form['is_active'] ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded border-gray-300" style="accent-color: #AF831A;">
                                <span class="text-sm font-medium text-gray-700">Form is Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 gold-focus"><?= htmlspecialchars($form['description']) ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notification Emails</label>
                        <input type="text" name="notification_emails" 
                               value="<?= htmlspecialchars(implode(', ', $notification_emails)) ?>"
                               placeholder="email1@example.com, email2@example.com"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 gold-focus">
                        <p class="text-xs text-gray-500 mt-1">Enter multiple emails separated by commas. These emails will receive notifications when forms are submitted.</p>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="editMode = false" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
            
            
            <script>
                // Load submissions count on page load
                document.addEventListener('DOMContentLoaded', function() {
                    fetch(`./api/forms/get-submissions-count.php?form_key=<?= $form['form_key'] ?>`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const formCard = document.getElementById('form-card-<?= $form['form_key'] ?>');
                                if (formCard && formCard._x_dataStack) {
                                    formCard._x_dataStack[0].submissionsCount = data.count;
                                }
                            }
                        })
                        .catch(error => console.error('Error loading count:', error));
                });
            </script>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($forms_config)): ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Forms Configured</h3>
            <p class="text-gray-600">Forms will appear here once they are created and configured.</p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>