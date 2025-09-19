-- Notification System Database Migration
-- Run this migration when setting up production database

-- Core notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL COMMENT 'Type of notification: announcement, system, alert, etc.',
    title VARCHAR(255) NOT NULL COMMENT 'Notification title',
    message TEXT COMMENT 'Notification content/message', 
    link_url VARCHAR(255) COMMENT 'Internal link URL (relative paths only)',
    icon VARCHAR(50) DEFAULT 'bell' COMMENT 'Icon identifier for UI',
    target_roles JSON COMMENT 'Array of target user roles',
    created_by INT COMMENT 'User ID who triggered the notification',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL COMMENT 'Optional expiration date',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether notification is still active',
    
    INDEX idx_active_notifications (is_active, created_at),
    INDEX idx_notification_type (type),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User-specific notification tracking
CREATE TABLE user_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL COMMENT 'ID of user who should see this notification',
    notification_id INT NOT NULL COMMENT 'Reference to notifications table',
    is_read BOOLEAN DEFAULT FALSE COMMENT 'Whether user has read this notification',
    read_at TIMESTAMP NULL COMMENT 'When notification was marked as read',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_notification (user_id, notification_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    
    INDEX idx_user_unread (user_id, is_read, created_at),
    INDEX idx_user_notifications (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing
INSERT INTO notifications (type, title, message, target_roles, created_by) VALUES
('system', 'Welcome to the Notification System', 'The notification system is now active and ready to keep you informed.', '["admin", "manager", "staff", "support", "viewer"]', 1),
('announcement', 'System Update Complete', 'The team portal has been updated with new notification features.', '["admin", "manager"]', 1);