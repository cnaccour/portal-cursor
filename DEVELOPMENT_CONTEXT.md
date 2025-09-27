# J. Joseph Salon Team Portal - Development Context
**Last Updated:** September 27, 2025  
**AI Assistant Memory File** — Keep this updated as we work together

## 🚀 Quick Start in Cursor
- Open this file first: `DEVELOPMENT_CONTEXT.md` (project overview + status)
- Then open, in order:
  1) `includes/db.php` (bootstraps PDO; defers to `public/includes/db.php` in prod)
  2) `includes/shift-report-manager.php` (DB mode; MySQL usage)
  3) `migrate.php` (numbered SQL migrations runner)
  4) `includes/config.php` and `public/includes/config.php` (APP_URL-aware base URL)
  5) `CPANEL_MIGRATION.md` (deployment specifics)
- Local commands (MAMP): see “Commands (Local)” below

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

## 📁 Migrations (Current)
- Use `migrate.php` (root or `public/`) to apply all `database/migrations/*.sql` with tracking in a `migrations` table.
- Export `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` before running on non-MAMP environments.
- Production safety: do NOT run dev/demo seeds (e.g., `099_dev_mock_users.sql`). If present, move it out before running migrations on prod.
- Alternative (schema-only on cPanel): run `scripts/db_migrate.sh` to apply `db/schema.sql`.

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

## 🚦 cPanel Migration Notes (Aligned to Code)
- Map site to `public/` (set DocumentRoot or symlink `~/public_html -> public`).
- DB config comes from `public/includes/db.php` (no `.env` needed for DB in prod). Use secure cPanel credentials.
- Run DB migrations:
  - Recommended schema-only: `bash scripts/db_migrate.sh` (applies `db/schema.sql`).
  - Or full numbered: export `DB_*` and run `/usr/local/bin/php migrate.php` (avoid dev-only files like `099_dev_mock_users.sql`).
- Ensure writable dirs (755): `storage/`, `storage/notifications/`, `attached_assets/`, `attached_assets/kb_images/`.
- `.htaccess`: security headers, PHP limits (`upload_max_filesize=10M`, `post_max_size=12M`, `max_execution_time=300`, `max_input_vars=3000`), disable indexes, enable gzip.

### Highlights from CPANEL_MIGRATION.md
- Deployment steps:
  1) Pull via cPanel Git or SSH clone to `~/repos/portal`
  2) Map/symlink `public/` to `~/public_html`
  3) Run schema-only or full migrations as above
  4) Ensure upload dirs exist (e.g., `attached_assets/kb_images/`, `storage/notifications/`)
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

## ✅ Recent Work (Sep 23, 2025)
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
Note: This file is the assistant’s working memory. Keep it updated as we modify code, environment, or deployment plans.


