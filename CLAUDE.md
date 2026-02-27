# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## CRITICAL: Secret Safety

This repo previously had a key leak on a public GitHub repo, forcing a full repo deletion. **Treat secret handling as the #1 priority.**

- **NEVER stage or commit**: `.env`, `config/google_ads_php.ini`, or any file containing real API keys/tokens/passwords.
- **Before any `git add`**, explicitly review what's being staged. Avoid `git add .` or `git add -A`.
- **If a secret is accidentally committed**, rotate the credential IMMEDIATELY — removing the commit is not enough.
- **Always use `.example` files** (`.env.example`, `.ini.example`) with placeholders, never real values.
- See `tasks/lessons.md` for the full incident record.

## Project Overview

KeywordAI is an AI-powered Google Ads Search Term Management Platform. It syncs search terms from Google Ads, lets users analyze them with AI (Gemini, OpenAI, OpenRouter), and manage negative keywords — all self-hosted.

## Development Commands

```bash
# Start all dev services (server, queue worker, log tail, vite) concurrently
composer dev

# Start with SSR
composer dev:ssr

# Individual services
php artisan serve              # Laravel dev server
php artisan queue:listen       # Queue worker
npm run dev                    # Vite dev server

# Frontend
npm run build                  # Build assets
npm run lint                   # ESLint (auto-fix)
npm run format                 # Prettier
npm run format:check           # Prettier check

# Database
php artisan migrate            # Run migrations
php artisan db:seed            # Run seeders (AdminUserSeeder, SettingsSeeder)

# Docker — Development (local Composer required)
cp .env.dev.example .env                              # First time only
composer install --ignore-platform-reqs               # First time (on host, not in Docker)
docker compose -f docker-compose.dev.yml up --build   # First time
docker compose -f docker-compose.dev.yml up           # Daily dev
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan tinker
docker compose -f docker-compose.dev.yml down         # Stop

# Note: Composer runs on host due to macOS Docker volume corruption issues
```

## Production Deployment

Production runs on a **bare-metal DigitalOcean VPS** (NOT Docker). See `memory/production.md` for full details.

```bash
ssh deployer@192.34.63.220
cd /var/www/keywordai
git pull origin main
composer install --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo supervisorctl restart keywordai:*
```

## Architecture

### Navigation: Universal Blade Top Navbar

The app uses a **single server-rendered Blade top navbar** (`resources/views/components/navbar.blade.php`) that works identically on both Inertia and Blade pages. Both template roots (`app.blade.php` for Inertia, `layouts/app.blade.php` for Blade) include it via `@include('components.navbar')`. Menu items are organized into grouped dropdowns (Gestão, Monitoramento, Admin) with mobile hamburger support. The navbar only renders for authenticated users (`@auth`).

### Dual Rendering: Blade + Inertia/Vue

The app has a **hybrid frontend**. Newer pages (Dashboard, Welcome, auth, settings) use **Inertia.js + Vue 3** with TypeScript (`resources/js/pages/`). Older pages (search terms, negative keywords, AI analysis, activity logs) use **Blade templates** (`resources/views/`) with vanilla JS. Both coexist — check the route definition to know which renderer a page uses (`Inertia::render()` vs `view()`).

### Background Processing Pipeline

Most Google Ads operations happen through queued jobs, not synchronous requests:

- **`SyncSearchTermsForDateJob`** — Syncs search terms for a specific date. Scheduled every 10 minutes for today.
- **`SyncSearchTermStatsJob`** — Updates stats for individual search terms. Has a `handleSynchronous()` method for real-time refresh via AJAX.
- **`SyncAdsEntitiesJob`** — Syncs campaigns and ad groups. Runs hourly via scheduler.
- **`AddNegativeKeywordJob`** / **`AddKeywordToAdGroupJob`** — Mutates Google Ads via API.
- **`SendNewSearchTermNotificationJob`** — Google Chat notifications for new terms.
- **`BatchSyncSearchTermStatsJob`** — Batch stats sync for multiple terms.

Jobs handle Google Ads quota exceeded errors by releasing back to queue with 60s delay. The `GoogleAdsQuotaService` enforces rate limits.

### Google Ads Integration

