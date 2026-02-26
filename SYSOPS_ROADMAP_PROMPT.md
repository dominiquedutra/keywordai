# SysOps LLM Agent: KeywordAI Home Server Migration

## Executive Summary

**Mission:** Migrate KeywordAI from DigitalOcean VPS to home server infrastructure.

**Current State:**
- Application running on DigitalOcean VPS ($5/month)
- Stack: PHP/Laravel application with SQLite database
- Docker-based deployment already configured

**Target State:**
- Running on home server hardware (NAS, old PC, or Raspberry Pi)
- Zero monthly infrastructure costs
- Fully automated deployment and maintenance
- Backup and monitoring in place

**Business Justification:**
$5/month × 12 months = $60/year saved. Application is not critical (internal tool), making it perfect for home server deployment where occasional downtime is acceptable.

---

## Application Architecture Analysis

### What is KeywordAI?
KeywordAI is a Google Ads search term management platform that:
- Fetches search term data from Google Ads API
- Uses AI (Gemini/OpenAI) to analyze terms and suggest negative keywords
- Provides web UI and REST API for PPC campaign management
- Self-hosted, privacy-focused (data never leaves your server)

### Tech Stack
```
┌─────────────────────────────────────────────────────────────┐
│                         KeywordAI                            │
├─────────────────────────────────────────────────────────────┤
│  Frontend: Laravel Blade + Tailwind CSS + Vue.js            │
│  Backend: PHP 8.2 + Laravel 11                              │
│  Database: SQLite (default) / MySQL / PostgreSQL            │
│  Cache/Queue: Redis                                         │
│  Web Server: Nginx (Alpine)                                 │
│  Container: Docker + Docker Compose                         │
├─────────────────────────────────────────────────────────────┤
│  External APIs:                                             │
│  - Google Ads API (requires credentials)                    │
│  - Optional AI: Gemini, OpenAI, OpenRouter                   │
└─────────────────────────────────────────────────────────────┘
```

### Container Breakdown

#### 1. `app` (PHP-FPM)
**Role:** Main application container
- Runs PHP-FPM process
- Handles all application logic
- Serves API endpoints
- **Resource usage:** ~150MB RAM, low CPU
- **Health check:** `php artisan --version`
- **Persistent volumes:**
  - `db-data` → `/var/www/html/database` (SQLite file)
  - `app-data` → `/var/www/html/storage` (logs, cache, uploads)
  - `./config` → `/var/www/html/config` (read-only, Google Ads credentials)

#### 2. `nginx` (Nginx Alpine)
**Role:** Web server / reverse proxy
- Serves static assets
- Proxies PHP requests to app container
- Handles SSL termination (if configured)
- **Resource usage:** ~10MB RAM, minimal CPU
- **Health check:** HTTP check to `/health` endpoint
- **Port mapping:** `${HTTP_PORT:-8080}:80`

#### 3. `queue-worker` (PHP-FPM)
**Role:** Background job processor
- Processes Laravel queue jobs asynchronously
- Handles Google Ads API sync operations
- **Resource usage:** ~100MB RAM, CPU on demand
- **Command:** `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- **Important:** Runs as separate container to prevent blocking web requests

#### 4. `scheduler` (PHP-FPM)
**Role:** Cron job runner
- Executes Laravel scheduled tasks every minute
- Triggers automated syncs with Google Ads
- **Resource usage:** ~50MB RAM, minimal CPU
- **Command:** `while true; do php artisan schedule:run; sleep 60; done`

#### 5. `redis` (Redis 7 Alpine)
**Role:** In-memory data store
- Queue backend for Laravel
- Cache store (optional)
- **Resource usage:** ~20MB RAM, minimal CPU
- **Health check:** `redis-cli ping`
- **Persistent volume:** `redis-data`

### Total Resource Footprint
```
Container        Memory    CPU      Notes
─────────────────────────────────────────────────────
app              150MB     Low      Main application
nginx            10MB      Minimal  Web server
queue-worker     100MB     Variable Background jobs
scheduler        50MB      Minimal  Cron tasks
redis            20MB      Minimal  Cache/Queue
─────────────────────────────────────────────────────
TOTAL            ~330MB    Low      Very lightweight!
```

**Perfect for:** Raspberry Pi 4, old laptop, NAS, or any home server.

---

## Deployment Roadmap

### Phase 1: Pre-Deployment Assessment

#### 1.1 Hardware Requirements Check
```bash
# Verify system has minimum resources
docker info                    # Docker installed and running?
free -h                        # At least 1GB RAM available?
df -h                          # At least 5GB disk space?
```

**Minimum specs:**
- 1GB RAM (2GB recommended)
- 5GB storage (10GB recommended)
- x86_64 or ARM64 architecture
- Internet connection

#### 1.2 Network Configuration
- **Port availability:** Ensure port 8080 (or chosen port) is available
- **Firewall:** Open port if accessing from outside network
- **Router:** (Optional) Set up port forwarding if accessing remotely
- **Domain:** (Optional) Configure DDNS if no static IP

#### 1.3 Data Migration Planning
**From DigitalOcean:**
```bash
# 1. Create backup on DO server
./backup.sh

