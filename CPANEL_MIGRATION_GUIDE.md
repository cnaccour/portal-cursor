# cPanel Migration Guide

## Overview
Complete step-by-step guide for migrating J. Joseph Salon Team Portal from Replit development environment to cPanel production hosting under `/portal` subdirectory.

## Pre-Migration Checklist

### 1. cPanel Requirements Verification
- [ ] PHP 8.0+ support enabled
- [ ] MySQL database available
- [ ] Email sending capabilities (mail() or SMTP)
- [ ] File upload permissions configured
- [ ] Session storage configured
- [ ] Sufficient storage space for attachments
- [ ] SSH access enabled (for git operations)

### 2. Pull Latest Code to cPanel

#### Initial Setup via SSH
```bash
# Connect to your cPanel server via SSH
ssh your_username@your_domain.com

# Navigate to public_html
cd ~/public_html

# Clone the repository into portal directory
git clone https://your_repo_url.git portal
cd portal
```

#### Update Existing Installation
```bash
# Navigate to portal directory
cd ~/public_html/portal

# Pull latest changes and reset to match repository exactly
git reset --hard origin/main

# Set proper permissions after update
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R portaljjosephsal:portaljjosephsal ~/public_html/portal
```

### 3. Database Setup

#### Create Production Database
1. **Login to cPanel → MySQL Databases**
2. **Create Database**: `your_username_salon_portal`
3. **Create Database User**: `your_username_portal_user`
4. **Set Strong Password** (save securely)
5. **Add User to Database** with ALL PRIVILEGES

#### Import Database Schema
Run migration files in this exact order:
```bash
# Upload and execute via cPanel phpMyAdmin or command line:
1. database/migrations/001_create_notifications.sql
2. database/migrations/002_create_user_tables.sql
3. database/migrations/003_create_invitations_table.sql
4. database/migrations/003_migrate_mock_users.sql
5. database/migrations/004_create_shift_reports_table.sql
```

### 4. File Structure Verification

#### Confirm Complete File Structure
```
public_html/portal/
├── api/
├── assets/
│   ├── css/
│   │   └── output.css
│   ├── images/
│   │   └── logo.png
│   └── js/
├── database/
│   └── migrations/
├── forms/
│   └── shift-reports.php
├── includes/
├── reports/
│   └── view.php
├── storage/
├── admin-announcements.php
├── admin.php
├── announcements.php
├── dashboard.php
├── favicon.ico
├── forms.php
├── index.php
├── login.php
├── logout.php
├── reports.php
└── signup.php
```

#### Set File Permissions
```bash
# Set correct permissions for all files and directories
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Ensure specific ownership (replace with your cPanel username)
chown -R portaljjosephsal:portaljjosephsal ~/public_html/portal

# Make storage directories writable
chmod 755 storage/
chmod 755 storage/notifications/
```

## Configuration Changes Required

### 1. Database Connection (`includes/db.php`)

**Replace Development Configuration:**
```php
// DEVELOPMENT (current)
$mock_users = [...]; // Remove this entire array

// PRODUCTION (update to your cPanel database details)
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

**Create `includes/email-config.php` (this file doesn't exist yet):**
```php
<?php
// Production email configuration for invitation system
$email_config = [
    'from_email' => 'noreply@yourdomain.com',      // Replace with your domain
    'from_name' => 'J. Joseph Salon Team Portal',
    'smtp_host' => 'mail.yourdomain.com',          // Check with hosting provider
    'smtp_port' => 587,                            // Usually 587 or 465
    'smtp_user' => 'noreply@yourdomain.com',       // Replace with your email
    'smtp_pass' => 'your_email_password',          // Replace with password
    'use_smtp' => true  // Set false to use PHP mail() instead
];
?>
```

**Update Notification System (`includes/notification-manager.php`):**
```php
// Change this method to use database in production:
private function isDatabaseAvailable() {
    return true;  // Change from false to true for production
}
```

### 3. Verify CSS Reference

**Confirm `includes/header.php` uses correct CSS file:**
```html
<link href="assets/css/output.css" rel="stylesheet">
```
*Note: This should already be correct - the file uses output.css, not tailwind.css*

### 4. Verify Relative Navigation Links

**All navigation links in the project should be relative (no leading slash):**
- ✅ `dashboard.php` (not `/dashboard.php`)
- ✅ `announcements.php` (not `/announcements.php`)
- ✅ `login.php` (not `/login.php`)

*Note: This should already be correct - all links are relative for cPanel compatibility*

### 5. Session Configuration and .htaccess

**Create `.htaccess` file in portal directory:**
```apache
# Security headers for PHP 8+
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# PHP 8+ session security
php_value session.cookie_httponly 1
php_value session.cookie_secure 1
php_value session.use_strict_mode 1
php_value session.cookie_samesite Strict

