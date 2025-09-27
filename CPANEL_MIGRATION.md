# cPanel Deploy Runbook (Aligned with Current Code)

Branch: `main`

## Overview
This runbook deploys the PHP app to cPanel with `public/` as the web root, env-driven config for URLs, production DB configured in `public/includes/db.php`, and two migration options (schema-only or numbered SQL via `migrate.php`).

## Prereqs in cPanel
- Enable SSH for the cPanel user
- Create MySQL database and user; grant ALL PRIVILEGES
- Create an email account or SMTP app password for the domain

## Get the code on server
### Option A) cPanel Git UI
- Create repo in `~/repos/portal`
- Deploy or symlink to DocumentRoot (see Mapping below)

### Option B) SSH
```bash
mkdir -p ~/repos && cd ~/repos
git clone <repo-url> portal
cd portal && git pull
```

## Map repo to site
- Web root: set DocumentRoot to `~/public_html` and place app `public/` there OR symlink:
```bash
ln -s ~/repos/portal/public ~/public_html
```

## Environment on server
```bash
cd ~/repos/portal
# Optional: create `.env` for app URL and SMTP only
cat > .env << 'ENV'
APP_URL=https://portal.jjosephsalon.com
FORCE_HTTPS=true
SMTP_HOST=
SMTP_PORT=465
SMTP_SECURE=ssl
SMTP_USER=
SMTP_PASS=
FROM_EMAIL=noreply@jjosephsalon.com
ENV
```

## Install deps/build (choose what exists)
- PHP: `composer install --no-dev --optimize-autoloader` (if composer.json exists)
- Node SPA: `npm ci && npm run build` then copy/symlink dist to `~/public_html`

## Database migration
```bash
export DB_HOST=localhost
export DB_NAME=<db>
export DB_USER=<user>
export DB_PASS=<pass>
# Option A (recommended, schema-only):
bash scripts/db_migrate.sh

# Option B (full numbered migrations):
/usr/local/bin/php migrate.php
```
If framework migrations exist, run `scripts/db_framework_migrate.sh`.

Note:
- `database/migrations/099_dev_mock_users.sql` is development-only seed data. Do not run it on cPanel/production.
- You may use either schema-only (`db/schema.sql`) or full numbered migrations. Ensure prod excludes any dev/demo seed files.

## Permissions
```bash
find ~/public_html -type d -exec chmod 755 {} \;
find ~/public_html -type f -exec chmod 644 {} \;
```

## Email test (SMTP)
- Visit: `/test_email.php` (uses PHPMailer + env)

## Rollback
```bash
cd ~/repos/portal
git checkout <previous-tag-or-commit>
git reset --hard && git pull
# restore prior .env if needed
```

## Sanity checklist
- [ ] Homepage loads over HTTPS
- [ ] Deep link works (rewrites ok)
- [ ] Images/CSS/JS 200 OK (no absolute path leaks)
- [ ] Email test succeeds
- [ ] DB tables present; no demo rows
- [ ] Console/network free of 404s to old domains

## CHANGELOG (paths and deploy files)
- `.htaccess`: HTTPS + rewrites, absolute API fetches
- `includes/header.php`: notifications fetch now `/api/...`
- `includes/config.php` and `public/includes/config.php`: APP_URL-aware base URL
- `public/includes/db.php`: production DB settings + DSN fallbacks
- `db/schema.sql`: schema-only
- `scripts/db_migrate.sh`: MySQL apply
- `lib/Email.php`, `public/test_email.php`: SMTP test
- `.gitignore`: ignore .env files except example