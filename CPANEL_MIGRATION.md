# cPanel Migration Guide - J. Joseph Salon Team Portal

## Overview
Complete step-by-step guide for migrating J. Joseph Salon Team Portal from Replit development environment to cPanel production hosting. The system uses traditional PHP architecture with no special dependencies, making it ideal for standard web hosting.

## Pre-Migration Checklist

### ✅ **Hosting Requirements**
- **PHP 8.0+** support
- **MySQL/MariaDB** database 
- **cPanel/WHM** hosting environment
- **Email sending capabilities** (mail() or SMTP)
- **File upload permissions** configured
- **Session storage** configured
- **Sufficient storage space** for attachments
- **SSH access enabled** (for git operations)

### ✅ **Pull Latest Code to cPanel**

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
chown -R your_username:your_username ~/public_html/portal
```

## Database Migration

### ✅ **Create Production Database**
1. **Login to cPanel → MySQL Databases**
2. **Create Database**: `your_username_salon_portal`
3. **Create Database User**: `your_username_portal_user`
4. **Set Strong Password** (save securely)
5. **Add User to Database** with ALL PRIVILEGES

### ✅ **Import Database Schema**
Run migration files in this exact order via phpMyAdmin:
```sql
-- Execute these in order:
1. database/migrations/001_create_notifications.sql
2. database/migrations/002_create_user_tables.sql
3. database/migrations/003_create_invitations_table.sql
4. database/migrations/003_migrate_mock_users.sql
5. database/migrations/004_create_shift_reports_table.sql

-- New: Knowledge Base Support
6. CREATE TABLE kb_articles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    category VARCHAR(100),
    tags TEXT,
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Configuration Changes Required

### 1. **Database Connection** (`includes/db.php`)
```php
// Update these with your cPanel database credentials
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

### 2. **File Permissions**
```bash
# Set correct permissions for all files and directories
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Ensure specific ownership (replace with your cPanel username)
chown -R your_username:your_username ~/public_html/portal

# Make storage directories writable
chmod 755 storage/
chmod 755 storage/notifications/
chmod 755 assets/kb/  # For knowledge base uploads
```

### 3. **Create .htaccess File**
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
```

## Email System Compatibility

### ✅ **Current Email Setup**
The forms system uses PHP's built-in `mail()` function which is **fully compatible** with cPanel hosting:

```php
// This works perfectly on cPanel hosting
$headers = "From: noreply@jjosephsalon.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$mail_sent = mail($email, $subject, $message, $headers);
```

### ✅ **cPanel Email Features**
- **Automatic sending**: PHP mail() works out of the box
- **From address**: Uses your domain (noreply@jjosephsalon.com)
- **No configuration needed**: cPanel handles email routing automatically
- **SMTP alternative**: Can upgrade to SMTP if needed

### ✅ **Email Notifications**
All email functionality will work immediately on cPanel:
- ✅ **Time off request notifications** to configured emails
- ✅ **Form sharing** via email with professional formatting
- ✅ **Knowledge base sharing** (new feature)
- ✅ **Error handling** that doesn't break form submissions

## File Structure Verification

### Complete File Structure Expected
```
public_html/portal/
├── api/
│   ├── forms/
│   ├── invitations/
│   ├── notifications/
│   └── kb/                 # New: Knowledge base API
├── assets/
│   ├── css/
│   │   └── output.css
│   ├── images/
│   │   └── logo.png
│   ├── js/
│   └── kb/                 # New: KB uploads directory
├── database/
│   └── migrations/
├── forms/
├── includes/
├── reports/
├── storage/
├── admin-announcements.php
├── admin-forms.php
├── admin-kb.php           # New: KB admin
├── admin.php
├── announcements.php
├── dashboard.php
├── favicon.ico
├── form-submissions.php
├── forms.php
├── index.php
├── kb-article.php         # New: Article viewer
├── knowledge-base.php     # Updated: Article listing
├── login.php
├── logout.php
├── reports.php
└── signup.php
```

