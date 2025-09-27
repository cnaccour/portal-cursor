# J. Joseph Salon Team Portal - Development Context
**Last Updated:** September 27, 2025  
**AI Assistant Memory File** — Keep this updated as we work together

**Assistant**: Working with GPT-5 in Cursor

## 🚀 Quick Start in Cursor
- Open this file first: `DEVELOPMENT_CONTEXT.md` (project overview + status)
- Then open, in order:
  1) `includes/db.php` (PDO + env + MAMP socket)
  2) `includes/shift-report-manager.php` (DB mode; MySQL usage)
  3) `migrate.php` (how migrations run locally)
  4) `includes/config.php` (base URL + future redirect helper)
  5) `CPANEL_MIGRATION.md` (deployment specifics)
- Local commands (MAMP): see "Commands (Local)" below

## 🎯 Project Overview
Traditional PHP application for J. Joseph Salon team management.
- Purpose: Employee shift tracking, forms, announcements, knowledge base
- Architecture: Multi-page PHP app (no frameworks)
- Hosting target: cPanel/WHM (production-ready from day 1)

## 🛠 Tech Stack
- Frontend: TailwindCSS + Alpine.js (CDN only)
- Backend: Pure PHP 8+
- Database: MySQL with PDO
- Auth: PHP Sessions (`$_SESSION`)
- Hosting: cPanel-compatible

## 🧪 Local Development Environment
- Local: MAMP PRO (macOS)
- PHP: 8.2.26 (MAMP)
- MySQL Socket: `/Applications/MAMP/tmp/mysql/mysql.sock`
- DB: `portal_dev2` (root/root)
- App URL: `http://portaltest:8888`
- Helper: run-php.sh (deleted; can recreate if needed)

## 📁 Migration Files
Production (run on cPanel):
- `001_create_notifications.sql`
- `002_create_user_tables.sql`
- `003_create_invitations_table.sql`
- `004_create_shift_reports_table.sql`

Knowledge Base related (ensure schema parity on prod):
- `009_add_kb_print_control.sql` (adds `allow_print`)
- `010_add_kb_sections_control.sql` (adds `enable_sections`)
- `011_add_kb_user_tracking.sql` (adds `created_by`, `updated_by` + FKs)

Development-only demo data (do NOT run on prod):
- `003_migrate_mock_users.sql`
- `005_insert_demo_shift_reports.sql`
- `006_add_eliana_stewson_user.sql`
- `007_add_eliana_shift_report.sql` (deleted; demo only)

## 👥 Users & Demo Data (Dev Only)
- Users: Admin User, Staff User, Eliana Stewson (manager)
- Demo Shift Reports: 4 realistic reports across locations/shift types

## 🔧 Key Files to Reference
- `includes/db.php` — PDO setup; prefers `public/includes/db.php` in production
- `public/includes/db.php` — cPanel DB credentials + DSN fallbacks; runtime guard for `password_resets`
- `includes/shift-report-manager.php` — fixed to MySQL (no mock when DB present)
- `migrate.php` and `public/migrate.php` — apply numbered SQL migrations; load `.env` if present
- `includes/config.php` and `public/includes/config.php` — base URL helpers (prefer `APP_URL` env, fallback to MAMP/prod detection)
- `CPANEL_MIGRATION.md` — full production deployment guide
- `api/upload-kb-image.php` — KB image upload endpoint (admin-only, CSRF-protected)
- `knowledge-base.php` — KB listing; gold category badges
- `admin-kb-edit.php` — KB editor (Quill) with HTML view toggle, image upload
- (Removed) `AI_RULES.md` — folded rules into this document
- (Removed) `replit.md` — historical architecture notes folded in

## 🚦 cPanel Migration Notes
- Remove Replit artifacts before production: `.replit`, `replit.nix`, `replit.md`
- Fix absolute redirects to relative in:
  - `api/save-shift-report.php`
  - `logout.php`
  - `signup.php`
- Ensure `includes/config.php` uses environment-based or auto-detected base URL; avoid MAMP-specific logic in prod
- Ensure writable dirs exist with 755 perms: `storage/`, `storage/notifications/`, `attached_assets/`, `attached_assets/kb_images/`
- PHP limits in `.htaccess`: `upload_max_filesize=10M`, `post_max_size=12M`, `max_execution_time=300`, `max_input_vars=3000`

### Highlights from CPANEL_MIGRATION.md
- Deployment steps:
  1) Pull via cPanel Git or SSH clone to `public_html/portal/`
  2) Import DB schema (prod-only migrations: 001–004)
  3) Update `includes/db.php` with cPanel credentials (host=localhost)
  4) Ensure upload dirs exist (e.g., `assets/kb/`, `storage/notifications/`)
- Permissions/ownership:
  - Files 644, directories 755; chown to the cPanel user
  - Make storage dirs writable where needed
