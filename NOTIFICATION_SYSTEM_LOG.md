# Notification System Implementation Log

## Overview
Building a role-based notification system for J. Joseph Salon Team Portal that integrates seamlessly with existing architecture and supports future feature expansion.

## Architecture Decisions

### Core Design Principles
1. **Role-Based Control**: Notifications can be targeted by user roles (admin, manager, support, staff, viewer)
2. **Non-Breaking Integration**: Additive approach that doesn't modify existing functionality
3. **Portable Implementation**: Pure PHP/MySQL solution compatible with cPanel hosting
4. **Future-Proof**: Easy integration points for new features

### Database Schema
```sql
-- Core notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,           -- 'announcement', 'system', 'alert', etc.
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link_url VARCHAR(255),               -- Internal links only for security
    icon VARCHAR(50) DEFAULT 'bell',     -- Icon identifier
    target_roles JSON,                   -- Array of roles: ["admin", "manager"]
    created_by INT,                      -- User ID who triggered notification
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- User-specific notification tracking
CREATE TABLE user_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_notification (user_id, notification_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_user_notifications_unread ON user_notifications (user_id, is_read, created_at);
CREATE INDEX idx_notifications_active ON notifications (is_active, created_at);
```

### API Endpoints
- `GET /api/notifications.php` - Get user's notifications with unread count
- `POST /api/notifications/mark-read.php` - Mark specific notification as read
- `POST /api/notifications/mark-all-read.php` - Mark all user notifications as read

### Role-Based Notification Methods
```php
// Target specific roles
NotificationManager::notify_roles(['admin', 'manager'], $notification_data);

// Target specific users
NotificationManager::notify_users([1, 2, 3], $notification_data);

// Target all users
NotificationManager::notify_all($notification_data);
```

## Integration Points for Future Features

### Current Integrations
1. **Announcements**: Auto-notify when announcements are created/updated/deleted
2. **User Management**: Notify on role changes or account updates

### Future Integration Examples
```php
// Shift Reports
NotificationManager::notify_roles(['manager', 'admin'], [
    'type' => 'shift_report',
    'title' => 'New Shift Report Submitted',
    'message' => "Shift report for {$date} has been submitted by {$user_name}",
    'link_url' => "/reports/view.php?id={$report_id}"
]);

// Form Submissions
NotificationManager::notify_roles(['support', 'admin'], [
    'type' => 'form_submission',
    'title' => 'New Form Submission',
    'message' => "{$form_name} has been submitted",
    'link_url' => "/forms/view.php?id={$submission_id}"
]);

// System Alerts
NotificationManager::notify_roles(['admin'], [
    'type' => 'system_alert',
    'title' => 'System Maintenance Scheduled',
    'message' => 'Scheduled maintenance on {$date}',
    'expires_at' => $maintenance_date
]);
```

## Implementation Status
- [x] Database schema and migrations (`database/migrations/001_create_notifications.sql`)
- [x] NotificationManager class with role-based targeting (`includes/notification-manager.php`)
- [x] API endpoints with authentication and CSRF protection (`api/notifications.php`, `api/notifications/mark-read.php`, `api/notifications/mark-all-read.php`)
- [x] Frontend notification bell component (`includes/header.php` - Alpine.js component with polling)
- [x] Integration with announcement system (`api/save-announcement.php`)
- [x] Testing and verification

## Security Considerations
- All API endpoints require authentication ✓
- CSRF protection on all POST endpoints ✓
- Role-based access control for notification visibility ✓
- Internal link validation (no external URLs) ✓
- XSS protection through proper escaping ✓

## Files Modified/Created
### Core System Files
- `includes/notification-manager.php` - Main notification management class
- `database/migrations/001_create_notifications.sql` - Database schema for production

### API Endpoints
- `api/notifications.php` - GET notifications with unread count
- `api/notifications/mark-read.php` - POST mark single notification as read
- `api/notifications/mark-all-read.php` - POST mark all notifications as read

### Frontend Integration
- `includes/header.php` - Added notification bell with Alpine.js component and polling

### Feature Integration
- `api/save-announcement.php` - Added notification emissions for new/updated announcements

### Storage (Development)
- `storage/notifications/notifications.json` - Mock notification storage
- `storage/notifications/read_status.json` - Mock read status tracking

## Role-Based Notification Rules Implemented
- **New Announcements**: Notify all roles (admin, manager, support, staff, viewer)
- **Updated Announcements**: Notify working roles only (admin, manager, support, staff) - excludes viewers to reduce noise
- **Future Integrations**: Ready for shift reports, form submissions, system alerts, etc.

## Usage Examples for Future Features
```php
// Shift Report Submitted
NotificationManager::notify_roles(['manager', 'admin'], [
    'type' => 'shift_report',
    'title' => 'New Shift Report Submitted',
    'message' => "Shift report for {$date} submitted by {$user_name}",
    'link_url' => "/reports/view.php?id={$report_id}",
    'icon' => 'document'
]);

// System Maintenance Alert
NotificationManager::notify_roles(['admin'], [
    'type' => 'system_alert',
    'title' => 'Scheduled Maintenance',
    'message' => 'System maintenance scheduled for tonight at 2 AM',
    'expires_at' => date('Y-m-d H:i:s', strtotime('+1 week'))
]);

// User Role Change
NotificationManager::notify_users([$user_id], [
    'type' => 'account',
    'title' => 'Role Updated',
    'message' => "Your role has been changed to {$new_role}",
    'icon' => 'user'
]);
```

---
*This log will be maintained throughout development to ensure easy future integration*