## Security Considerations

### ✅ **Production Ready Features**
- **Session security**: PHP sessions with regeneration
- **CSRF protection**: Implemented on admin actions
- **Role-based access**: Admin/Manager/Staff hierarchy
- **Password hashing**: PHP's secure password_hash()
- **SQL injection prevention**: PDO prepared statements
- **File upload validation**: Restricted file types and sizes
- **XSS protection**: All user inputs sanitized

### ✅ **cPanel Security Integration**
- **SSL certificates**: cPanel handles HTTPS automatically
- **File permissions**: Standard hosting security
- **Database security**: cPanel's MySQL security features

## Testing After Migration

### 1. **Basic Functionality**
- [ ] Login system works
- [ ] Dashboard loads correctly
- [ ] Admin panels accessible
- [ ] Navigation works properly

### 2. **Email System**
- [ ] Time off request notifications send
- [ ] Form sharing emails work
- [ ] Knowledge base sharing works
- [ ] No PHP errors in logs

### 3. **Database Operations**
- [ ] Form submissions save correctly
- [ ] Admin settings update properly
- [ ] Knowledge base articles save/load
- [ ] Reports generate successfully

### 4. **New Knowledge Base Features**
- [ ] Admin can create articles
- [ ] Rich text editor works
- [ ] File uploads function
- [ ] Article categories work
- [ ] Public viewing displays correctly

## Migration Steps

### Step 1: **Prepare Production Database**
1. Create MySQL database in cPanel
2. Create database user with full permissions
3. Note down credentials for configuration

### Step 2: **Export from Replit**
1. Download complete project files
2. Export database structure and data
3. Test local backup if possible

### Step 3: **Upload to cPanel**
1. Upload all files via File Manager or FTP
2. Import database via phpMyAdmin
3. Update `includes/db.php` with production credentials
4. Create assets/kb/ directory for uploads

### Step 4: **Test & Verify**
1. Test login functionality
2. Submit a test time off request
3. Verify email notifications work
4. Check admin forms management
5. Test knowledge base admin features

## Troubleshooting Common Issues

### CSS and Asset 404 Errors
```bash
# If CSS or images don't load, check permissions:
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R your_username:your_username ~/public_html/portal

# Verify CSS file exists and is accessible:
ls -la assets/css/output.css
# Should show: -rw-r--r-- (644 permissions)
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

### Permission Issues After Updates
```bash
# Reset all permissions after git pull:
cd ~/public_html/portal
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R your_username:your_username ~/public_html/portal

# Make storage directories writable:
chmod 755 storage/
chmod 755 storage/notifications/
chmod 755 assets/kb/
```

## Maintenance and Updates

### Regular Updates
```bash
# To update the portal with latest code:
cd ~/public_html/portal
git reset --hard origin/main

# Reset permissions after update:
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chown -R your_username:your_username ~/public_html/portal
```

### Backup Strategy
- [ ] Set up automated database backups via cPanel
- [ ] Configure file system backups
- [ ] Test backup restoration process
- [ ] Keep recent backups before major updates

## Post-Migration Benefits

### ✅ **Production Email System**
- **Real email delivery** to actual recipients
- **Professional sender** (noreply@jjosephsalon.com)
- **Reliable SMTP** through cPanel's mail system

### ✅ **Performance & Reliability**
- **Fast loading** on production hosting
- **Database optimization** with MySQL
- **CDN compatibility** (TailwindCSS, Alpine.js)

### ✅ **Maintenance & Updates**
- **Easy file updates** through cPanel File Manager
- **Database backups** via cPanel tools
- **Email logs** available in cPanel

## Notes
- The system is **production-ready** as-is
- **No code changes** needed for core functionality
- **Email system works immediately** on cPanel
- **All features compatible** with standard hosting
- **Knowledge base system** fully cPanel compatible

---
*This guide includes all features through the knowledge base system. Update this guide when new features are added.*