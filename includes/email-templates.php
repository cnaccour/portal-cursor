<?php
// Shared email templates for the portal

if (!function_exists('buildShiftReportEmailHTML')) {
    function buildShiftReportEmailHTML(array $data): string {
        $checklistHTML = '';
        if (!empty($data['checklist']) && is_array($data['checklist'])) {
            foreach ($data['checklist'] as $item) {
                if (is_array($item) && isset($item['label'])) {
                    $done = !empty($item['done']);
                    $status = $done ? '✓' : '✗';
                    $statusClass = $done ? 'done' : 'pending';
                    $checklistHTML .= '<div class="checklist-item ' . $statusClass . '">' . $status . ' ' . htmlspecialchars($item['label']) . '</div>';
                }
            }
        }

        $refundsHTML = '';
        if (!empty($data['refunds']) && is_array($data['refunds'])) {
            foreach ($data['refunds'] as $refund) {
                $amount = isset($refund['amount']) ? (float)$refund['amount'] : 0;
                $reason = htmlspecialchars($refund['reason'] ?? '');
                $customer = htmlspecialchars($refund['customer'] ?? '');
                $service = htmlspecialchars($refund['service'] ?? '');
                $notes = htmlspecialchars($refund['notes'] ?? '');
                $refundsHTML .= '<div class="refund-item">
                    <div class="refund-header">$' . number_format($amount, 2) . ' - ' . $reason . '</div>
                    <div class="refund-details">Customer: ' . $customer . ' | Service: ' . $service . '</div>';
                if (!empty($notes)) {
                    $refundsHTML .= '<div class="refund-notes">Notes: ' . $notes . '</div>';
                }
                $refundsHTML .= '</div>';
            }
        }

        $location = htmlspecialchars($data['location'] ?? '');
        $shiftDate = htmlspecialchars($data['shift_date'] ?? '');
        $userName = htmlspecialchars($data['user_name'] ?? '');
        $shiftType = htmlspecialchars($data['shift_type'] ?? '');
        $checklistCompleted = (int)($data['checklist_completed'] ?? 0);
        $checklistTotal = (int)($data['checklist_total'] ?? 0);
        $reviewsCount = (int)($data['reviews_count'] ?? 0);
        $refundsCount = (int)($data['refunds_count'] ?? 0);
        $refundsAmount = (float)($data['refunds_amount'] ?? 0);
        $shipVendor = htmlspecialchars($data['shipments']['vendor'] ?? '');
        $shipNotes = htmlspecialchars($data['shipments']['notes'] ?? '');
        $notes = htmlspecialchars($data['notes'] ?? 'No additional notes');

        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shift Report - ' . $location . '</title>
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
                @media (max-width: 600px) { .info-grid { grid-template-columns: 1fr; } .stats-row { flex-direction: column; } }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Shift Report Submitted</h1>
                    <p>' . $location . ' - ' . $shiftDate . '</p>
                </div>

                <div class="content">
                    <div class="section">
                        <div class="section-title">Shift Information</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Employee</div>
                                <div class="info-value">' . $userName . '</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Shift Type</div>
                                <div class="info-value">' . $shiftType . '</div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-title">Summary</div>
                        <div class="stats-row">
                            <div class="stat-item">
                                <div class="stat-number">' . $checklistCompleted . '/' . $checklistTotal . '</div>
                                <div class="stat-label">Tasks Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . $reviewsCount . '</div>
                                <div class="stat-label">Customer Reviews</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . $refundsCount . '</div>
                                <div class="stat-label">Refunds</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">$' . number_format($refundsAmount, 2) . '</div>
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

                    ' . (!empty($shipVendor) || !empty($shipNotes) ? '
                    <div class="section">
                        <div class="section-title">Shipments & Deliveries</div>
                        <div class="shipment-item">
                            ' . (!empty($shipVendor) ? '<div class="shipment-label">Vendor: <span class="shipment-content">' . $shipVendor . '</span></div>' : '') . '
                            ' . (!empty($shipNotes) ? '<div class="shipment-label">Notes: <span class="shipment-content">' . $shipNotes . '</span></div>' : '') . '
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
                            <div class="notes-content">' . $notes . '</div>
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
}

<?php
/**
 * Email Template System
 * Handles HTML email templates for invitations and notifications
 */

class EmailTemplates {
    
    /**
     * Generate invitation email HTML
     */
    public static function getInvitationEmailHTML($invitation_data) {
        $email = $invitation_data['email'];
        $role = $invitation_data['role'];
        $token = $invitation_data['token'];
        $invited_by_name = $invitation_data['invited_by_name'] ?? 'Administrator';
        $expires_at = $invitation_data['expires_at'];
        
        // Generate secure signup URL
        require_once __DIR__ . '/config.php';
        $signup_url = getSignupUrl($token);
        
        // Format expiration date
        $expires_formatted = date('F j, Y \a\t g:i A', strtotime($expires_at));
        
        // Get role display name
        $role_display = self::getRoleDisplayName($role);
        
        return self::getEmailTemplate([
            'title' => 'You\'ve been invited to join J. Joseph Salon Team Portal',
            'preheader' => "Complete your registration to access the team portal as {$role_display}",
            'content' => self::getInvitationContent($email, $role_display, $invited_by_name, $signup_url, $expires_formatted)
        ]);
    }
    
    /**
     * Get the main email template wrapper
     */
    private static function getEmailTemplate($data) {
        $title = htmlspecialchars($data['title']);
        $preheader = htmlspecialchars($data['preheader']);
        $content = $data['content'];
        
        return "
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$title}</title>
    <style>
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            outline: none;
            text-decoration: none;
        }
        
        /* Email styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .content h2 {
            color: #2d3748;
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }
        
        .content p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        
        .info-box {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .info-box p {
            margin: 0;
            color: #2d3748;
            font-size: 14px;
        }
        
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .header, .content, .footer {
                padding: 25px 20px !important;
            }
            
            .header h1 {
                font-size: 24px !important;
            }
            
            .content h2 {
                font-size: 20px !important;
            }
            
            .cta-button {
                width: 100% !important;
                padding: 18px !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <!-- Preheader text -->
    <div style=\"display: none; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #ffffff;\">
        {$preheader}
    </div>
    
    <div class=\"email-container\">
        <div class=\"header\">
            <h1>J. Joseph Salon</h1>
        </div>
        
        <div class=\"content\">
            {$content}
        </div>
        
        <div class=\"footer\">
            <p>This invitation was sent by J. Joseph Salon Team Portal</p>
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
            <p>This invitation will expire and cannot be used after the expiration date.</p>
        </div>
    </div>
</body>
</html>";
    }
    
    /**
     * Get invitation email content
     */
    private static function getInvitationContent($email, $role_display, $invited_by_name, $signup_url, $expires_formatted) {
        return "
            <h2>Welcome to the Team! 🎉</h2>
            
            <p>Hi there!</p>
            
            <p><strong>{$invited_by_name}</strong> has invited you to join the <strong>J. Joseph Salon Team Portal</strong> as a <strong>{$role_display}</strong>.</p>
            
            <p>Our team portal helps you stay connected with the latest announcements, manage your shifts, submit reports, and collaborate with your colleagues.</p>
            
            <div style=\"text-align: center; margin: 30px 0;\">
                <a href=\"{$signup_url}\" class=\"cta-button\">Complete Your Registration</a>
            </div>
            
            <div class=\"info-box\">
                <p><strong>Your Role:</strong> {$role_display}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Expires:</strong> {$expires_formatted}</p>
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ol style=\"color: #4a5568; line-height: 1.6;\">
                <li>Click the button above to access the registration page</li>
                <li>Set up your password and complete your profile</li>
                <li>Start using the team portal immediately</li>
            </ol>
            
            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style=\"word-break: break-all; color: #667eea; font-size: 14px;\">{$signup_url}</p>
            
            <p>We're excited to have you on the team!</p>
            
            <p>Best regards,<br>
            <strong>The J. Joseph Salon Team</strong></p>
        ";
    }
    
    /**
     * Get text version of invitation email
     */
    public static function getInvitationEmailText($invitation_data) {
        $email = $invitation_data['email'];
        $role = $invitation_data['role'];
        $token = $invitation_data['token'];
        $invited_by_name = $invitation_data['invited_by_name'] ?? 'Administrator';
        $expires_at = $invitation_data['expires_at'];
        
        require_once __DIR__ . '/config.php';
        $signup_url = getSignupUrl($token);
        $expires_formatted = date('F j, Y \a\t g:i A', strtotime($expires_at));
        $role_display = self::getRoleDisplayName($role);
        
        return "J. Joseph Salon Team Portal - Invitation

Welcome to the Team!

Hi there!

{$invited_by_name} has invited you to join the J. Joseph Salon Team Portal as a {$role_display}.

Our team portal helps you stay connected with the latest announcements, manage your shifts, submit reports, and collaborate with your colleagues.

COMPLETE YOUR REGISTRATION
{$signup_url}

Your Details:
- Role: {$role_display}
- Email: {$email}
- Expires: {$expires_formatted}

What happens next?
1. Visit the registration link above
2. Set up your password and complete your profile
3. Start using the team portal immediately

We're excited to have you on the team!

Best regards,
The J. Joseph Salon Team

---
This invitation was sent by J. Joseph Salon Team Portal
If you didn't expect this invitation, you can safely ignore this email.
This invitation will expire and cannot be used after the expiration date.";
    }
    
    /**
     * Get role display name
     */
    private static function getRoleDisplayName($role) {
        $role_names = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'support' => 'Support Specialist',
            'staff' => 'Staff Member',
            'viewer' => 'Viewer'
        ];
        
        return $role_names[$role] ?? ucfirst($role);
    }
    
    /**
     * Send invitation email
     */
    public static function sendInvitationEmail($invitation_data) {
        try {
            $html_content = self::getInvitationEmailHTML($invitation_data);
            $text_content = self::getInvitationEmailText($invitation_data);
            
            // Log email sending (development only, no sensitive data)
            $dev_mode = getenv('DEV_MODE') === 'true' || file_exists(__DIR__ . '/../.dev_mode');
            if ($dev_mode) {
                error_log("=== INVITATION EMAIL SENT ===");
                error_log("To: {$invitation_data['email']}");
                error_log("Role: {$invitation_data['role']}");
                error_log("Expires: {$invitation_data['expires_at']}");
                error_log("Token: " . substr($invitation_data['token'], 0, 8) . "...[REDACTED]");
                error_log("==========================");
            }
            
            // In production, you would replace this with actual email sending:
            // Option 1: PHP's built-in mail() function
            // Option 2: PHPMailer library  
            // Option 3: Email service (SendGrid, Mailgun, etc.)
            // Option 4: Your hosting provider's SMTP
            
            /*
            // Production email sending example:
            $to = $invitation_data['email'];
            $subject = "You've been invited to join J. Joseph Salon Team Portal";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: noreply@{$_SERVER['HTTP_HOST']}" . "\r\n";
            
            return mail($to, $subject, $html_content, $headers);
            */
            
            return true; // Mock success for development
            
        } catch (Exception $e) {
            error_log("Error sending invitation email: " . $e->getMessage());
            return false;
        }
    }
}