- .htaccess:
  - Security headers, PHP limits (upload/post size, exec time), disable indexes
  - Enable gzip (Deflate) for common types
- Email:
  - PHP `mail()` works out of the box; SMTP optional if preferred
- Post-deploy checklist:
  - Login, dashboard, admin panels
  - Forms (save+email), KB articles (CRUD), reports
  - No PHP errors in logs; assets load without 404s
- Troubleshooting:
  - Reset permissions after updates; verify `assets/css/output.css` exists
  - Provide `test-db.php` for quick DB connectivity checks if needed

## 🧭 Commands (Local)
```
# Run migrations
/Applications/MAMP/bin/php/php8.2.26/bin/php migrate.php

# DB test
/Applications/MAMP/bin/php/php8.2.26/bin/php -r "require_once 'includes/db.php'; echo 'Connected!';"

# Shift report count
/Applications/MAMP/bin/php/php8.2.26/bin/php -r "require_once 'includes/shift-report-manager.php'; $m=ShiftReportManager::getInstance(); echo 'Reports: '.count($m->getShiftReports());"
```

## ✅ Recent Work (Sep 26–27, 2025)
### Shift Report Email System - Complete Fix
- **Problem**: Shift report emails weren't sending due to multiple issues
- **Root Causes**:
  - Complex email template generation was failing
  - Data structure mismatches between form data and email template
  - Variable name conflicts (shipment notes vs additional notes)
  - Email settings not configured for all locations
- **Solutions Applied**:
  - Simplified email template to be self-contained (no complex includes)
  - Added comprehensive debug logging to identify data structure issues
  - Fixed data processing to handle both arrays and single objects
  - Used unique variable names (`$shipment_notes` vs `$additional_notes`)
  - Added error handling with fallback templates
- **Files Modified**:
  - `public/includes/shift-report-email-manager.php` - Complete rewrite with working template
  - `public/api/save-shift-report.php` - Enhanced error handling and debug logging
  - `public/forms/shift-reports.php` - AJAX form submission with better error handling
- **Key Lessons**:
  - Always log actual data structure before assuming format
  - Use simple, self-contained templates instead of complex includes
  - Handle both array and single object data structures
  - Use descriptive variable names to avoid conflicts
  - Add fallback templates for when main templates fail

### Previous Work (Sep 23, 2025)
- KB Admin: show creator/updater (JOIN with `users`); migration 011
- KB Editor: removed email template button; added Quill alignment + indent
- KB Editor: added bottom image upload; created `api/upload-kb-image.php`
- KB Editor: added HTML view toggle (View HTML / Visual Editor)
- KB Article Viewer: print controls per-article; sections toggle per-article
- KB Listing: category badges standardized to gold (CSS class)
- Fixed malformed SVG path in header; robust observers for TOC
- Fixed `ShiftReportManager` DB detection (MySQL `SHOW TABLES LIKE`)
- Replaced `getPDO()` usage with global `$pdo` from `includes/db.php`
- Added demo data migrations (005–006); validated reports UI
- Verified MAMP PDO socket connection and migrations

### Cleanup (Sep 23, 2025)
- Removed Replit artifacts and unused files:
  - `AI_RULES.md`, `replit.md`, `replit.nix`
  - `NOTIFICATION_SYSTEM_LOG.md`, `USER_MANAGEMENT_SYSTEM_LOG.md`
  - `cookies.txt`, `test.html`
  - `assets/css/tailwind.css` (unused; using `assets/css/output.css`)
  - Dev/sample assets under `attached_assets/` (kept `attached_assets/announcements/`)
  - `storage/notifications/sample-notifications.php`
  - `shift-reports.txt` (mock fallback; DB in use)
 - Recreated `api/upload-kb-image.php` after deletion

### Consolidated Rules (from AI_RULES.md)
- Pure PHP 8+, Tailwind + Alpine via CDN, PDO only
- Sessions for auth; password_hash/password_verify
- Keep code cPanel-compatible; no external SaaS (Firebase/Supabase/ReplitAuth)
- Explain created/modified files in changes

## ⚠️ Open Follow-ups
- Recreate `run-php.sh` helper if desired
- Replace absolute redirects with relative paths (see files above)
- Consider cPanel-safe `redirectTo()` helper in `includes/config.php`
- Confirm production `.htaccess` includes PHP limits and security headers
- Ensure `kb_articles` table on prod has fields: `allow_print`, `enable_sections`, `created_by`, `updated_by`
- Validate upload perms on `attached_assets/kb_images/` in prod

## 🔄 Workflow
Dev (MAMP) → Push to GitHub → SSH and `cd ~/repos/portal && git pull` → Run schema-only or full migrations → Real users create data via forms/announcements/KB.

