# cPanel Migration Guide

## Overview
This JJS Team Portal is designed to be fully compatible with standard cPanel hosting environments. The system uses traditional PHP architecture with no special dependencies, making it ideal for standard web hosting.

## Pre-Migration Checklist

### ✅ **Hosting Requirements**
- **PHP 8.0+** support
- **MySQL/MariaDB** database 
- **cPanel/WHM** hosting environment
- **Standard web hosting** (no Node.js or special services required)

### ✅ **Database Migration**
1. **Export Database**: Use phpMyAdmin or database export tools to export all tables
2. **Import to Production**: Create new database in cPanel and import the exported SQL
3. **Update Connection**: Modify `includes/db.php` with production database credentials

### ✅ **File Transfer**
1. **Download Project**: Export entire project from Replit
2. **Upload Files**: Transfer all files to cPanel public_html directory
3. **Set Permissions**: Ensure proper file permissions (644 for files, 755 for directories)

## Email System Compatibility

### ✅ **Current Email Setup**
The admin forms system uses PHP's built-in `mail()` function which is **fully compatible** with cPanel hosting:

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
The time off request notifications will work immediately on cPanel:
- ✅ **Automatic emails** to `bfernandez@jjosephsalon.com`
- ✅ **Form sharing** via email
- ✅ **Professional formatting** with all submission details
- ✅ **Error handling** that doesn't break form submissions

## Configuration Changes Needed

### 1. **Database Connection** (`includes/db.php`)
```php
// Update these with your cPanel database credentials
$host = 'localhost';  // Usually localhost on cPanel
$dbname = 'your_cpanel_database_name';
$username = 'your_cpanel_db_user';
$password = 'your_cpanel_db_password';
```

### 2. **Email Settings** (Optional Enhancement)
```php
// For better email delivery, you can configure SMTP
// But the current mail() function works perfectly fine
```

### 3. **File Paths** (Already Compatible)
The system uses relative paths that work perfectly on cPanel:
- ✅ `require __DIR__.'/../includes/auth.php'`
- ✅ `require_once __DIR__.'/includes/db.php'`

## Security Considerations

### ✅ **Production Ready Features**
- **Session security**: PHP sessions with regeneration
- **CSRF protection**: Implemented on admin actions
- **Role-based access**: Admin/Manager/Staff hierarchy
- **Password hashing**: PHP's secure password_hash()
- **SQL injection prevention**: PDO prepared statements

### ✅ **cPanel Security Integration**
- **SSL certificates**: cPanel handles HTTPS automatically
- **File permissions**: Standard hosting security
- **Database security**: cPanel's MySQL security features

## Testing After Migration

### 1. **Basic Functionality**
- [ ] Login system works
- [ ] Dashboard loads correctly
- [ ] Admin forms accessible

### 2. **Email System**
- [ ] Time off request notifications send
- [ ] Form sharing emails work
- [ ] No PHP errors in logs

### 3. **Database Operations**
- [ ] Form submissions save correctly
- [ ] Admin settings update properly
- [ ] Reports generate successfully

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

### Step 4: **Test & Verify**
1. Test login functionality
2. Submit a test time off request
3. Verify email notifications work
4. Check admin forms management

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