<?php
require __DIR__.'/../includes/auth.php';
require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__.'/../includes/db.php';
        
        // Collect form data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $work_location = $_POST['work_location'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $additional_info = trim($_POST['additional_info'] ?? '');
        $has_compensation = isset($_POST['has_compensation']) ? 1 : 0;
        $understands_blackout = isset($_POST['understands_blackout']) ? 1 : 0;
        $submitted_by = $_SESSION['user_id'];
        
        // Basic validation
        $errors = [];
        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($work_location)) $errors[] = 'Work location is required';
        if (empty($start_date)) $errors[] = 'Start date is required';
        if (empty($end_date)) $errors[] = 'End date is required';
        if (empty($reason)) $errors[] = 'Reason for time off is required';
        if (!$understands_blackout) $errors[] = 'You must acknowledge the blackout dates policy';
        
        // Validate email format
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        // Validate date range
        if (!empty($start_date) && !empty($end_date)) {
            $start = strtotime($start_date);
            $end = strtotime($end_date);
            if ($start > $end) {
                $errors[] = 'End date must be after start date';
            }
            if ($start < strtotime('today')) {
                $errors[] = 'Start date cannot be in the past';
            }
        }
        
        if (empty($errors)) {
            // Create time_off_requests table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS time_off_requests (
                    id SERIAL PRIMARY KEY,
                    full_name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    work_location VARCHAR(100) NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
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
            
            // Insert the time off request
            $stmt = $pdo->prepare("
                INSERT INTO time_off_requests 
                (full_name, email, work_location, start_date, end_date, reason, additional_info, 
                 has_compensation, understands_blackout, submitted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $full_name, $email, $work_location, $start_date, $end_date, 
                $reason, $additional_info, $has_compensation, $understands_blackout, $submitted_by
            ]);
            
            $success = "Your time off request has been submitted successfully! You will be notified when it's reviewed.";
        }
        
    } catch (Exception $e) {
        error_log('Time off request error: ' . $e->getMessage());
        $errors[] = 'An error occurred while submitting your request. Please try again.';
    }
}

require __DIR__.'/../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">
            Submit Time Off Request
        </h1>
        <p class="text-gray-600 mt-2">Request time off for vacation, personal days, sick leave, or other needs.</p>
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

    <?php if (!empty($errors)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="font-medium text-amber-800 mb-2">Please correct the following errors:</div>
            <ul class="text-amber-700 text-sm space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li>• <?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="bg-white rounded-xl border shadow-sm p-6 space-y-8">
        
        <!-- Personal Information -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="full_name" required 
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                           placeholder="Enter your full name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? '') ?>"
                           placeholder="Enter your email"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- Work Location -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Location</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <?php 
                $locations = [
                    'land-o-lakes' => "Land O' Lakes",
                    'lutz' => 'Lutz',
                    'citrus-park' => 'Citrus Park',
                    'odessa' => 'Odessa',
                    'wesley-chapel' => 'Wesley Chapel'
                ];
                foreach ($locations as $value => $label): 
                    $checked = ($_POST['work_location'] ?? '') === $value ? 'checked' : '';
                ?>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="work_location" value="<?= $value ?>" <?= $checked ?> required
                               class="sr-only peer">
                        <div class="p-4 text-center border border-gray-300 rounded-lg peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-400 transition-colors">
                            <svg class="w-5 h-5 mx-auto mb-2 text-gray-500 peer-checked:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($label) ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Time Off Period -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Time Off Period</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Date Range</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                        <input type="date" name="start_date" required 
                               value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">End Date</label>
                        <input type="date" name="end_date" required 
                               value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Reason for Time Off -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reason for Time Off</h3>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <?php 
                $reasons = [
                    'vacation' => [
                        'label' => 'Vacation',
                        'description' => 'Planned time off for leisure',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>'
                    ],
                    'personal' => [
                        'label' => 'Personal Day',
                        'description' => 'Personal matters or rest',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>'
                    ],
                    'sick' => [
                        'label' => 'Sick Leave',
                        'description' => 'Health-related absence',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>'
                    ],
                    'family-emergency' => [
                        'label' => 'Family Emergency',
                        'description' => 'Urgent family matters',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>'
                    ],
                    'other' => [
                        'label' => 'Other',
                        'description' => 'Other circumstances',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>'
                    ]
                ];
                foreach ($reasons as $value => $info): 
                    $checked = ($_POST['reason'] ?? '') === $value ? 'checked' : '';
                ?>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="reason" value="<?= $value ?>" <?= $checked ?> required
                               class="sr-only peer">
                        <div class="p-4 border border-gray-300 rounded-lg peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-400 transition-colors">
                            <div class="flex items-center gap-3 mb-2">
                                <svg class="w-5 h-5 text-gray-500 peer-checked:text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $info['icon'] ?>
                                </svg>
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($info['label']) ?></div>
                            </div>
                            <div class="text-sm text-gray-600"><?= htmlspecialchars($info['description']) ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Additional Details -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Information (Optional)</label>
                <textarea name="additional_info" rows="4" 
                          placeholder="Provide any additional details about your request..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"><?= htmlspecialchars($_POST['additional_info'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Checkboxes -->
        <div class="space-y-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="has_compensation" value="1" 
                       <?= isset($_POST['has_compensation']) ? 'checked' : '' ?>
                       class="mt-1 w-4 h-4 border-gray-300 rounded focus:ring-2" style="accent-color: #AF831A;">
                <span class="text-sm text-gray-700">I have compensation days available for this request</span>
            </label>
            
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="understands_blackout" value="1" required
                       <?= isset($_POST['understands_blackout']) ? 'checked' : '' ?>
                       class="mt-1 w-4 h-4 border-gray-300 rounded focus:ring-2" style="accent-color: #AF831A;">
                <span class="text-sm text-gray-700">I understand that this request may fall during blackout dates and agree to any applicable policies</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" 
                    class="w-full px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium">
                Submit Time Off Request
            </button>
        </div>
    </form>
</div>

<?php require __DIR__.'/../includes/footer.php'; ?>