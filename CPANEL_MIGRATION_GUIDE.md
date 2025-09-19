# cPanel Migration Guide

## Overview
Complete guide for migrating J. Joseph Salon Team Portal from Replit development environment to cPanel production hosting.

## Pre-Migration Checklist

### 1. cPanel Requirements Verification
- [ ] PHP 8.0+ support enabled
- [ ] MySQL database available
- [ ] Email sending capabilities (mail() or SMTP)
- [ ] File upload permissions configured
- [ ] Session storage configured
- [ ] Sufficient storage space for attachments

### 2. Database Setup

#### Create Production Database
1. **Login to cPanel → MySQL Databases**
2. **Create Database**: `your_username_salon_portal`
3. **Create Database User**: `your_username_portal_user`
4. **Set Strong Password** (save securely)
5. **Add User to Database** with ALL PRIVILEGES

#### Import Database Schema
```bash
# Upload and run migration files in order:
1. database/migrations/001_create_notifications.sql
2. database/migrations/002_create_user_tables.sql
3. database/migrations/003_migrate_mock_users.sql
```

### 3. File Upload and Configuration

#### Upload All Files via File Manager or FTP
```
public_html/
├── includes/
├── api/
├── templates/
├── assets/
├── storage/
├── database/
├── index.php
├── login.php
├── dashboard.php
├── admin.php
├── announcements.php
└── [all other PHP files]
```

#### Set File Permissions
```bash
# Via cPanel File Manager or FTP:
Files: 644 (readable by web server)
Directories: 755 (executable by web server)
Storage directories: 755 (writable for uploads)
```

## Configuration Changes Required

### 1. Database Connection (`includes/db.php`)

**Replace Development Configuration:**
```php
// DEVELOPMENT (current)
$mock_users = [...]; // Remove this

// PRODUCTION (update to)
$host = 'localhost';  // Usually localhost on cPanel
$dbname = 'your_username_salon_portal';  // Your database name
$username = 'your_username_portal_user'; // Your database user
$password = 'your_database_password';    // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please contact support.');
}
```

### 2. Email Configuration

**For Notification System (`includes/notification-manager.php`):**
```php
// Update isDatabaseAvailable() method:
private function isDatabaseAvailable() {
    // Set to true for production database usage
    return true;  // Change from false to true
}
```

**For Invitation Emails (create `includes/email-config.php`):**
```php
<?php
// Production email configuration
$email_config = [
    'from_email' => 'noreply@yourdomain.com',
    'from_name' => 'J. Joseph Salon Team Portal',
    'smtp_host' => 'mail.yourdomain.com',  // Check with hosting provider
    'smtp_port' => 587,                     // Usually 587 or 465
    'smtp_user' => 'noreply@yourdomain.com',
    'smtp_pass' => 'your_email_password',
    'use_smtp' => true  // Set false to use PHP mail() instead
];
?>
```

### 3. File Paths and URLs

**Update any hardcoded paths in:**
- `includes/header.php` - Asset URLs
- `includes/announcement-helpers.php` - File upload paths
- `api/` files - Any file operations

### 4. Session Configuration

**Add to `.htaccess` (create if doesn't exist):**
```apache
# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# PHP session security
php_value session.cookie_httponly 1
php_value session.cookie_secure 1
php_value session.use_strict_mode 1

# File upload limits (adjust as needed)
php_value upload_max_filesize 10M
php_value post_max_size 12M
php_value max_execution_time 300

# Disable directory browsing
Options -Indexes

# Protect sensitive files
<Files "*.md">
    Order allow,deny
    Deny from all
</Files>
<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
```

## Feature-Specific Migration Tasks

### 1. Notification System
- [ ] Update `NotificationManager::isDatabaseAvailable()` to return `true`
- [ ] Test notification creation and delivery
- [ ] Verify database table creation
- [ ] Test role-based notification filtering

### 2. User Management System  
- [ ] Run user table migration scripts
- [ ] Test user deletion and restoration functionality
- [ ] Test user status management (active/inactive toggles)
- [ ] Verify audit logging in user_audit_log table
- [ ] Test invitation email sending
- [ ] Configure SMTP settings for invitations
- [ ] Verify role-based access controls

### 3. Announcement System
- [ ] Test file upload functionality
- [ ] Verify attachment storage permissions
- [ ] Test rich text editor functionality
- [ ] Confirm notification integration

### 4. File Attachments
- [ ] Set proper storage directory permissions
- [ ] Test file upload limits
- [ ] Verify file type restrictions
- [ ] Test download functionality

## Testing Checklist Post-Migration

### Authentication & Access
- [ ] Login with admin account
- [ ] Test role-based page access
- [ ] Verify session persistence
- [ ] Test logout functionality

### User Management
- [ ] Send test invitation
- [ ] Verify invitation email delivery
- [ ] Test user role changes
- [ ] Test user deletion/restoration

### Announcements
- [ ] Create new announcement
- [ ] Upload attachment files
- [ ] Test rich text formatting
- [ ] Verify notifications sent

### Notifications
- [ ] Check notification bell functionality
- [ ] Test mark as read/unread
- [ ] Verify role-based filtering
- [ ] Test notification polling

## Troubleshooting Common Issues

### Database Connection Errors
```php
// Add this to check database connectivity
// Create test-db.php (remove after testing)
<?php
require_once 'includes/db.php';
echo "Database connection successful!";
?>
```

### Email Delivery Issues
- Check cPanel Email Deliverability settings
- Verify SPF/DKIM records
- Test with simple PHP mail() first
- Check server mail logs

### File Permission Issues
```bash
# Set correct permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 storage/
chmod 755 storage/announcements/
chmod 755 storage/notifications/
```

### Session Issues
- Check PHP session configuration in cPanel
- Verify session.save_path is writable
- Ensure session cookies are configured correctly

## Performance Optimization

### Database Optimization
- [ ] Add database indexes for frequently queried fields
- [ ] Optimize notification polling frequency
- [ ] Implement query caching if needed

### File Optimization
- [ ] Enable gzip compression in .htaccess
- [ ] Optimize image assets
- [ ] Minify CSS/JS if using custom files

### Security Hardening
- [ ] Change default admin credentials
- [ ] Update all passwords
- [ ] Review file permissions
- [ ] Configure security headers
- [ ] Set up regular backups

## Backup Strategy
- [ ] Set up automated database backups
- [ ] Configure file system backups
- [ ] Test backup restoration process
- [ ] Document backup schedules

## Maintenance Tasks
- [ ] Set up error log monitoring
- [ ] Configure automated security updates
- [ ] Plan regular backup testing
- [ ] Schedule periodic security reviews

---
*Keep this guide updated as new features are added to the system*