# Self-Hosting KeywordAI with Docker

This guide explains how to self-host KeywordAI on your home server using Docker Compose.

## 💰 Why Self-Host?

Moving from a VPS (like DigitalOcean $5/month) to your home server:
- **No monthly fees** - Use existing hardware
- **Full control** - Your data stays at home
- **Privacy** - No third-party access to your Google Ads data
- **Learning** - Great way to learn Docker and self-hosting

## 📋 Requirements

### Hardware
- Any computer/server that can run 24/7
- Minimum: 2GB RAM, 10GB storage
- Recommended: 4GB RAM, 20GB storage
- Architecture: x86_64 or ARM64 (Raspberry Pi 4+ supported)

### Software
- Docker Engine 20.10+
- Docker Compose 2.0+
- Internet connection (for initial setup and Google Ads API)

## 🚀 Quick Start (5 minutes)

### 1. Clone the Repository

```bash
git clone https://github.com/dominiquedutra/keywordai.git
cd keywordai
```

### 2. Run the Deploy Script

```bash
./deploy.sh
```

This will:
- Check Docker is installed
- Create `.env` file with secure keys
- Create required directories
- Build and start all containers
- Wait for services to be ready

### 3. Create Admin User

```bash
# Enter the app container
docker-compose exec app php artisan tinker

# Create user (in the tinker shell)
>>> App\Models\User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('your_secure_password')])
>>> exit
```

### 4. Access the Application

- **Web Interface**: http://your-server-ip:8080
- **API Documentation**: http://your-server-ip:8080/api/docs
- **Health Check**: http://your-server-ip:8080/health

### 5. Configure Google Ads (Required for sync)

```bash
# Copy the example config
cp config/google_ads_php.ini.example config/google_ads_php.ini

# Edit with your credentials
nano config/google_ads_php.ini
```

Then restart:
```bash
docker-compose restart
```

## 🔧 Manual Setup (If you prefer)

### 1. Environment Configuration

```bash
# Copy example environment
cp .env.docker.example .env

# Generate APP_KEY
openssl rand -base64 32
# Add to .env: APP_KEY=base64:your_generated_key
```

### 2. Create Directories

```bash
mkdir -p storage/logs storage/framework/{cache,sessions,views} storage/app/public database bootstrap/cache
```

### 3. Start Services

```bash
docker-compose up -d --build
```

### 4. Run Migrations

```bash
docker-compose exec app php artisan migrate --force
```

## 🔐 Google Ads API Setup

To sync data from Google Ads, you need API credentials:

### Step 1: Get Developer Token

1. Log into your **Google Ads Manager Account** (MCC)
2. Go to **Tools & Settings** → **Setup** → **API Center**
3. Apply for API access
4. Once approved, copy your **Developer Token**

### Step 2: Create OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select existing)
3. Enable the **Google Ads API**
4. Go to **APIs & Services** → **Credentials**
5. Click **Create Credentials** → **OAuth client ID**
6. Application type: **Desktop app**
7. Note the **Client ID** and **Client Secret**

### Step 3: Get Refresh Token

Run this on your local machine (not the server):

```bash
# Clone the repo locally
git clone https://github.com/dominiquedutra/keywordai.git
cd keywordai

# Run the token generation script
php scripts/get_refresh_token.php
```

Follow the browser authentication flow and copy the refresh token.

### Step 4: Configure KeywordAI

Edit `config/google_ads_php.ini`:

```ini
[GOOGLE_ADS]
developerToken = "YOUR_DEVELOPER_TOKEN"
loginCustomerId = "YOUR_MCC_ACCOUNT_ID"      # Without dashes
clientCustomerId = "YOUR_CLIENT_ACCOUNT_ID"  # Without dashes

[OAUTH2]
clientId = "YOUR_CLIENT_ID.apps.googleusercontent.com"
clientSecret = "YOUR_CLIENT_SECRET"
refreshToken = "YOUR_REFRESH_TOKEN"
```

**Important:** This file is in `.gitignore` and won't be committed.

### Step 5: Restart

