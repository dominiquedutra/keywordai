# Upgrade Google Ads API + Deploy to Production

## Completed

- [x] Upgrade `googleads/google-ads-php` from ^26.1 to ^32.0 (installed v32.2.0)
- [x] Replace all V19 namespace references with V20 across 12 PHP files (59 occurrences)
- [x] Verify REST transport config (documented in INI example, set on production)
- [x] Verify locally — composer update succeeded, V20 classes confirmed in vendor
- [x] Commit and push to GitHub (commit `487f55c`)
- [x] Deploy to production (192.34.63.220)
  - [x] Update git remote from GitLab to GitHub
  - [x] Switch from `master` to `main` branch
  - [x] Restore `config/google_ads_php.ini` from old branch
  - [x] Set `transport = "rest"` in INI (no gRPC on production PHP 8.3)
  - [x] `composer update` (resolved Symfony 8.x → 7.x for PHP 8.3 compat)
  - [x] `npm install && npm run build`
  - [x] Run migrations (api_tokens table + remove AI settings)
  - [x] Cache config, routes, views
  - [x] Flush 972 failed jobs
  - [x] Restart supervisor workers (4 queues: default, bulk, notifications, critical)
  - [x] Update crontab to use `schedule:run` instead of direct artisan commands
- [x] Verify: `php artisan googleads:sync-entities` ran successfully on production
- [x] Verify: No new failed jobs, no UNSUPPORTED_VERSION errors

---

# Stats Sync Button + AI Settings in UI

## Completed

- [x] Create `keywordai:sync-all-active-stats` artisan command (chunks active terms, dispatches SyncSearchTermStatsJob)
- [x] Register command in QueueCommandsController with dry-run checkbox option
- [x] Add checkbox input type support to queue_commands Blade view
- [x] Add `encrypted` type to Setting model (AES-256-CBC via Laravel Crypt)
- [x] Create migration seeding 7 AI config settings (default_model, 3x api_key encrypted, 3x model_name)
- [x] Update global settings UI: API key password inputs + model name fields per provider with recommendations
- [x] Update GlobalSettingsController: validate new fields, encrypted storage for API keys, skip-if-empty logic
- [x] Fix AiAnalysisService: replace all broken `config('app.ai_*')` → `setting()` first, `config('ai.models.*')` fallback
- [x] Fix HealthApiController + AiAnalysisApiController: same DB-first pattern
- [x] Verify: migration ran, encryption round-trip works, dry-run command finds 478 terms

## Still Needed (Production)

- [x] Deploy this commit to production (done 2026-02-26)
- [x] Run `php artisan migrate` on production (done 2026-02-26)
- [ ] Set API keys via Settings > Global UI
- [ ] Run `php artisan googleads:full-sync-search-terms` on production to backfill Feb 20-25 gap

## Notes

- Production PHP is 8.3.6 while local is 8.5. The `composer.lock` needed `composer update` on production to downgrade Symfony 8.x → 7.x.
- The old GitLab repo is deleted. Production now points to `https://github.com/dominiquedutra/keywordai.git`.
- 2735 old `UNSUPPORTED_VERSION` errors exist in laravel.log from before the upgrade — consider truncating the log.

---

# Deploy OpenRouter to Production + Docker Fixes

## Completed (2026-02-26)

- [x] Reset production git to match `origin/main` (`ae7399b` — Replace Perplexity with OpenRouter)
  - Production was stuck on old pre-leak commits (`1cdb939`), diverged from origin
  - `git fetch origin && git reset --hard origin/main`
- [x] Rebuild Docker containers (`docker compose -f docker-compose.prod.yml build --no-cache && up -d`)
- [x] Run missing migrations (`add_ai_config_settings` + `replace_perplexity_with_openrouter`)
  - Had to `docker cp` migration files into container (db-data volume overlay issue)
- [x] Run `SettingsSeeder` to populate OpenRouter instruction settings
- [x] Verify: `setting('ai_openrouter_model')` returns `google/gemini-2.0-flash-001`
- [x] Fix nginx "File not found" — `$realpath_root` fails when nginx has no access to app's `public/`
  - Changed nginx config to proxy all requests directly to PHP-FPM with hardcoded `SCRIPT_FILENAME`
- [x] Fix `update.sh`, `backup.sh`, `deploy.sh` — add `-f docker-compose.prod.yml` flag
- [x] Fix db-data volume migration sync — entrypoint now copies from `/tmp/migrations-from-image`
- [x] Create `tasks/production-setup.md` with full server documentation
- [x] Verify: app responding on `http://localhost:8080`, all 13 settings present

## Discovered Issues (Fixed)

1. **db-data volume hides new migrations**: Named volume overlays `/var/www/html/database`, so image rebuilds don't update migration files. Fixed by stashing migrations in `/tmp/migrations-from-image` during build and copying in entrypoint.
2. **Nginx can't serve PHP**: `$realpath_root` / `try_files` fail because nginx container doesn't have the app's `public/` directory. Fixed by proxying everything to PHP-FPM directly.
3. **Shell scripts missing compose file flag**: `update.sh`, `backup.sh`, `deploy.sh` used bare `docker compose` which defaults to `docker-compose.yml` (doesn't exist). Fixed to use `-f docker-compose.prod.yml`.
