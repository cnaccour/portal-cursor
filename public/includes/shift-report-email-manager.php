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
            error_log("ShiftReportEmailManager: Processing shift report for location: " . $shiftData['location']);
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Processing shift report for location: " . $shiftData['location'] . "\n", FILE_APPEND);
            
            // Get email settings for the location
            $settings = $this->getEmailSettingsForLocation($shiftData['location']);
            error_log("ShiftReportEmailManager: Settings found: " . print_r($settings, true));
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Settings found: " . print_r($settings, true) . "\n", FILE_APPEND);
            
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
            error_log("ShiftReportEmailManager: Generated subject: $subject");
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Generated subject: $subject\n", FILE_APPEND);
            
            // Use simple email template directly - skip the complex one for now
            error_log("ShiftReportEmailManager: Using simple email template");
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Using simple email template\n", FILE_APPEND);
            
            error_log("ShiftReportEmailManager: About to generate email template");
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: About to generate email template\n", FILE_APPEND);
            
            try {
                $html_body = $this->generateSimpleEmailTemplate($shiftData);
                error_log("ShiftReportEmailManager: Generated email body length: " . strlen($html_body));
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Generated email body length: " . strlen($html_body) . "\n", FILE_APPEND);
            } catch (Exception $e) {
                error_log("ShiftReportEmailManager: Error generating email template: " . $e->getMessage());
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Error generating email template: " . $e->getMessage() . "\n", FILE_APPEND);
                
                // Use a very simple fallback template
                $html_body = '<html><body><h1>Shift Report - ' . htmlspecialchars($shiftData['location'] ?? 'Unknown') . '</h1><p>Employee: ' . htmlspecialchars($shiftData['user_name'] ?? 'Unknown') . '</p><p>Date: ' . htmlspecialchars($shiftData['shift_date'] ?? 'Unknown') . '</p><p>Notes: ' . htmlspecialchars($shiftData['notes'] ?? 'No notes') . '</p></body></html>';
                error_log("ShiftReportEmailManager: Using fallback template, length: " . strlen($html_body));
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Using fallback template, length: " . strlen($html_body) . "\n", FILE_APPEND);
            }
            
            // Send emails - use same pattern as working forgot-password.php
            if (file_exists(__DIR__ . '/../public/lib/Email.php')) {
                require_once __DIR__ . '/../public/lib/Email.php';
            } elseif (file_exists(__DIR__ . '/../lib/Email.php')) {
                require_once __DIR__ . '/../lib/Email.php';
            } else {
                error_log("ShiftReportEmailManager: Email library not found");
                return false;
            }
            
            $success_count = 0;
            $total_count = count($email_addresses);
            error_log("ShiftReportEmailManager: About to send emails to $total_count recipients");
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: About to send emails to $total_count recipients\n", FILE_APPEND);
            
            foreach ($email_addresses as $email_address) {
                error_log("ShiftReportEmailManager: Attempting to send email to: $email_address");
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Attempting to send email to: $email_address\n", FILE_APPEND);
                
                $result = send_smtp_email($email_address, $subject, $html_body);
                error_log("ShiftReportEmailManager: Email send result: " . print_r($result, true));
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Email send result: " . print_r($result, true) . "\n", FILE_APPEND);
                
                if ($result['success']) {
                    $success_count++;
                    error_log("Shift report email sent successfully to: $email_address");
                    file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " Shift report email sent successfully to: $email_address\n", FILE_APPEND);
                } else {
                    error_log("Failed to send shift report email to: $email_address - " . ($result['error'] ?? 'Unknown error'));
                    file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " Failed to send shift report email to: $email_address - " . ($result['error'] ?? 'Unknown error') . "\n", FILE_APPEND);
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
            error_log("ShiftReportEmailManager: Looking for email settings for location: '$location'");
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Looking for email settings for location: '$location'\n", FILE_APPEND);
            
            // First, let's see what locations are available
            $all_stmt = $this->pdo->prepare("SELECT location, is_active FROM shift_report_email_settings");
            $all_stmt->execute();
            $all_settings = $all_stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("ShiftReportEmailManager: All available settings: " . print_r($all_settings, true));
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: All available settings: " . print_r($all_settings, true) . "\n", FILE_APPEND);
            
            $stmt = $this->pdo->prepare("SELECT * FROM shift_report_email_settings WHERE location = ? AND is_active = 1");
            $stmt->execute([$location]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("ShiftReportEmailManager: Database query result: " . print_r($result, true));
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " ShiftReportEmailManager: Database query result: " . print_r($result, true) . "\n", FILE_APPEND);
            
            return $result;
        } catch (Exception $e) {
            error_log("Error getting email settings for location $location: " . $e->getMessage());
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " Error getting email settings for location $location: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }
    
    /**
     * Generate detailed email template matching the test email style
     */
    private function generateSimpleEmailTemplate($data) {
        $location = htmlspecialchars($data['location'] ?? 'Unknown Location');
        $user_name = htmlspecialchars($data['user_name'] ?? 'Unknown User');
        $shift_date = htmlspecialchars($data['shift_date'] ?? 'Unknown Date');
        $shift_type = htmlspecialchars($data['shift_type'] ?? 'Unknown Type');
        $notes = htmlspecialchars($data['notes'] ?? 'No additional notes');
        
        // Process checklist data
        $checklist = $data['checklist'] ?? [];
        if (is_string($checklist)) {
            $checklist = json_decode($checklist, true) ?? [];
        }
        
        // Process refunds data
        $refunds = $data['refunds'] ?? [];
        if (is_string($refunds)) {
            $refunds = json_decode($refunds, true) ?? [];
        }
        
        // Process shipments data - fix the duplication issue
        $shipments = $data['shipments'] ?? [];
        if (is_string($shipments)) {
            $shipments = json_decode($shipments, true) ?? [];
        }
        
        // Process reviews data
        $reviews = $data['reviews'] ?? [];
        if (is_string($reviews)) {
            $reviews = json_decode($reviews, true) ?? [];
        }
        
        // Calculate stats
        $checklist_completed = 0;
        $checklist_total = count($checklist);
        foreach ($checklist as $item) {
            if (isset($item['completed']) && $item['completed']) {
                $checklist_completed++;
            } elseif (isset($item['done']) && $item['done']) {
                $checklist_completed++;
            }
        }
        
        $refunds_count = count($refunds);
        $refunds_amount = 0;
        foreach ($refunds as $refund) {
            $refunds_amount += floatval($refund['amount'] ?? 0);
        }
        
        $shipments_count = count($shipments);
        $reviews_count = count($reviews);
        
        // Build checklist HTML
        $checklistHTML = '';
        if (!empty($checklist)) {
            foreach ($checklist as $item) {
                $done = $item['completed'] ?? $item['done'] ?? false;
                $label = $item['task'] ?? $item['label'] ?? 'Unknown Task';
                $status = $done ? '✓' : '✗';
                $statusClass = $done ? 'done' : 'pending';
                $checklistHTML .= '<div class="checklist-item ' . $statusClass . '">' . $status . ' ' . htmlspecialchars($label) . '</div>';
            }
        }
        
        // Build refunds HTML
        $refundsHTML = '';
        if (!empty($refunds)) {
            foreach ($refunds as $refund) {
                $refundsHTML .= '<div class="refund-item">
                    <div class="refund-header">$' . number_format(floatval($refund['amount'] ?? 0), 2) . ' - ' . htmlspecialchars($refund['reason'] ?? 'Unknown') . '</div>
                    <div class="refund-details">Customer: ' . htmlspecialchars($refund['customer'] ?? 'Unknown') . ' | Service: ' . htmlspecialchars($refund['service'] ?? 'Unknown') . '</div>';
                if (!empty($refund['notes'])) {
                    $refundsHTML .= '<div class="refund-notes">Notes: ' . htmlspecialchars($refund['notes']) . '</div>';
                }
                $refundsHTML .= '</div>';
            }
        }
        
        // Build shipments HTML - fix duplication
        $shipmentsHTML = '';
        if (!empty($shipments)) {
            foreach ($shipments as $shipment) {
                // Only show if there's actual data
                if (!empty($shipment['tracking']) || !empty($shipment['carrier']) || !empty($shipment['status'])) {
                    $shipmentsHTML .= '<div class="shipment-item">
                        <div class="shipment-label">Shipment</div>
                        <div class="shipment-content">Tracking: ' . htmlspecialchars($shipment['tracking'] ?? 'N/A') . 
                        ' | Carrier: ' . htmlspecialchars($shipment['carrier'] ?? 'N/A') . 
                        ' | Status: ' . htmlspecialchars($shipment['status'] ?? 'N/A') . '</div>
                    </div>';
                }
            }
        }
        
        // Build reviews HTML
        $reviewsHTML = '';
        if (!empty($reviews)) {
            foreach ($reviews as $review) {
                $reviewsHTML .= '<div class="review-item">
                    <div class="review-header">' . htmlspecialchars($review['platform'] ?? 'Unknown Platform') . ' - ' . htmlspecialchars($review['rating'] ?? 'Unknown Rating') . '</div>
                    <div class="review-content">' . htmlspecialchars($review['comment'] ?? 'No comment') . '</div>
                </div>';
            }
        }
        
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
                .shipment-item { padding: 12px; background: #e0f2fe; border-radius: 6px; border: 1px solid #0284c7; margin-bottom: 10px; }
                .shipment-label { font-weight: 600; color: #0c4a6e; margin-bottom: 4px; }
                .shipment-content { font-size: 14px; color: #0c4a6e; }
                .review-item { padding: 12px; background: #f0fdf4; border-radius: 6px; border: 1px solid #16a34a; margin-bottom: 10px; }
                .review-header { font-weight: 600; color: #14532d; margin-bottom: 4px; }
                .review-content { font-size: 14px; color: #14532d; }
                .notes-section { padding: 15px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
                .notes-content { color: #374151; line-height: 1.4; }
                .footer { padding: 15px 20px; text-align: center; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 8px 8px; }
                .footer p { margin: 0; font-size: 12px; color: #6b7280; }
                @media (max-width: 600px) {
                    .info-grid { grid-template-columns: 1fr; }
                    .stats-row { flex-direction: column; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Shift Report Submitted</h1>
                    <p>' . $location . ' - ' . $shift_date . '</p>
                </div>
                
                <div class="content">
                    <div class="section">
                        <div class="section-title">Shift Information</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Employee</div>
                                <div class="info-value">' . $user_name . '</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Shift Type</div>
                                <div class="info-value">' . $shift_type . '</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Summary</div>
                        <div class="stats-row">
                            <div class="stat-item">
                                <div class="stat-number">' . $checklist_completed . '/' . $checklist_total . '</div>
                                <div class="stat-label">Tasks Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . $refunds_count . '</div>
                                <div class="stat-label">Refunds</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . $shipments_count . '</div>
                                <div class="stat-label">Shipments</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">' . $reviews_count . '</div>
                                <div class="stat-label">Reviews</div>
                            </div>
                        </div>
                    </div>
                    
                    ' . ($checklistHTML ? '<div class="section">
                        <div class="section-title">Checklist</div>
                        ' . $checklistHTML . '
                    </div>' : '') . '
                    
                    ' . ($refundsHTML ? '<div class="section">
                        <div class="section-title">Refunds</div>
                        ' . $refundsHTML . '
                    </div>' : '') . '
                    
                    ' . ($shipmentsHTML ? '<div class="section">
                        <div class="section-title">Shipments</div>
                        ' . $shipmentsHTML . '
                    </div>' : '') . '
                    
                    ' . ($reviewsHTML ? '<div class="section">
                        <div class="section-title">Reviews</div>
                        ' . $reviewsHTML . '
                    </div>' : '') . '
                    
                    <div class="section">
                        <div class="section-title">Notes</div>
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
