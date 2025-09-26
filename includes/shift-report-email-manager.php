<?php
/**
 * Shift Report Email Manager
 * Handles conditional email notifications based on location
 */

class ShiftReportEmailManager {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send shift report email notifications based on location
     */
    public function sendShiftReportNotifications($shiftData) {
        try {
            // Get email settings for the location
            $settings = $this->getEmailSettingsForLocation($shiftData['location']);
            
            if (empty($settings) || !$settings['is_active']) {
                error_log("No active email settings found for location: " . $shiftData['location']);
                return false;
            }
            
            $email_addresses = json_decode($settings['email_addresses'], true);
            if (empty($email_addresses)) {
                error_log("No email addresses configured for location: " . $shiftData['location']);
                return false;
            }
            
            // Generate email content
            $subject = "Shift Report - " . $shiftData['location'] . " (" . $shiftData['shift_date'] . ")";
            $html_body = $this->generateShiftReportEmailHTML($shiftData);
            
            // Send emails
            require_once __DIR__ . '/../lib/Email.php';
            $email = new Email();
            
            $success_count = 0;
            $total_count = count($email_addresses);
            
            foreach ($email_addresses as $email_address) {
                $result = $email->send_smtp_email($email_address, $subject, $html_body);
                if ($result) {
                    $success_count++;
                    error_log("Shift report email sent successfully to: $email_address");
                } else {
                    error_log("Failed to send shift report email to: $email_address");
                }
            }
            
            error_log("Shift report email notifications: $success_count/$total_count sent successfully");
            return $success_count > 0;
            
        } catch (Exception $e) {
            error_log("Error sending shift report notifications: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email settings for a specific location
     */
    private function getEmailSettingsForLocation($location) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM shift_report_email_settings WHERE location = ? AND is_active = 1");
            $stmt->execute([$location]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting email settings for location $location: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate HTML email template for shift report
     */
    private function generateShiftReportEmailHTML($data) {
        // Calculate stats
        $checklist_completed = 0;
        $checklist_total = 0;
        $refunds_count = 0;
        $refunds_amount = 0;
        
        if (!empty($data['checklist']) && is_array($data['checklist'])) {
            $checklist_total = count($data['checklist']);
            foreach ($data['checklist'] as $item) {
                if (is_array($item) && isset($item['done']) && $item['done']) {
                    $checklist_completed++;
                } elseif (is_string($item)) {
                    $checklist_completed++;
                }
            }
        }
        
        if (!empty($data['refunds']) && is_array($data['refunds'])) {
            $refunds_count = count($data['refunds']);
            foreach ($data['refunds'] as $refund) {
                if (is_array($refund) && isset($refund['amount'])) {
                    $refunds_amount += floatval($refund['amount']);
                }
            }
        }
        
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shift Report - ' . htmlspecialchars($data['location']) . '</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
                .header { background: linear-gradient(135deg, #AF831A 0%, #8B6914 100%); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
                .header p { margin: 8px 0 0 0; opacity: 0.9; font-size: 16px; }
                .content { padding: 30px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
                .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #AF831A; }
                .info-label { font-size: 12px; text-transform: uppercase; font-weight: 600; color: #6b7280; margin-bottom: 4px; }
                .info-value { font-size: 16px; font-weight: 500; color: #111827; }
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-bottom: 25px; }
                .stat-item { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; }
                .stat-number { font-size: 24px; font-weight: 700; color: #AF831A; margin-bottom: 4px; }
                .stat-label { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 500; }
                .notes-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
                .notes-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; }
                .notes-content { color: #6b7280; line-height: 1.5; }
                .checklist-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
                .checklist-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; }
                .checklist-item { display: flex; align-items: center; margin-bottom: 8px; }
                .checklist-icon { margin-right: 8px; font-size: 16px; }
                .refunds-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; }
                .refunds-label { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; }
                .refund-item { background: white; padding: 12px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #AF831A; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; }
                .footer p { margin: 0; font-size: 14px; color: #6b7280; }
                .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
                .badge-success { background: #d1fae5; color: #065f46; }
                .badge-warning { background: #fef3c7; color: #92400e; }
                @media (max-width: 600px) {
                    .info-grid { grid-template-columns: 1fr; }
                    .stats-grid { grid-template-columns: repeat(2, 1fr); }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📋 Shift Report Submitted</h1>
                    <p>' . htmlspecialchars($data['location']) . ' • ' . htmlspecialchars($data['shift_date']) . '</p>
                </div>
                
                <div class="content">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Employee</div>
                            <div class="info-value">' . htmlspecialchars($data['user_name'] ?? 'Unknown User') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Shift Type</div>
                            <div class="info-value">' . htmlspecialchars($data['shift_type'] ?? 'N/A') . '</div>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">' . $checklist_completed . '/' . $checklist_total . '</div>
                            <div class="stat-label">Tasks Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">' . $refunds_count . '</div>
                            <div class="stat-label">Refunds</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">$' . number_format($refunds_amount, 2) . '</div>
                            <div class="stat-label">Refund Amount</div>
                        </div>
                    </div>';
        
        // Add checklist section if available
        if (!empty($data['checklist']) && is_array($data['checklist'])) {
            $html .= '
                    <div class="checklist-section">
                        <div class="checklist-label">✅ Task Checklist</div>';
            
            foreach ($data['checklist'] as $item) {
                $label = '';
                $completed = false;
                
                if (is_array($item) && isset($item['label'])) {
                    $label = $item['label'];
                    $completed = $item['done'] ?? false;
                } elseif (is_string($item)) {
                    $label = $item;
                    $completed = true; // Assume completed if it's in the list
                }
                
                $icon = $completed ? '✅' : '❌';
                $html .= '
                        <div class="checklist-item">
                            <span class="checklist-icon">' . $icon . '</span>
                            <span>' . htmlspecialchars($label) . '</span>
                        </div>';
            }
            
            $html .= '
                    </div>';
        }
        
        // Add refunds section if available
        if (!empty($data['refunds']) && is_array($data['refunds'])) {
            $html .= '
                    <div class="refunds-section">
                        <div class="refunds-label">💰 Refunds & Returns</div>';
            
            foreach ($data['refunds'] as $refund) {
                if (is_array($refund) && (!empty($refund['amount']) || !empty($refund['reason']))) {
                    $html .= '
                        <div class="refund-item">
                            <div><strong>Amount:</strong> $' . number_format(floatval($refund['amount'] ?? 0), 2) . '</div>
                            <div><strong>Reason:</strong> ' . htmlspecialchars($refund['reason'] ?? 'N/A') . '</div>';
                    
                    if (!empty($refund['customer'])) {
                        $html .= '<div><strong>Customer:</strong> ' . htmlspecialchars($refund['customer']) . '</div>';
                    }
                    if (!empty($refund['service'])) {
                        $html .= '<div><strong>Service:</strong> ' . htmlspecialchars($refund['service']) . '</div>';
                    }
                    if (!empty($refund['notes'])) {
                        $html .= '<div><strong>Notes:</strong> ' . htmlspecialchars($refund['notes']) . '</div>';
                    }
                    
                    $html .= '
                        </div>';
                }
            }
            
            $html .= '
                    </div>';
        }
        
        // Add notes section
        if (!empty($data['notes'])) {
            $html .= '
                    <div class="notes-section">
                        <div class="notes-label">📝 Shift Notes</div>
                        <div class="notes-content">' . htmlspecialchars($data['notes']) . '</div>
                    </div>';
        }
        
        $html .= '
                </div>
                
                <div class="footer">
                    <p>This is an automated notification from the J. Joseph Salon Portal</p>
                    <p><a href="https://portal.jjosephsalon.com/portal/reports.php" style="color: #AF831A; text-decoration: none;">View All Reports →</a></p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Get all email settings
     */
    public function getAllEmailSettings() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM shift_report_email_settings ORDER BY location");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting all email settings: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if email notifications are enabled for a location
     */
    public function isEmailEnabledForLocation($location) {
        try {
            $stmt = $this->pdo->prepare("SELECT is_active FROM shift_report_email_settings WHERE location = ?");
            $stmt->execute([$location]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['is_active'] : false;
        } catch (Exception $e) {
            error_log("Error checking email status for location $location: " . $e->getMessage());
            return false;
        }
    }
}
?>
