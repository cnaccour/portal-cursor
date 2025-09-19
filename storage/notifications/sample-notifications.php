<?php
/**
 * Sample Notifications Creator for Testing
 * Run this file to create sample notifications for testing the system
 */

require_once __DIR__ . '/../../includes/notification-manager.php';

// Create sample notifications for testing
try {
    echo "Creating sample notifications...\n";
    
    // Welcome notification for all users
    NotificationManager::notify_roles(['admin', 'manager', 'support', 'staff', 'viewer'], [
        'type' => 'system',
        'title' => 'Welcome to the Notification System',
        'message' => 'The notification system is now active. You\'ll receive updates about announcements, reports, and important events.',
        'icon' => 'system'
    ]);
    
    // Sample announcement notification
    NotificationManager::notify_roles(['admin', 'manager', 'staff'], [
        'type' => 'announcement',
        'title' => 'New Announcement: Team Meeting Tomorrow',
        'message' => 'Important team meeting scheduled for tomorrow at 10 AM. Please check announcements for details.',
        'link_url' => '/announcements.php',
        'icon' => 'announcement'
    ]);
    
    // Admin-only system notification
    NotificationManager::notify_roles(['admin'], [
        'type' => 'system_alert',
        'title' => 'System Update Available',
        'message' => 'A new system update is available. Review and install when convenient.',
        'icon' => 'system'
    ]);
    
    // Manager notification
    NotificationManager::notify_roles(['manager', 'admin'], [
        'type' => 'report',
        'title' => 'Weekly Report Summary',
        'message' => 'The weekly report summary is ready for review.',
        'link_url' => '/reports.php',
        'icon' => 'document'
    ]);
    
    echo "Sample notifications created successfully!\n";
    echo "Login to the system to see them in the notification bell.\n";
    
} catch (Exception $e) {
    echo "Error creating sample notifications: " . $e->getMessage() . "\n";
}
?>