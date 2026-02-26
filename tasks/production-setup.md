# Production Server Setup

## Server Access

- **SSH**: `ssh homeserver` (alias for 10.15.30.2)
- **OS**: Ubuntu 22.04
- **Location**: Home server
- **App URL**: Via Cloudflare Tunnel (connects to `127.0.0.1:8080`)

## Docker Architecture

Runs via `docker compose -f docker-compose.prod.yml`. Five containers:

| Container | Image | Purpose |
|-----------|-------|---------|
| `keywordai-app` | Custom (Dockerfile.prod) | PHP 8.2-FPM, main app |
| `keywordai-nginx` | nginx:alpine | Reverse proxy, port 8080 |
| `keywordai-queue` | Custom (Dockerfile.prod) | Queue worker (`queue:work`) |
| `keywordai-scheduler` | Custom (Dockerfile.prod) | Cron scheduler (`schedule:run` loop) |
| `keywordai-redis` | redis:7-alpine | Running but unused (queue/cache use database driver) |

### Docker Volumes

| Volume | Mounts To | Contains |
|--------|-----------|----------|
| `keywordai_app-data` | `/var/www/html/storage` | Logs, cache, sessions, uploads |
| `keywordai_db-data` | `/var/www/html/database` | SQLite database + migrations |
| `keywordai_redis-data` | Redis `/data` | Redis persistence (unused) |

### Bind Mounts

- `./config:/var/www/html/config:ro` — Google Ads INI + Laravel config (read-only)
- `./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro` — Nginx config

### Important: db-data Volume Gotcha

The `db-data` volume overlays `/var/www/html/database` in the container. This means **new migration files from Docker image rebuilds are hidden** by the persistent volume. The entrypoint.sh works around this by copying migrations from `/tmp/migrations-from-image` (stashed during build) into the volume at startup.

If migrations are missing after a rebuild, manually copy them:
```bash
docker cp database/migrations/XXXX_migration.php keywordai-app:/var/www/html/database/migrations/
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Deployment

### Quick Update (Recommended)

```bash
ssh homeserver
cd ~/keywordai
./update.sh
```

The `update.sh` script:
1. Runs `backup.sh` (backs up DB, storage, .env, google_ads_php.ini)
2. `git pull origin main`
3. `docker compose -f docker-compose.prod.yml down`
4. `docker compose -f docker-compose.prod.yml build --no-cache`
5. `docker compose -f docker-compose.prod.yml up -d`
6. Runs `php artisan migrate --force`
7. Clears caches

### Manual Deployment

```bash
ssh homeserver
cd ~/keywordai

# Backup first
./backup.sh

# Pull code
git pull origin main

# Rebuild and restart
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

# Run migrations (entrypoint does this too, but just in case)
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Verify
docker compose -f docker-compose.prod.yml ps
curl -s http://localhost:8080/api/info | python3 -m json.tool
```

### Entrypoint Auto-Setup

The `docker/entrypoint.sh` runs on every container start:
1. Creates required directories
2. Syncs migration files from image to volume
3. Creates SQLite DB if missing
4. Runs `php artisan migrate --force`
5. Caches config, routes, views (production only)
6. Creates storage symlink

## Backups

```bash
ssh homeserver "cd ~/keywordai && ./backup.sh"
```

Backs up to `~/keywordai/backups/`:
- `database_YYYYMMDD_HHMMSS.sqlite` — SQLite database
- `storage_YYYYMMDD_HHMMSS.tar.gz` — Storage files
- `env_YYYYMMDD_HHMMSS` — .env file
- `google_ads_php_YYYYMMDD_HHMMSS.ini` — Google Ads config

Keeps last 10 backups of each type.

## Key Paths on Server

| Path | Description |
|------|-------------|
| `~/keywordai/` | Project root |
| `~/keywordai/.env` | Environment config (secrets) |
| `~/keywordai/config/google_ads_php.ini` | Google Ads API credentials |
| `~/keywordai/docker/nginx/default.conf` | Nginx config (bind-mounted) |
| `~/keywordai/backups/` | Backup files |

## Common Operations

```bash
# View logs
docker compose -f docker-compose.prod.yml logs -f
docker compose -f docker-compose.prod.yml logs app --tail=50
docker compose -f docker-compose.prod.yml logs queue-worker --tail=50

# Container shell
docker compose -f docker-compose.prod.yml exec app sh

# Artisan commands
docker compose -f docker-compose.prod.yml exec app php artisan tinker
docker compose -f docker-compose.prod.yml exec app php artisan migrate:status
docker compose -f docker-compose.prod.yml exec app php artisan queue:failed

# Restart a single service
docker compose -f docker-compose.prod.yml restart queue-worker

# Check health
curl -s http://localhost:8080/api/info | python3 -m json.tool
curl -s http://localhost:8080/health
```

## No Host-Level PHP/Node

Everything runs inside Docker containers. There is no PHP or Node.js installed on the host. All artisan commands must be run via `docker compose exec app`.

## Nginx Configuration

Nginx proxies all requests to PHP-FPM (`app:9000`). It does NOT serve static files directly — PHP-FPM handles everything including CSS/JS/images since nginx doesn't have access to the app's `public/` directory (it's baked into the app image, not shared via volume).

The `/health` endpoint is handled directly by nginx (returns 200 "healthy").
