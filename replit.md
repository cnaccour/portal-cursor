# Shift Management System

## Overview

This is a web-based shift management system built with a traditional PHP architecture. The application handles employee shift tracking, daily checklists, and administrative tasks for what appears to be a retail or service business with multiple locations. The system is designed to be simple, lightweight, and compatible with standard cPanel hosting environments.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

**Frontend Architecture**
- Pure HTML with TailwindCSS for styling via CDN
- Alpine.js for interactive JavaScript functionality via CDN
- No build process or bundling required
- Mobile-responsive design using Tailwind's utility classes

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
- PHP session-based authentication
- No external authentication services
- Role-based access control likely implemented through session variables
- Secure password handling with built-in PHP functions

**File Organization**
- `includes/` - Shared PHP components (database, auth, layout)
- `api/` - Backend API endpoints
- `forms/` - Form handling scripts
- `assets/` - Static files (CSS, JS, images)
- Root level - Main application pages

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