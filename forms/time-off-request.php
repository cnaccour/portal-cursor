<?php
require __DIR__.'/../includes/auth.php';
// Forms are accessible to all users - no login required

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__.'/../includes/db.php';
        
        // Collect form data
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $work_location = $_POST['work_location'] ?? '';
        $date_range = $_POST['date_range'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $additional_info = trim($_POST['additional_info'] ?? '');
        $has_compensation = isset($_POST['has_compensation']) ? 1 : 0;
        $understands_blackout = isset($_POST['understands_blackout']) ? 1 : 0;
        $submitted_by = $_SESSION['user_id'] ?? null;
        
        // Parse date range (format: "2024-01-01 to 2024-01-05")
        $start_date = '';
        $end_date = '';
        if ($date_range && strpos($date_range, ' to ') !== false) {
            list($start_date, $end_date) = explode(' to ', $date_range);
        }
        
        // Basic validation
        $errors = [];
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($work_location)) $errors[] = 'Work location is required';
        if (empty($date_range)) $errors[] = 'Date range is required';
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
                    date_range VARCHAR(255),
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
                (first_name, last_name, email, work_location, start_date, end_date, date_range, reason, additional_info, 
                 has_compensation, understands_blackout, submitted_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $first_name, $last_name, $email, $work_location, $start_date, $end_date, $date_range,
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
/* Gold theme focus styling */
.form-field {
    transition: all 0.2s ease;
}

.form-field:focus {
    outline: none;
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}

.form-field:focus-within {
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}

/* Custom select arrow */
.custom-select {
    background-image: url("data:image/svg+xml;charset=UTF-8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6,9 12,15 18,9'></polyline></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    appearance: none;
}

/* Radio button styling */
.radio-option {
    position: relative;
    cursor: pointer;
}

.radio-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.radio-option .radio-design {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: 2px solid #E5E7EB;
    border-radius: 8px;
    background: #F9FAFB;
    transition: all 0.2s ease;
}

.radio-option input[type="radio"]:checked + .radio-design {
    border-color: #AF831A;
    background: #FFFBF0;
    color: #92400E;
}

.radio-option input[type="radio"]:focus + .radio-design {
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}

.radio-option .radio-circle {
    width: 16px;
    height: 16px;
    border: 2px solid #D1D5DB;
    border-radius: 50%;
    margin-right: 12px;
    position: relative;
    transition: all 0.2s ease;
}

.radio-option input[type="radio"]:checked + .radio-design .radio-circle {
    border-color: #AF831A;
}

.radio-option input[type="radio"]:checked + .radio-design .radio-circle::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #AF831A;
}

/* Checkbox styling */
.checkbox-custom {
    width: 18px;
    height: 18px;
    accent-color: #AF831A;
}

.checkbox-custom:focus {
    outline: 2px solid #AF831A;
    outline-offset: 2px;
}

/* Date range display */
.date-summary {
    background: linear-gradient(135deg, #AF831A 0%, #D4AF37 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
    margin-top: 12px;
    box-shadow: 0 2px 4px rgba(175, 131, 26, 0.2);
}
</style>

<h1 class="text-2xl font-semibold mb-6">Submit Time Off Request</h1>

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
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="font-medium text-red-800 mb-2">Please correct the following errors:</div>
        <ul class="text-red-700 text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
                <li>• <?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="space-y-6" x-data="timeOffForm()">

    <!-- Personal Information -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Personal Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">First Name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" required 
                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                       placeholder="Enter first name"
                       class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Last Name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" required 
                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                       placeholder="Enter last name"
                       class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="Enter email address"
                       class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
        </div>
    </section>

    <!-- Work Location -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Work Location</h2>
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
                <label class="radio-option">
                    <input type="radio" name="work_location" value="<?= $value ?>" <?= $checked ?> required>
                    <div class="radio-design">
                        <div class="radio-circle"></div>
                        <span class="text-sm font-medium"><?= htmlspecialchars($label) ?></span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Date Range -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Time Off Period</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
                <input type="date" x-model="startDate" @change="updateDateRange" 
                       min="<?= date('Y-m-d') ?>"
                       class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">End Date <span class="text-red-500">*</span></label>
                <input type="date" x-model="endDate" @change="updateDateRange" 
                       :min="startDate || '<?= date('Y-m-d') ?>'"
                       class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
        </div>
        <input type="hidden" name="date_range" x-model="dateRange" required>
        <div x-show="dayCount > 0" x-transition class="date-summary">
            <span x-text="dayCount === 1 ? '1 day selected' : dayCount + ' days selected'"></span>
            <span class="mx-3">•</span>
            <span x-text="formatDateRange()"></span>
        </div>
    </section>

    <!-- Reason for Time Off -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Reason for Time Off</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php 
            $reasons = [
                'vacation' => 'Vacation',
                'personal' => 'Personal Day',
                'sick' => 'Sick Leave',
                'family-emergency' => 'Family Emergency',
                'other' => 'Other'
            ];
            foreach ($reasons as $value => $label): 
                $checked = ($_POST['reason'] ?? '') === $value ? 'checked' : '';
            ?>
                <label class="radio-option">
                    <input type="radio" name="reason" value="<?= $value ?>" <?= $checked ?> required>
                    <div class="radio-design">
                        <div class="radio-circle"></div>
                        <span class="text-sm font-medium"><?= htmlspecialchars($label) ?></span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Additional Details -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Additional Details</h2>
        <div>
            <label class="block text-sm font-medium mb-2">Additional Information (Optional)</label>
            <textarea name="additional_info" rows="4" 
                      placeholder="Provide any additional details about your request..."
                      class="w-full border rounded-lg px-3 py-2 form-field resize-none"><?= htmlspecialchars($_POST['additional_info'] ?? '') ?></textarea>
        </div>
    </section>

    <!-- Acknowledgments -->
    <section class="bg-white p-6 rounded-xl border">
        <h2 class="text-lg font-semibold mb-4">Acknowledgments</h2>
        <div class="space-y-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="has_compensation" value="1" 
                       <?= isset($_POST['has_compensation']) ? 'checked' : '' ?>
                       class="checkbox-custom flex-shrink-0 mt-0.5">
                <span class="text-gray-700">I have compensation days available for this request</span>
            </label>
            
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="understands_blackout" value="1" required
                       <?= isset($_POST['understands_blackout']) ? 'checked' : '' ?>
                       class="checkbox-custom flex-shrink-0 mt-0.5">
                <span class="text-gray-700">I understand that this request may fall during blackout dates and agree to any applicable policies</span>
            </label>
        </div>
    </section>

    <!-- Submit Button -->
    <section class="bg-white p-6 rounded-xl border">
        <button type="submit" 
                class="w-full px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium">
            Submit Time Off Request
        </button>
    </section>

</form>

<script>
function timeOffForm() {
    return {
        startDate: '<?= $_POST['start_date'] ?? '' ?>',
        endDate: '<?= $_POST['end_date'] ?? '' ?>',
        dateRange: '<?= $_POST['date_range'] ?? '' ?>',
        dayCount: 0,
        
        updateDateRange() {
            if (this.startDate && this.endDate) {
                this.dateRange = this.startDate + ' to ' + this.endDate;
                this.calculateDays();
            } else {
                this.dateRange = '';
                this.dayCount = 0;
            }
        },
        
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