# 2. Transfer backups to home server
scp -r backups/ user@home-server:~/keywordai/

# 3. Note current Google Ads credentials location
# (config/google_ads_php.ini - NOT in git!)
```

### Phase 2: Deployment

#### 2.1 Repository Setup
```bash
# Clone repository
git clone https://github.com/dominiquedutra/keywordai.git
cd keywordai

# Verify clean state
git status
```

#### 2.2 Environment Configuration
```bash
# Create .env file
./deploy.sh                     # Automated, or manually:
cp .env.docker.example .env

# Required: Generate APP_KEY
openssl rand -base64 32
# Add to .env: APP_KEY=base64:...

# Optional: Change default port
# Edit docker-compose.yml or set HTTP_PORT env var
export HTTP_PORT=3000
```

#### 2.3 Google Ads API Configuration
```bash
# Copy config template
cp config/google_ads_php.ini.example config/google_ads_php.ini

# Edit with actual credentials
nano config/google_ads_php.ini
```

**Required fields:**
- `developerToken` - From Google Ads API Center
- `loginCustomerId` - Manager account ID (no dashes)
- `clientCustomerId` - Client account ID (no dashes)
- `clientId` - OAuth client ID
- `clientSecret` - OAuth client secret
- `refreshToken` - OAuth refresh token

#### 2.4 Container Deployment
```bash
# One-command deployment
./deploy.sh

# Or manual steps:
docker-compose pull
docker-compose build --no-cache
docker-compose up -d

# Verify health
docker-compose ps
curl http://localhost:8080/health
```

### Phase 3: Data Migration

#### 3.1 Restore from Backup (if migrating)
```bash
# Stop containers
docker-compose down

# Restore database
cp backups/database_YYYYMMDD_HHMMSS.sqlite database/database.sqlite

# Fix permissions
chmod 664 database/database.sqlite

# Start containers
docker-compose up -d
```

#### 3.2 Fresh Setup (if not migrating)
```bash
# Create admin user
docker-compose exec app php artisan tinker
>>> App\Models\User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('secure_password')
... ])
>>> exit
```

### Phase 4: Post-Deployment Configuration

#### 4.1 Security Hardening
```bash
# Disable registration after creating users
# Edit .env:
ALLOW_ACCOUNT_CREATION=false

# Restart to apply
docker-compose restart
```

#### 4.2 Reverse Proxy Setup (Optional but Recommended)
For HTTPS and domain access:

**Option A: Nginx Proxy Manager**
```yaml
# Add to docker-compose.yml
services:
  npm:
    image: jc21/nginx-proxy-manager:latest
    ports:
      - "80:80"
      - "443:443"
      - "81:81"
    volumes:
      - npm-data:/data
      - npm-ssl:/etc/letsencrypt
```

**Option B: Traefik**
```yaml
# Add Traefik labels to nginx service
services:
  nginx:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.keywordai.rule=Host(`keywordai.yourdomain.com`)"
      - "traefik.http.routers.keywordai.tls=true"
```

#### 4.3 Backup Automation
```bash
# Add to crontab (runs daily at 3 AM)
0 3 * * * cd /path/to/keywordai && ./backup.sh

