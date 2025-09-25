<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin');

// Get form configurations
$forms_config = [];
$selected_form = $_GET['form'] ?? 'time_off_request';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25; // Reasonable limit for performance
$offset = ($page - 1) * $limit;

try {
    require_once __DIR__.'/includes/db.php';
    
    // Get all form configurations
    $stmt = $pdo->query("SELECT * FROM forms_config ORDER BY title ASC");
    $forms_config = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get submissions for selected form
    $submissions = [];
    $total_submissions = 0;
    
    if ($selected_form === 'time_off_request') {
        // Get total count
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM time_off_requests");
        $total_submissions = $count_stmt->fetchColumn();
        
        // Get paginated submissions
        $stmt = $pdo->prepare("
            SELECT * FROM time_off_requests 
            ORDER BY submitted_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($selected_form === 'bi_weekly_report') {
        // Get total count
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM bi_weekly_reports");
        $total_submissions = $count_stmt->fetchColumn();
        
        // Get paginated submissions
        $stmt = $pdo->prepare("\n            SELECT * FROM bi_weekly_reports\n            ORDER BY submitted_at DESC\n            LIMIT ? OFFSET ?\n        ");
        $stmt->execute([$limit, $offset]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $total_pages = ceil($total_submissions / $limit);
    
} catch (Exception $e) {
    error_log('Form submissions page error: ' . $e->getMessage());
    $error = 'Error loading form submissions.';
}

// Handle email sharing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'share_submission') {
    try {
        $submission_id = $_POST['submission_id'] ?? '';
        $email = trim($_POST['email'] ?? '');
        
        if (empty($submission_id) || empty($email)) {
            throw new Exception('Submission ID and email are required');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
        // Get submission details
        $stmt = $pdo->prepare("SELECT * FROM time_off_requests WHERE id = ?");
        $stmt->execute([$submission_id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$submission) {
            throw new Exception('Submission not found');
        }
        
        // Send email
        $subject = "Time Off Request - {$submission['first_name']} {$submission['last_name']}";
        $message = "
Time Off Request Details

Employee: {$submission['first_name']} {$submission['last_name']}
Email: {$submission['email']}
Work Location: {$submission['work_location']}
Date Range: {$submission['date_range']}
Reason: {$submission['reason']}
Status: {$submission['status']}
Submitted: {$submission['submitted_at']}

Additional Information:
{$submission['additional_info']}

Compensation Days Available: " . ($submission['has_compensation'] ? 'Yes' : 'No') . "
Blackout Policy Acknowledged: " . ($submission['understands_blackout'] ? 'Yes' : 'No') . "

---
Shared from JJS Team Portal
";

        $headers = "From: noreply@jjosephsalon.com\r\n";
        $headers .= "Reply-To: noreply@jjosephsalon.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Detect development (MAMP/portaltest) and simulate email with on-page preview
        $is_dev = (
            (isset($_SERVER['HTTP_HOST']) && (
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'portaltest') !== false
            )) || file_exists('/Applications/MAMP/tmp/mysql/mysql.sock')
        );

        if ($is_dev) {
            // Development environment - preview instead of sending
            $email_preview = [
                'to' => $email,
                'subject' => $subject,
                'headers' => $headers,
                'body' => $message,
            ];
            $success = "Email shared successfully! (Development preview shown below)";
        } else {
            // Production environment - actually send email
            $mail_sent = mail($email, $subject, $message, $headers);
            if ($mail_sent) {
                $success = "Email shared successfully!";
            } else {
                throw new Exception('Failed to send email');
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require __DIR__.'/includes/header.php';
?>

<style>
.submission-card {
    transition: all 0.2s ease;
}
.submission-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.status-pending { background-color: #FEF3C7; color: #92400E; }
.status-approved { background-color: #D1FAE5; color: #065F46; }
.status-denied { background-color: #FEE2E2; color: #991B1B; }
</style>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Form Submissions</h1>
            <p class="text-gray-600 mt-2">Review and manage all form submissions with advanced filtering and export options.</p>
        </div>
        <div class="flex gap-3">
            <a href="admin-forms.php" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Forms
            </a>
        </div>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
        <?php if (!empty($email_preview) && is_array($email_preview)): ?>
        <div class="mt-3 bg-white border rounded p-3">
            <div class="text-sm text-gray-600 mb-2"><strong>Development Email Preview</strong></div>
            <div class="text-xs text-gray-600"><strong>To:</strong> <?= htmlspecialchars($email_preview['to']) ?></div>
            <div class="text-xs text-gray-600"><strong>Subject:</strong> <?= htmlspecialchars($email_preview['subject']) ?></div>
            <pre class="mt-2 text-xs whitespace-pre-wrap text-gray-800"><?= htmlspecialchars($email_preview['body']) ?></pre>
        </div>
        <?php endif; ?>
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

<!-- Form Selector and Stats -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Form:</label>
            <select onchange="window.location.href='?form=' + this.value" 
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-yellow-600">
                <?php foreach ($forms_config as $form): ?>
                    <option value="<?= htmlspecialchars($form['form_key']) ?>" 
                            <?= $selected_form === $form['form_key'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($form['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex items-center gap-6 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span><strong><?= $total_submissions ?></strong> total submissions</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <a href="api/forms/export-pdf.php?form_key=<?= urlencode($selected_form) ?>" 
                   target="_blank"
                   class="font-medium"
                   style="color: #AF831A;"
                   onmouseover="this.style.color='#8B6914'" 
                   onmouseout="this.style.color='#AF831A'">Export as PDF</a>
            </div>
        </div>
    </div>
</div>

<!-- Submissions List -->
<div class="space-y-4">
    <?php if (empty($submissions)): ?>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Submissions Found</h3>
            <p class="text-gray-600">No submissions have been received for this form yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($submissions as $submission): ?>
            <div class="submission-card bg-white rounded-xl border border-gray-200 shadow-sm p-6" 
                 x-data="{ showDetails: false, showShareForm: false }">
                
                <!-- Submission Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?>
                            </h3>
                            <span class="status-<?= $submission['status'] ?> px-2 py-1 text-xs font-medium rounded-full">
                                <?= ucfirst($submission['status']) ?>
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <div><strong>Email:</strong> <?= htmlspecialchars($submission['email']) ?></div>
                            <div><strong>Location:</strong> <?= htmlspecialchars($submission['work_location']) ?></div>
                            <div><strong>Date Range:</strong> <?= htmlspecialchars($submission['date_range']) ?></div>
                            <div><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 ml-4">
                        <button @click="showDetails = !showDetails" 
                                class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                            <span x-text="showDetails ? 'Hide Details' : 'Show Details'"></span>
                        </button>
                        <button @click="showShareForm = !showShareForm" 
                                class="px-3 py-1 text-sm rounded transition-colors"
                                style="background-color:#AF831A; color:white;"
                                onmouseover="this.style.backgroundColor='#8B6914'" 
                                onmouseout="this.style.backgroundColor='#AF831A'">
                            Share
                        </button>
                    </div>
                </div>
                
                <!-- Quick Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div><strong>Reason:</strong> <?= htmlspecialchars($submission['reason']) ?></div>
                        <div><strong>Compensation:</strong> <?= $submission['has_compensation'] ? 'Yes' : 'No' ?></div>
                        <div><strong>Policy Acknowledged:</strong> <?= $submission['understands_blackout'] ? 'Yes' : 'No' ?></div>
                    </div>
                </div>
                
                <!-- Detailed View -->
                <div x-show="showDetails" x-transition class="border-t pt-4">
                    <?php if (!empty($submission['additional_info'])): ?>
                        <div class="bg-white border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Additional Information:</h4>
                            <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($submission['additional_info']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Share Form -->
                <div x-show="showShareForm" x-transition class="border-t pt-4">
                    <form method="post" class="flex gap-3 items-end" onsubmit="return (function(f){
                        var id = f.querySelector('input[name=\'submission_id\']').value;
                        var url = 'api/forms/export-pdf.php?form_key=<?= urlencode($selected_form) ?>&submission_id='+encodeURIComponent(id);
                        window.open(url, '_blank');
                        return true; // continue to send email
                    })(this)">
                        <input type="hidden" name="action" value="share_submission">
                        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" required 
                                   placeholder="Enter email to share this submission"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-yellow-600">
                        </div>
                        <button type="submit" 
                                class="px-4 py-2 rounded-lg transition-colors"
                                style="background-color:#AF831A; color:white;"
                                onmouseover="this.style.backgroundColor='#8B6914'" 
                                onmouseout="this.style.backgroundColor='#AF831A'">
                            Send
                        </button>
                        <button type="button" @click="showShareForm = false"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
        <nav class="flex items-center gap-2">
            <?php if ($page > 1): ?>
                <a href="?form=<?= urlencode($selected_form) ?>&page=<?= $page - 1 ?>" 
                   class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?form=<?= urlencode($selected_form) ?>&page=<?= $i ?>" 
                   class="px-3 py-2 border border-gray-300 rounded-lg <?= $i === $page ? 'bg-yellow-600 text-white border-yellow-600' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?form=<?= urlencode($selected_form) ?>&page=<?= $page + 1 ?>" 
                   class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </nav>
    </div>
<?php endif; ?>

<?php require __DIR__.'/includes/footer.php'; ?>