## ✅ Live Deployment Snapshot (cPanel)
- Branch deployed: `cpanel-test-deploy`
- Repo on server: `~/repos/portal` → web root served from `~/public_html/portal/`
- Deployment: pull then rsync public files
```
cd ~/repos/portal && git checkout cpanel-test-deploy && git pull
rsync -av --delete ~/repos/portal/public/ ~/public_html/portal/
```
- Nav fix (Sep 27, 2025): Converted header links to absolute `/portal/...` in `public/includes/header.php` and `includes/header.php` to prevent subdirectory routing issues (e.g., `/portal/forms/forms.php`).

## 🔐 User Management (Status)
- URL: `/portal/user-management.php` — requires login + `admin` role
- Features:
  - Users: role change, active/inactive toggle, delete (soft), reset password (CSRF‑protected)
  - Invitations: send (`api/invitations/send-invitation.php`), list, revoke, copy secure link
- Temporary debug: `/portal/admin_debug.log` records admin actions (invite send/list/revoke; user status/delete/restore/reset). Remove before real invites.
- Security: session auth + CSRF tokens; actions restricted to admin

## ✉️ Email Status
- Shift report emails: confirmed working in production
- Invitations: implemented via invitations API; uses existing mail/PHPMailer setup. Ensure SMTP or PHP mail is configured per `CPANEL_MIGRATION.md`.

## 🛡 Ops: Verify DB in app matches phpMyAdmin
Run on server to print the live DB/user/host/version the app uses:
```
php -r "require '/home/portaljjosephsal/public_html/portal/includes/db.php'; \
echo 'DB=' . $pdo->query('SELECT DATABASE()')->fetchColumn() . PHP_EOL; \
echo 'USER=' . $pdo->query('SELECT CURRENT_USER()')->fetchColumn() . PHP_EOL; \
echo 'HOST=' . $pdo->query('SELECT @@hostname')->fetchColumn() . PHP_EOL; \
echo 'VER=' . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;"
```
Schema signature (compare with phpMyAdmin on the selected DB):
```
php -r "require '/home/portaljjosephsal/public_html/portal/includes/db.php'; \
echo 'SIG=' . $pdo->query(\"SELECT MD5(GROUP_CONCAT(CONCAT(TABLE_NAME, ':', TABLE_ROWS) ORDER BY TABLE_NAME SEPARATOR '|')) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()\")->fetchColumn() . PHP_EOL;"
```

## 🔎 Security Sanity Checks (read‑only)
```
# Scan for suspicious PHP usage
grep -R -nE "eval\(|base64_decode\(|gzinflate\(|assert\(|system\(|exec\(|shell_exec\(|popen\(" ~/public_html/portal | head -n 50

# Ensure no PHP files are present in upload dirs
find ~/public_html/portal/attached_assets -type f -iname '*.php' -ls
find ~/public_html/portal/attached_assets/kb_images -type f -iname '*.php' -ls

# Recent file changes (last 2 hours)
find ~/public_html/portal -type f -mmin -120 -ls | head -n 50

# World-writable files/dirs (should be none)
find ~/public_html/portal -perm -o+w -ls | head -n 50
```

## 🔔 Notification System Snapshot (from NOTIFICATION_SYSTEM_LOG.md)
- Tables: `notifications`, `user_notifications` (indexes for performance)
- Endpoints: `api/notifications.php`, `api/notifications/mark-read.php`, `api/notifications/mark-all-read.php`
- Manager: `includes/notification-manager.php` with role/user/all targeting
- UI: Header bell (Alpine component) with unread counts and actions
- Security: auth + CSRF, internal link validation, proper escaping
- Integrations: announcements (live), future hooks for shift reports, forms

## 👤 User Management Snapshot (from USER_MANAGEMENT_SYSTEM_LOG.md)
- Tables:
  - `users` (roles: admin/manager/support/staff/viewer; status: active/inactive/deleted; soft delete via `deleted_at`)
  - `user_invitations` (token, role, invited_by, status, expiry, indexes)
  - `user_audit_log` (action, old/new JSON values, performed_by, IP, UA)
- API endpoints (admin-only, CSRF-protected):
  - `api/users/delete-user.php` (soft delete)
  - `api/users/restore-user.php` (restore)
  - `api/users/update-status.php` (active/inactive)
  - Invitations (send/resend/cancel) planned under `api/invitations/`
- Implementation phases:
  - Phase 1 (Foundation) ✅ schema, mock migration, admin UI, cPanel guide
  - Phase 2 (CRUD) ✅ soft delete/restore, status, audit, endpoints, UI
  - Phase 3 (Invitations) 🔜 HTML email invitations + signup flow (token)
  - Phase 4 (Roles Manager) 🔜 roles CRUD and permission framework
- Features implemented:
  - Soft deletion with restore, status toggle, admin-only actions, audit logging
  - Interactive admin interface with real-time feedback
- Production notes:
  - Pure PHP + PDO, session auth; email templates in `templates/emails/`
  - Compatible with shared hosting; env-based email configuration

---
Note: This file is the assistant's working memory. Keep this updated as we modify code, environment, or deployment plans.