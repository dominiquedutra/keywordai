# Lessons Learned

## CRITICAL: Key Leak on Public GitHub Repo

**Date:** 2025 (prior to current work)
**Impact:** Had to delete the entire GitHub repo due to leaked credentials.

### What happened
- Sensitive credentials (API keys, tokens) were committed and pushed to a public GitHub repository.
- Once pushed to a public repo, secrets are exposed even if deleted later (git history, forks, caches).
- The only safe remediation was deleting the entire repo.

### Rules to prevent recurrence
1. **NEVER commit secrets** — API keys, tokens, passwords, `.ini` files with credentials, `.env` files.
2. **Always verify `.gitignore`** before first commit — ensure `config/google_ads_php.ini`, `.env`, and any credential files are listed.
3. **Before any `git add`**, review staged files explicitly — avoid `git add .` or `git add -A` which can sweep in secret files.
4. **If a secret is accidentally committed**, rotate the key IMMEDIATELY — deleting the commit is not enough.
5. **Use `.env.example` and `.ini.example`** with placeholder values, never real credentials.
6. **Before making a repo public**, audit the entire git history for secrets with tools like `git log -p | grep -i "key\|secret\|token\|password"`.
7. **Never hardcode webhook URLs or API endpoints with embedded keys** — use env vars instead.

### Specific leaked secret
- **File:** `app/Services/GoogleChatNotificationService.php:18`
- **What:** Google Chat webhook URL with embedded API key was hardcoded in source code.
- **Fix:** Moved to `GOOGLE_CHAT_WEBHOOK_URL` env var.
- **Rotation:** The exposed API key and webhook token MUST be rotated in Google Cloud Console.

## Docker Named Volumes Hide Image Updates

**Date:** 2026-02-26
**Impact:** New migration files were invisible after Docker rebuild, causing deployment failure.

### What happened
- `docker-compose.prod.yml` maps `db-data` named volume to `/var/www/html/database`
- Named volumes persist across container rebuilds — they overlay the image's directory
- When the Docker image was rebuilt with new migration files, the volume's old contents took precedence
- `php artisan migrate` showed "Nothing to migrate" because it couldn't see the new files

### Fix applied
1. `Dockerfile.prod`: Stash migrations to `/tmp/migrations-from-image` before volume mount
2. `docker/entrypoint.sh`: Copy stashed migrations to the volume on startup (`cp -n` = don't overwrite)

### Rules
1. **Named volumes persist across rebuilds** — any directory mounted as a named volume won't get new files from image updates.
2. If code files need to live in a volume-mounted path, use an entrypoint sync pattern.
3. For urgent fixes, `docker cp` files directly into running containers.

## Nginx $realpath_root Requires Local Filesystem Access

**Date:** 2026-02-26
**Impact:** All HTTP requests returned "File not found" on production.

### What happened
- Nginx config used `$realpath_root` for `SCRIPT_FILENAME` and `try_files` for routing
- The nginx container only has `storage/` via volume mount, not the app's `public/` directory
- `$realpath_root` resolves paths on nginx's local filesystem — fails if path doesn't exist
- PHP-FPM received empty/invalid `SCRIPT_FILENAME`

### Fix applied
- Removed `root`, `try_files` from nginx config
- All requests proxy directly to PHP-FPM with hardcoded `SCRIPT_FILENAME /var/www/html/public/index.php`
- PHP-FPM resolves the path on its own filesystem (where the app code lives)

### Rules
1. When nginx and PHP-FPM run in separate containers, nginx needs either:
   - A shared volume with the app's public files, OR
   - Direct proxy config without filesystem-dependent directives
2. `$realpath_root` and `try_files` require the paths to exist on nginx's local filesystem.
3. After editing bind-mounted configs, **restart the container** (not just `nginx -s reload`).

## Production vs Staging vs Local: Know Your Environment

**Date:** 2026-02-26
**Impact:** Clarity on the three deployment environments and their differences.

### Environment topology

| Environment | Location | Database | Queue | PHP | Infra |
|-------------|----------|----------|-------|-----|-------|
| **Local** | macOS laptop | SQLite | `database` driver | 8.2 | Optional Docker dev compose |
| **Staging** | Home server | Varies | `database` driver | 8.2 | Docker |
| **Production** | DigitalOcean VPS | **PostgreSQL** | **Beanstalkd** | **8.3.6** | **Bare metal** (no Docker) |

### Rules
1. **Production is NOT Docker** — it runs Nginx + PHP-FPM + PostgreSQL + Beanstalkd + Supervisor directly on Ubuntu 24.04.
2. **Database differs per environment** — local uses SQLite, production uses PostgreSQL. Test migrations against both if possible.
3. **Queue driver differs** — local uses `database`, production uses `beanstalkd`. Jobs that work locally may behave differently in production.
4. **PHP version differs** — local is 8.2, production is 8.3.6. Be aware of deprecation warnings or behavior changes.
5. **Always deploy manually** — `ssh deployer@192.34.63.220`, git pull, build, migrate, restart supervisor. No CI/CD pipeline yet.
6. **Check the scheduler** — production currently has NO cron for `php artisan schedule:run`. Scheduled tasks won't run until this is fixed.