# Or use systemd timer for more control
```

### Phase 5: Monitoring & Maintenance

#### 5.1 Health Monitoring
```bash
# Check all services
docker-compose ps

# View logs
docker-compose logs -f

# Check resource usage
docker stats

# API health check
curl http://localhost:8080/api/health
```

#### 5.2 Update Procedure
```bash
# Automated update with backup
./update.sh

# Manual update steps:
./backup.sh                    # 1. Backup
git pull origin main           # 2. Pull latest
docker-compose down            # 3. Stop
docker-compose up -d --build   # 4. Rebuild & start
docker-compose exec app php artisan migrate --force
```

#### 5.3 Log Management
```bash
# View logs
docker-compose logs -f app
docker-compose logs -f queue-worker

# Clear old logs
docker-compose exec app sh -c 'echo "" > storage/logs/laravel.log'
```

---

## Operational Runbook

### Daily Operations

**Check health:**
```bash
curl -s http://localhost:8080/api/health | jq
```

**Check queue status:**
```bash
docker-compose exec app php artisan queue:status
```

**View recent logs:**
```bash
docker-compose logs --tail=100 app
```

### Weekly Operations

**Backup verification:**
```bash
ls -la backups/
# Ensure recent backups exist
```

**Resource check:**
```bash
docker system df
# Clean if needed: docker system prune
```

**Update check:**
```bash
cd ~/keywordai
git fetch origin
# If updates available: ./update.sh
```

### Troubleshooting Guide

| Symptom | Likely Cause | Solution |
|---------|--------------|----------|
| "502 Bad Gateway" | PHP-FPM not running | `docker-compose restart app` |
| Database errors | Permission issue | `chmod 664 database/*.sqlite` |
| Queue stuck | Worker crashed | `docker-compose restart queue-worker` |
| Out of disk space | Logs too large | `./backup.sh` then clean logs |
| Slow performance | OPcache not enabled | Check Dockerfile, restart |
| Can't access externally | Firewall/port | Check `ufw`, router port forward |

---

## Security Considerations

### Container Security
- ✅ App runs as non-root user (www-data)
- ✅ Config files mounted read-only
- ✅ No sensitive data in environment variables (only in config files)
- ✅ Health checks prevent traffic to unhealthy containers

### Network Security
- Default: Only exposed on localhost (safe)
- For remote access: Use VPN or reverse proxy with SSL
- Never expose port 8080 directly to internet without SSL

### Data Security
- Database file permissions: 664 (readable by www-data)
- Backups stored locally (consider offsite backup)
- Google Ads credentials in separate config file (not in env)

---

## Cost Analysis

### Current (DigitalOcean)
- VPS: $5/month = $60/year
- Backups: $1/month = $12/year
- **Total: ~$72/year**

### New (Home Server)
- Hardware: Already owned ($0)
- Electricity: ~$10-20/year (Pi 4) to $50/year (old PC)
- Internet: Already paying for ($0 additional)
- **Total: ~$0-50/year**

**Savings: $22-72/year**

Plus: Full data ownership, no vendor lock-in, learning opportunity.

---

## Success Criteria

- [ ] Application accessible at home server IP:port
- [ ] Database migrated (or fresh install working)
- [ ] Google Ads sync functional
- [ ] Automated backups running
- [ ] Admin user can log in
- [ ] API tokens can be created
- [ ] All containers healthy (`docker-compose ps`)
- [ ] DigitalOcean VPS can be terminated

---

## Rollback Plan

If home server deployment fails:

1. **Keep DO server running** until home server is verified
2. **DNS/Access:** Can switch back instantly if using domain
3. **Data:** Always have backups before migration
4. **Time limit:** Give home server 1 week trial before terminating DO

---

## Questions for Analysis

As the SysOps agent, please analyze:

1. **Hardware suitability:** Is the target hardware adequate?
2. **Network topology:** Best approach for access (local only, VPN, reverse proxy)?
3. **Backup strategy:** Should we add offsite backup (S3, another server)?
4. **Monitoring:** Do we need alerting (email/Slack on failure)?
5. **Updates:** Automate security updates? Schedule maintenance windows?
6. **Resource optimization:** Any tuning needed for specific hardware?

Provide your assessment and recommended approach.
