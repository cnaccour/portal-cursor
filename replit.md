# Shift Management System

## Overview

This is a web-based shift management system built with a traditional PHP architecture. The application handles employee shift tracking, daily checklists, administrative tasks, and comprehensive user management for what appears to be a retail or service business with multiple locations. The system features invitation-based user signup, role-based access control, and comprehensive audit logging. It is designed to be simple, lightweight, and compatible with standard cPanel hosting environments.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

**Frontend Architecture**
- Pure HTML with TailwindCSS for styling via CDN
- Alpine.js for interactive JavaScript functionality via CDN
- No build process or bundling required
- Mobile-responsive design using Tailwind's utility classes
- Optimized mobile UX with touch-friendly interface, no gradient avatars, responsive layouts

**Backend Architecture**
- Pure PHP 8+ with no external frameworks
- Traditional multi-page application (MPA) structure
- Session-based authentication using PHP's native `$_SESSION`
- Password security using PHP's `password_hash()` and `password_verify()`
- Organized into logical directories (includes/, api/, forms/)

**Data Storage**
- MySQL database with PDO for database interactions
- No ORM - direct SQL queries for simplicity
- Session data stored server-side for authentication state

**Authentication & Authorization**
- PHP session-based authentication with session regeneration for security
- Five-tier role hierarchy: Administrator → Manager → Support → Staff → Viewer
- Comprehensive role-based access control with numerical permission levels
- CSRF protection on sensitive administrative actions
- Secure password handling with built-in PHP functions
- Admin interface for user role management
- No external authentication services

**File Organization**
- `includes/` - Shared PHP components (database, auth, layout)
- `api/` - Backend API endpoints (save-shift-report.php)
- `forms/` - Form handling scripts (shift-reports.php)
- `reports/` - Report viewing pages (view.php)
- `assets/` - Static files (CSS, JS, images)
- Root level - Main application pages (announcements.php, dashboard.php, admin.php, etc.)
- Data files - shift-reports.txt for local development storage

**User Role Management**
- **Administrator**: Full system access, user management, all features
- **Manager**: Location management, staff oversight, operational controls
- **Support**: Customer support functions, limited administrative access
- **Staff**: Basic access to shift reporting and standard features
- **Viewer**: Read-only access to permitted content
- Role indicators visible in header and dashboard
- Admin panel (/admin.php) for role management (admin-only access)
- Secure role updates with CSRF protection

**Invitation-Based User Management (Phase 3)**
- Secure invitation system with HTML email templates
- Token-based signup with expiration handling
- Admin invitation interface with role selection
- Invitation status tracking (pending, accepted, expired, revoked)
- CSRF protection and secure token handling
- Comprehensive audit logging with IP tracking
- Production-ready security measures

## External Dependencies

**CDN Resources**
- TailwindCSS - Styling framework delivered via CDN
- Alpine.js - Lightweight JavaScript framework via CDN

**Database**
- MySQL - Primary data storage
- PDO - PHP database abstraction layer (built-in)

**Hosting Requirements**
- cPanel/WHM compatible hosting
- PHP 8+ support
- MySQL database support
- Standard web hosting environment (no special deployment needs)

**Notable Exclusions**
- No Node.js dependencies
- No external authentication services (Firebase, Supabase, ReplitAuth)
- No build tools or package managers required
- No external API integrations for core functionality