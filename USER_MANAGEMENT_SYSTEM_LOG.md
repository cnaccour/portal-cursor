# User Management System Modernization Log

## Overview
Modernizing J. Joseph Salon Team Portal user management system with invitation-based signup, role management, and proper database persistence while maintaining cPanel hosting compatibility.

## User Requirements
- HTML email invitations
- Both hard/soft deletion options (prefer soft)
- Preserve 5-tier role system (admin > manager > support > staff > viewer)
- Admin-only user management
- Invitation-only signup
- Role-based permissions foundation

## Technical Architecture

### Database Schema Design
```sql
-- Users table (replaces mock_users array)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'support', 'staff', 'viewer') DEFAULT 'viewer',
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    invited_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL
);

-- User invitations table
CREATE TABLE user_invitations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'support', 'staff', 'viewer') DEFAULT 'viewer',
    token VARCHAR(64) UNIQUE NOT NULL,
    invited_by INT NOT NULL,
    status ENUM('pending', 'accepted', 'expired', 'cancelled') DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at)
);

-- User audit log table
CREATE TABLE user_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    old_value JSON NULL,
    new_value JSON NULL,
    performed_by INT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```

## Implementation Phases

### Phase 1: Foundation & Current System Cleanup ✅
- [x] Remove "User Management" text from admin.php
- [x] Create database schema and migrations
- [x] Create migration from mock users to database
- [x] Modernize admin interface design
- [x] Create cPanel migration guide

### Phase 2: User CRUD Operations
- [ ] Add user deletion functionality (soft deletion with restore option)
- [ ] Enhanced role management with change history
- [ ] User status management (active/inactive)
- [ ] Audit logging for all user changes

### Phase 3: Invitation System
- [ ] Create invitation management interface
- [ ] Integrate Replit Mail for HTML email invitations
- [ ] Build invitation acceptance/signup flow
- [ ] Modify existing signup to require valid invitation token
- [ ] Add invitation expiry and resend functionality

### Phase 4: Basic Roles Manager Foundation
- [ ] Create roles permission framework
- [ ] Build roles CRUD interface
- [ ] Design permission inheritance system
- [ ] Create role-based access control foundation

## Email Integration Strategy

### Development (Replit)
- Use Replit Mail integration for invitation emails
- HTML email templates stored in `/templates/emails/`
- Token-based invitation links

### Production (cPanel)
- Switch to PHP `mail()` or SMTP configuration
- Same HTML templates, different delivery method
- Environment-based email configuration

## File Organization
```
includes/
  user-manager.php          # Core user management class
  invitation-manager.php    # Invitation handling
  user-audit.php           # Audit logging
  email-templates.php      # Email template functions

api/
  users/
    create-user.php         # Create new user (admin)
    update-user.php         # Update user details/role
    delete-user.php         # Soft delete user
    restore-user.php        # Restore deleted user
  invitations/
    send-invitation.php     # Send new invitation
    resend-invitation.php   # Resend existing invitation
    cancel-invitation.php   # Cancel pending invitation

templates/
  emails/
    invitation.html         # HTML invitation email template
    invitation.txt          # Text fallback template

database/
  migrations/
    002_create_user_tables.sql    # User management tables
    003_migrate_mock_users.sql    # Data migration script
```

## Security Considerations
- CSRF protection on all user management actions
- Secure invitation token generation (64-byte random)
- Password strength requirements
- Role-based access control validation
- Audit logging for compliance
- Input validation and sanitization
- Proper password hashing with PHP password_hash()

## cPanel Compatibility Notes
- Pure PHP implementation (no external frameworks)
- MySQL PDO for database operations
- File-based email templates
- Session-based authentication
- Compatible with shared hosting environments
- Environment variable configuration for email settings

## Current Status: Phase 1 Implementation
- Database schema created ✅
- Admin interface modernized ✅
- Mock user migration ready ✅
- Foundation for invitation system ✅

---
*This log will be updated as implementation progresses*