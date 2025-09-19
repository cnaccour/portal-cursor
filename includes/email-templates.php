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
        
        // Generate signup URL (you'll need to set your domain)
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
        $signup_url = "http://{$domain}/signup.php?token=" . urlencode($token);
        
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
            <p>This invitation was sent by {$_SERVER['HTTP_HOST'] ?? 'J. Joseph Salon Team Portal'}</p>
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
        
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
        $signup_url = "http://{$domain}/signup.php?token=" . urlencode($token);
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
This invitation was sent by " . ($_SERVER['HTTP_HOST'] ?? 'J. Joseph Salon Team Portal') . "
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
     * Send email (mock implementation for development)
     */
    public static function sendInvitationEmail($invitation_data) {
        $html_content = self::getInvitationEmailHTML($invitation_data);
        $text_content = self::getInvitationEmailText($invitation_data);
        
        // For development, just log the email content
        error_log("Mock Email Sent to {$invitation_data['email']}:");
        error_log("Subject: You've been invited to join J. Joseph Salon Team Portal");
        error_log("Signup URL: http://{$_SERVER['HTTP_HOST']}/signup.php?token=" . urlencode($invitation_data['token']));
        
        // In production, you would use mail() function or a service like:
        // - PHPMailer
        // - SendGrid
        // - Amazon SES
        // - Your hosting provider's mail service
        
        /*
        // Production email sending example:
        $to = $invitation_data['email'];
        $subject = "You've been invited to join J. Joseph Salon Team Portal";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@{$_SERVER['HTTP_HOST']}" . "\r\n";
        
        return mail($to, $subject, $html_content, $headers);
        */
        
        return true; // Mock success
    }
}