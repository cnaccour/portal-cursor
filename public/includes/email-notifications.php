<?php
/**
 * Email Notifications Helper
 * Handles automatic email notifications for form submissions
 */

class EmailNotifications {
    
    /**
     * Send notification email for new time off request
     */
    public static function sendTimeOffRequestNotification($submission_data, $notification_emails) {
        if (empty($notification_emails)) {
            return false;
        }
        
        $subject = "New Time Off Request - {$submission_data['first_name']} {$submission_data['last_name']}";
        
        $message = "
A new time off request has been submitted:

Employee: {$submission_data['first_name']} {$submission_data['last_name']}
Email: {$submission_data['email']}
Work Location: {$submission_data['work_location']}
Date Range: {$submission_data['date_range']}
Reason: {$submission_data['reason']}
Submitted: " . date('Y-m-d H:i:s') . "

Additional Information:
{$submission_data['additional_info']}

Compensation Days Available: " . ($submission_data['has_compensation'] ? 'Yes' : 'No') . "
Blackout Policy Acknowledged: " . ($submission_data['understands_blackout'] ? 'Yes' : 'No') . "

Please review this request in the admin portal:
" . self::getBaseUrl() . "/admin-forms.php

---
JJS Team Portal - Automated Notification
";

        // Use SMTP helper
        if (file_exists(__DIR__ . '/../lib/Email.php')) {
            require_once __DIR__ . '/../lib/Email.php';
        } elseif (file_exists(__DIR__ . '/../../public/lib/Email.php')) {
            require_once __DIR__ . '/../../public/lib/Email.php';
        }
        
        $success = true;
        foreach ($notification_emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { continue; }
            $result = function_exists('send_smtp_email')
                ? send_smtp_email($email, $subject, nl2br(htmlspecialchars($message)), $message)
                : false;
            if (!$result || (is_array($result) && empty($result['success']))) {
                error_log("Failed to send notification email to: $email");
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Get base URL for links in emails
     */
    private static function getBaseUrl() {
        require_once __DIR__ . '/config.php';
        return rtrim(getPortalUrl(''), '/');
    }
}