```bash
docker-compose restart
```

## 📁 Data Persistence

Your data is stored in Docker volumes:

| Volume | Location | Contents |
|--------|----------|----------|
| `db-data` | `./database` | SQLite database |
| `app-data` | `./storage` | Logs, cache, uploads |
| `redis-data` | Docker managed | Queue data |

**Backup your data regularly!**

```bash
# Automated backup
./backup.sh

# Backups are saved to ./backups/
```

## 🔄 Updating

To update to the latest version:

```bash
./update.sh
```

This will:
1. Create a backup
2. Pull latest code
3. Rebuild containers
4. Run migrations
5. Clear caches

## 🛠️ Common Commands

```bash
# View all logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f queue-worker

# Restart services
docker-compose restart

# Stop all services
docker-compose down

# Stop and remove data (WARNING: deletes database)
docker-compose down -v

# Access app container
docker-compose exec app sh

# Run artisan commands
docker-compose exec app php artisan <command>

# Check queue status
docker-compose exec app php artisan queue:status

# Database shell
docker-compose exec app sqlite3 database/database.sqlite
```

## 🔒 Security Recommendations

### 1. Change Default Port

Edit `docker-compose.yml`:
```yaml
nginx:
  ports:
    - "3000:80"  # Use any port you prefer
```

### 2. Use a Reverse Proxy (Recommended)

For HTTPS and domain names, use Nginx Proxy Manager or Traefik:

**Nginx Proxy Manager:**
```yaml
# Add to docker-compose.yml
  nginx-proxy-manager:
    image: jc21/nginx-proxy-manager:latest
    ports:
      - "80:80"
      - "443:443"
      - "81:81"  # Admin UI
    volumes:
      - npm-data:/data
      - npm-letsencrypt:/etc/letsencrypt
```

### 3. Disable Registration After Setup

After creating your admin user, set in `.env`:
```
ALLOW_ACCOUNT_CREATION=false
```

Then restart:
```bash
docker-compose restart
```

### 4. IP Whitelisting (Optional)

To restrict access to specific IPs, set in `.env`:
```
FORCE_IP_WHITELIST=true
WHITELISTED_IP_ADDRESSES="192.168.1.100,192.168.1.101"
```

## 🐛 Troubleshooting

### "Permission denied" errors

```bash
# Fix permissions
sudo chown -R $USER:$USER .
chmod -R 775 storage bootstrap/cache database
```

### Database is locked or corrupt

```bash
# Restore from backup
cp backups/database_YYYYMMDD_HHMMSS.sqlite database/database.sqlite

# Or start fresh
rm database/database.sqlite
docker-compose restart
```

### Queue not processing jobs

```bash
# Restart queue worker
docker-compose restart queue-worker

# Check for failed jobs
docker-compose exec app php artisan queue:failed
```

### Container won't start

```bash
# Check logs
docker-compose logs app

# Rebuild containers
docker-compose down
docker-compose up -d --build
```

### Out of disk space

```bash
# Clean up Docker system
docker system prune -a

# Clean old logs
docker-compose exec app sh -c 'echo "" > storage/logs/laravel.log'
```

## 📊 Resource Usage

Typical resource usage on a home server:

| Container | Memory | CPU |
|-----------|--------|-----|
| app | ~150MB | Low |
| nginx | ~10MB | Low |
| queue-worker | ~100MB | On demand |
| scheduler | ~50MB | Low |
| redis | ~20MB | Low |
| **Total** | **~330MB** | **Minimal** |

Perfect for running on a Raspberry Pi or old laptop!

## 🆘 Getting Help

- **Documentation**: http://your-server:8080/api/docs
- **Issues**: https://github.com/dominiquedutra/keywordai/issues
- **Discussions**: https://github.com/dominiquedutra/keywordai/discussions

## 🎉 Success!

You're now saving $5/month and have full control over your KeywordAI instance!

---

**Next Steps:**
1. Set up automated backups (cron job)
2. Configure Google Ads API
3. Set up a domain with HTTPS (optional)
4. Enjoy your self-hosted PPC management tool!
