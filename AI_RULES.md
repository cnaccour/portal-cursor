# AI Coding Rules (This Project)

## Tech Stack
- Frontend: TailwindCSS (via CDN) + Alpine.js (via CDN)
- Backend: PHP 8+ (no Node, no Python, no external frameworks)
- Database: MySQL (PDO)
- Auth: PHP Sessions (`$_SESSION`)
- Hosting: cPanel/WHM

## File Structure
/
  .replit
  AI_RULES.md
  includes/
    db.php
    auth.php
    header.php
    footer.php
  api/
  forms/
  index.php
  login.php
  logout.php
  dashboard.php
  assets/

## Rules
- Do NOT use ReplitAuth, Firebase, Supabase, or other SaaS.
- Use only Tailwind & Alpine via CDN links.
- Use PDO (no mysqli_* functions).
- Use `password_hash()` / `password_verify()` for passwords.
- Start sessions at top of protected PHP pages.
- Keep everything compatible with cPanel hosting.
- Always explain which files are created/modified.