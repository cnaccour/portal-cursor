<?php
require __DIR__.'/../includes/auth.php';
require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__.'/../includes/db.php';
        
        // Collect form data
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
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
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
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
                    first_name VARCHAR(255) NOT NULL,
                    last_name VARCHAR(255) NOT NULL,
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
                (first_name, last_name, email, work_location, start_date, end_date, reason, additional_info, 
                 has_compensation, understands_blackout, submitted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $first_name, $last_name, $email, $work_location, $start_date, $end_date, 
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

<style>
/* Custom focus styles for gold theme */
.focus-gold:focus {
    outline: none !important;
    border-color: #AF831A !important;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1) !important;
}

.focus-gold:focus-within {
    border-color: #AF831A !important;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1) !important;
}

/* Modern select styling */
.modern-select {
    background-image: url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23AF831A' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6,9 12,15 18,9'></polyline></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

/* Date range display */
.date-summary {
    background: linear-gradient(135deg, #AF831A 0%, #D4AF37 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
    margin-top: 8px;
}

/* Modern checkbox styling */
.modern-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #AF831A;
    cursor: pointer;
}

.modern-checkbox:focus {
    outline: 2px solid #AF831A;
    outline-offset: 2px;
}
</style>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Submit Time Off Request</h1>
        <p class="text-gray-600">Request time off for vacation, personal days, sick leave, or other needs.</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="text-green-800 font-medium"><?= htmlspecialchars($success) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"></path>
                    </svg>
                </div>
                <div>
                    <div class="font-medium text-red-800 mb-2">Please correct the following errors:</div>
                    <ul class="text-red-700 space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li class="flex items-center gap-2">
                                <div class="w-1 h-1 bg-red-500 rounded-full"></div>
                                <?= htmlspecialchars($error) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-8" x-data="timeOffForm()">
        
        <!-- Personal Information -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" required 
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                           placeholder="Enter your first name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" required 
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                           placeholder="Enter your last name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? '') ?>"
                           placeholder="Enter your email address"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white">
                </div>
            </div>
        </div>

        <!-- Work Location -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Work Location</h2>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Select Your Location</label>
                <select name="work_location" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white modern-select appearance-none">
                    <option value="">Choose your work location...</option>
                    <option value="land-o-lakes" <?= ($_POST['work_location'] ?? '') === 'land-o-lakes' ? 'selected' : '' ?>>Land O' Lakes</option>
                    <option value="lutz" <?= ($_POST['work_location'] ?? '') === 'lutz' ? 'selected' : '' ?>>Lutz</option>
                    <option value="citrus-park" <?= ($_POST['work_location'] ?? '') === 'citrus-park' ? 'selected' : '' ?>>Citrus Park</option>
                    <option value="odessa" <?= ($_POST['work_location'] ?? '') === 'odessa' ? 'selected' : '' ?>>Odessa</option>
                    <option value="wesley-chapel" <?= ($_POST['work_location'] ?? '') === 'wesley-chapel' ? 'selected' : '' ?>>Wesley Chapel</option>
                </select>
            </div>
        </div>

        <!-- Time Off Period -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Time Off Period</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" required 
                           value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>"
                           x-model="startDate"
                           @change="calculateDays"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white">
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" required 
                           value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>"
                           x-model="endDate"
                           @change="calculateDays"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white">
                </div>
            </div>
            <div x-show="dayCount > 0" x-transition class="date-summary mt-4">
                <span x-text="dayCount === 1 ? '1 day selected' : dayCount + ' days selected'"></span>
                <span class="mx-2">•</span>
                <span x-text="formatDateRange()"></span>
            </div>
        </div>

        <!-- Reason for Time Off -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Reason for Time Off</h2>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Select Reason</label>
                <select name="reason" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white modern-select appearance-none">
                    <option value="">Choose your reason...</option>
                    <option value="vacation" <?= ($_POST['reason'] ?? '') === 'vacation' ? 'selected' : '' ?>>Vacation - Planned time off for leisure</option>
                    <option value="personal" <?= ($_POST['reason'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal Day - Personal matters or rest</option>
                    <option value="sick" <?= ($_POST['reason'] ?? '') === 'sick' ? 'selected' : '' ?>>Sick Leave - Health-related absence</option>
                    <option value="family-emergency" <?= ($_POST['reason'] ?? '') === 'family-emergency' ? 'selected' : '' ?>>Family Emergency - Urgent family matters</option>
                    <option value="other" <?= ($_POST['reason'] ?? '') === 'other' ? 'selected' : '' ?>>Other - Please specify in additional details</option>
                </select>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Additional Details</h2>
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Additional Information <span class="text-gray-500 font-normal">(Optional)</span></label>
                <textarea name="additional_info" rows="4" 
                          placeholder="Provide any additional details about your request..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus-gold transition-all duration-200 bg-gray-50 focus:bg-white resize-none"><?= htmlspecialchars($_POST['additional_info'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Acknowledgments -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Acknowledgments</h2>
            <div class="space-y-6">
                <label class="flex items-start gap-4 cursor-pointer group">
                    <input type="checkbox" name="has_compensation" value="1" 
                           <?= isset($_POST['has_compensation']) ? 'checked' : '' ?>
                           class="modern-checkbox flex-shrink-0 mt-0.5">
                    <span class="text-gray-700 group-hover:text-gray-900 transition-colors">I have compensation days available for this request</span>
                </label>
                
                <label class="flex items-start gap-4 cursor-pointer group">
                    <input type="checkbox" name="understands_blackout" value="1" required
                           <?= isset($_POST['understands_blackout']) ? 'checked' : '' ?>
                           class="modern-checkbox flex-shrink-0 mt-0.5">
                    <span class="text-gray-700 group-hover:text-gray-900 transition-colors">I understand that this request may fall during blackout dates and agree to any applicable policies</span>
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <button type="submit" 
                    class="w-full px-8 py-4 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl hover:from-gray-800 hover:to-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 transition-all duration-200 font-semibold text-lg shadow-lg">
                Submit Time Off Request
            </button>
        </div>
    </form>
</div>

<script>
function timeOffForm() {
    return {
        startDate: '<?= $_POST['start_date'] ?? '' ?>',
        endDate: '<?= $_POST['end_date'] ?? '' ?>',
        dayCount: 0,
        
        calculateDays() {
            if (this.startDate && this.endDate) {
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                
                if (end >= start) {
                    const timeDiff = end.getTime() - start.getTime();
                    this.dayCount = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
                } else {
                    this.dayCount = 0;
                }
            } else {
                this.dayCount = 0;
            }
        },
        
        formatDateRange() {
            if (this.startDate && this.endDate) {
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                
                const formatDate = (date) => {
                    return date.toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric',
                        year: start.getFullYear() !== end.getFullYear() ? 'numeric' : undefined
                    });
                };
                
                return formatDate(start) + ' to ' + formatDate(end);
            }
            return '';
        },
        
        init() {
            this.calculateDays();
        }
    }
}
</script>

<?php require __DIR__.'/../includes/footer.php'; ?>