- Credentials configured in `config/google_ads_php.ini` (never committed — see `.ini.example`)
- Client configured via `config/googleads.php` and service provider
- Uses `loginCustomerId` (MCC) for auth, `clientCustomerId` for data queries
- Key env vars: `GOOGLE_ADS_DEFAULT_NEGATIVE_LIST_ID`, `GOOGLE_ADS_ABSOLUTE_START_DATE`

### AI Analysis

Three AI providers configured in `config/ai.php` with API keys in `.env`:
- **Gemini** (default): `gemini-2.5-flash`, `gemini-2.5-flash-lite`, `gemini-2.5-pro`
- **OpenAI**: `gpt-4.1-nano`, `gpt-4.1-mini`, `gpt-4.1`, `gpt-4o`
- **OpenRouter**: Any model via OpenRouter API

Key features:
- `AiAnalysisService` centralizes AI term analysis logic
- Supports analysis by date or by top cost terms (up to 1000 terms)
- Two-step UI flow: **preview** (instant prompt/size display) → **analyze** (AI call)
- Preview endpoint (`POST /ai-analysis/preview`) builds prompt without calling AI
- AI instructions (global + per-model) stored in `settings` DB table, editable at `/settings/global`
- Model selection via curated dropdown lists in settings page (`resources/js/pages/settings/Global.vue`)
- Configurable API timeout (10–300 seconds)

### Authentication & Security

- Web auth via Laravel Breeze (session-based)
- API auth via custom `ApiTokenAuth` middleware using `X-API-Token` header
- IP whitelist middleware on all web routes (controlled by `FORCE_IP_WHITELIST` env var)
- Default admin: `admin@keywordai.com` / `password` (from `AdminUserSeeder`)

### Key Models

- **SearchTerm** — Core entity with impressions, clicks, cost (stored in micros), status
- **SearchTermSyncDate** — Tracks sync status per date (pending/processing/completed/failed)
- **NegativeKeyword** — Tracks negated keywords with reason and match type
- **ActivityLog** — Audit trail for keyword additions/negations
- **Setting** — Key-value store for global config (accessed via `setting()` helper from `app/Support/helpers.php`)
- **ApiToken** — Token-based API authentication

### Custom Artisan Commands

Located in `app/Console/Commands/`. Notable:
- `keywordai:analyze-search-terms` — AI analysis for terms by date
- `keywordai:analyze-top-search-terms` — AI analysis for top-cost terms
- `keywordai:full-sync` — Orchestrates historical sync day-by-day with retry/resume
- `keywordai:sync-entities` — Syncs campaigns and ad groups

### Route Structure

- **Web routes** (`routes/web.php`) — Blade and Inertia pages, protected by `auth` middleware
- **API routes** (`routes/api.php`) — REST API protected by `api_token` middleware (30+ endpoints)
- **Settings routes** (`routes/settings.php`) — User profile settings (Inertia)
- Public: `/`, `/api/docs`, `/api/health`, `/api/info`, `/docs/sistema`

## Tech Stack

- **Backend**: PHP 8.2, Laravel 12, SQLite (local dev), PostgreSQL (production)
- **Frontend**: Vue 3 + TypeScript (Inertia pages), Blade + Tailwind CSS (legacy pages)
- **UI Components**: shadcn/vue (reka-ui based) in `resources/js/components/ui/`
- **Queue**: Database driver (local dev), Beanstalkd (production)
- **Build**: Vite with `@` alias → `resources/js/`
- **Docker**: Dev only (`docker-compose.dev.yml`) with Vite HMR + Mailpit. Production is bare-metal.

## Important Patterns

- Cost values from Google Ads are stored in **micros** (multiply by 1,000,000). Display code divides back.
- The `setting('key')` global helper reads from the `settings` table (defined in `app/Support/helpers.php`, loaded in `bootstrap/app.php`).
- `SearchTermObserver` in `app/Observers/` watches for model events.
- ESLint ignores `resources/js/components/ui/*` (auto-generated shadcn components).
- No test suite exists yet — validation is manual via artisan commands and browser testing.
- AI model names change frequently (preview versions get removed). Always use stable model IDs (e.g., `gemini-2.5-flash` not `gemini-2.5-flash-preview-*`).