# File upload limits (adjust as needed)
php_value upload_max_filesize 10M
php_value post_max_size 12M
php_value max_execution_time 300
php_value max_input_vars 3000

# Disable directory browsing
Options -Indexes

# Enable gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Protect sensitive files
<Files "*.md">
    Order allow,deny
    Deny from all
</Files>
<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
<Files "*.txt">
    Order allow,deny
    Deny from all
</Files>
<Files ".htaccess">
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
- [ ] Run user table migration scripts in correct order
- [ ] Test user deletion and restoration functionality
- [ ] Test user status management (active/inactive toggles)
- [ ] Verify audit logging in user_audit_log table
- [ ] Create and configure email-config.php for invitation emails
- [ ] Test invitation email sending
- [ ] Configure SMTP settings for invitations
- [ ] Verify role-based access controls

### 3. Announcement System
- [ ] Test file upload functionality
- [ ] Verify attachment storage permissions
- [ ] Test rich text editor functionality
- [ ] Confirm notification integration
- [ ] Verify In House Education Schedule 2025 static announcement displays correctly

### 4. File Attachments and Assets
- [ ] Verify CSS loads correctly from assets/css/output.css
- [ ] Test image loading (logo.png)
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
- [ ] View In House Education Schedule 2025 announcement
- [ ] Create new announcement
- [ ] Upload attachment files
- [ ] Test rich text formatting
- [ ] Verify notifications sent

### Forms and Reports
- [ ] Access shift reports form
- [ ] Submit test shift report
- [ ] View shift report in reports section
- [ ] Test report viewing and printing

### Notifications
- [ ] Check notification bell functionality
- [ ] Test mark as read/unread
- [ ] Verify role-based filtering
- [ ] Test notification polling

## Troubleshooting Common Issues

### CSS and Asset 404 Errors
```bash
# If CSS or images don't load, check permissions:
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R portaljjosephsal:portaljjosephsal ~/public_html/portal

# Verify CSS file exists and is accessible:
ls -la assets/css/output.css
# Should show: -rw-r--r-- (644 permissions)

# Check .htaccess isn't blocking assets
# Remove any conflicting rules in .htaccess
```

### Database Connection Errors
```php
// Create test-db.php in portal root (remove after testing):
<?php
require_once 'includes/db.php';
echo "Database connection successful!";
echo "Connected to: " . $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
?>
```

### Email Delivery Issues
- Check cPanel Email Deliverability settings
- Verify SPF/DKIM records for your domain
- Test with simple PHP mail() first before SMTP
- Check server mail logs in cPanel
- Verify email-config.php file exists and has correct credentials

### Permission Issues After Updates
```bash
# Reset all permissions after git pull:
cd ~/public_html/portal
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R portaljjosephsal:portaljjosephsal ~/public_html/portal

# Make storage directories writable:
chmod 755 storage/
chmod 755 storage/notifications/

# Check if .htaccess exists and is readable:
ls -la .htaccess
```

### Session Issues
- Check PHP session configuration in cPanel
- Verify session.save_path is writable
- Ensure session cookies are configured correctly
- Test with session_start() in a simple PHP file

### Navigation/Routing Issues
- All links should be relative (no leading slash)
- Verify all PHP files exist in correct locations
- Check that forms/ and reports/ subdirectories have proper files
- Test all menu navigation links

## Performance Optimization

### Database Optimization
- [ ] Add database indexes for frequently queried fields
- [ ] Optimize notification polling frequency
- [ ] Implement query caching if needed

### File Optimization
- [ ] Verify gzip compression is enabled in .htaccess
- [ ] Optimize image assets if needed
- [ ] Confirm CSS file size is reasonable (~88KB for output.css)

### Security Hardening
- [ ] Change default admin credentials immediately
- [ ] Update all default passwords
- [ ] Review and set proper file permissions
- [ ] Configure security headers in .htaccess
- [ ] Set up regular backups

## Maintenance and Updates

### Regular Updates
```bash
# To update the portal with latest code:
cd ~/public_html/portal
git reset --hard origin/main

# Reset permissions after update:
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R portaljjosephsal:portaljjosephsal ~/public_html/portal
```

### Backup Strategy
- [ ] Set up automated database backups via cPanel
- [ ] Configure file system backups
- [ ] Test backup restoration process
- [ ] Document backup schedules
- [ ] Keep recent backups before major updates

### Monitoring Tasks
- [ ] Set up error log monitoring
- [ ] Monitor PHP error logs regularly
- [ ] Check storage space usage
- [ ] Monitor email delivery rates
- [ ] Review security logs periodically

---
*This guide is current as of the latest project version with relative paths, output.css, and all current features. Update this guide when new features are added.*