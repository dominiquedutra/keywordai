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

- [ ] Run `php artisan googleads:full-sync-search-terms` on production to backfill Feb 20-25 gap
- [ ] Deploy this commit to production
- [ ] Run `php artisan migrate` on production
- [ ] Set API keys via Settings > Global UI

## Notes

- Production PHP is 8.3.6 while local is 8.5. The `composer.lock` needed `composer update` on production to downgrade Symfony 8.x → 7.x.
- The old GitLab repo is deleted. Production now points to `https://github.com/dominiquedutra/keywordai.git`.
- 2735 old `UNSUPPORTED_VERSION` errors exist in laravel.log from before the upgrade — consider truncating the log.
