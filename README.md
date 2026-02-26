# 🔑 KeywordAI

> AI-powered Google Ads Search Term Management Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](DOCKER.md)

KeywordAI is an open-source platform that helps digital marketers and PPC specialists manage Google Ads search terms more efficiently. It uses AI to analyze search terms, suggest negative keywords, and automate campaign optimization workflows.

![KeywordAI Dashboard](https://via.placeholder.com/800x400?text=KeywordAI+Dashboard+Screenshot)

## ✨ Features

### 🔍 Search Term Management
- **Centralized Dashboard** - View all search terms across campaigns in one place
- **Advanced Filtering** - Filter by campaign, ad group, metrics, date ranges
- **Performance Metrics** - Track impressions, clicks, CTR, and cost
- **Smart Categorization** - Automatically identify added/excluded terms

### 🤖 AI-Powered Analysis
- **Multiple AI Providers** - Support for Google Gemini, OpenAI GPT, and OpenRouter
- **Intelligent Suggestions** - AI analyzes terms and suggests which to negate
- **Batch Operations** - Process hundreds of terms with AI in minutes
- **Rationale Explanations** - Understand why terms should be negated

### ⛔ Negative Keyword Management
- **One-Click Negation** - Add terms as negative keywords instantly
- **Bulk Operations** - Negate multiple terms at once
- **Match Type Selection** - Choose exact, phrase, or broad match
- **Reason Tracking** - Document why each term was negated

### 🔄 Automated Sync
- **Google Ads API Integration** - Direct sync with your Google Ads account
- **Scheduled Syncs** - Automatic daily/hourly data updates
- **Historical Data** - Track term performance over time
- **Queue Management** - Background processing for large datasets

### 🔐 Full REST API
- **Token-Based Auth** - Secure API access for integrations
- **Comprehensive Endpoints** - Full CRUD for all resources
- **Rate Limiting** - Respects Google Ads API quotas
- **Webhook Support** - Future extensibility

### 🐳 Self-Hosted & Private
- **Docker Support** - One-command deployment
- **SQLite Default** - No complex database setup required
- **Privacy First** - Your data stays on your servers
- **Cost Effective** - No SaaS subscription fees

## 🚀 Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/yourusername/keywordai.git
cd keywordai

# Copy and configure environment
cp .env.docker.example .env

# Generate app key
openssl rand -base64 32
# Add to .env: APP_KEY=base64:your_generated_key

# Start containers
docker-compose up -d

# Configure Google Ads (see below)
cp config/google_ads_php.ini.example config/google_ads_php.ini
# Edit config/google_ads_php.ini with your credentials

# Restart to apply config
docker-compose restart

# Access at http://localhost:8080
```

### Option 2: Manual Installation

```bash
# Clone repository
git clone https://github.com/yourusername/keywordai.git
cd keywordai

# Install PHP dependencies
composer install --no-dev

# Install Node dependencies
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate

# Configure Google Ads (see below)
cp config/google_ads_php.ini.example config/google_ads_php.ini
# Edit with your credentials

# Start server
php artisan serve
```

## 🔧 Google Ads API Setup

To use KeywordAI, you need Google Ads API credentials:

### 1. Get Developer Token
- Log into your **Google Ads Manager Account**
- Go to Tools & Settings → Setup → API Center
- Apply for API access and get your Developer Token

### 2. Create OAuth Credentials
- Go to [Google Cloud Console](https://console.cloud.google.com/)
- Create a new project
- Enable the Google Ads API
- Go to APIs & Services → Credentials
- Create OAuth 2.0 Client ID (Desktop application type)
- Note the Client ID and Client Secret

### 3. Get Refresh Token
```bash
# Run the provided script
php scripts/get_refresh_token.php

# Follow the authentication flow
# Copy the refresh token output
```

### 4. Configure KeywordAI
Edit `config/google_ads_php.ini`:
```ini
[GOOGLE_ADS]
developerToken = "your_developer_token"
loginCustomerId = "your_manager_account_id"
clientCustomerId = "your_client_account_id"

[OAUTH2]
clientId = "your_client_id.apps.googleusercontent.com"
clientSecret = "your_client_secret"
refreshToken = "your_refresh_token"
```

## 📖 Documentation

- **[API Documentation](API_DOCUMENTATION.md)** - Complete REST API reference
- **[Docker Guide](DOCKER.md)** - Self-hosting with Docker
- **[Contributing](CONTRIBUTING.md)** - How to contribute to the project

### API Quick Start

```bash
# Get API token from UI (Settings → API Tokens)

# Query search terms
curl -H "X-API-Token: your_token" \
  http://localhost:8080/api/search-terms

# AI analysis
curl -X POST -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{"analysis_type":"date","date":"2025-02-24","model":"gemini"}' \
  http://localhost:8080/api/ai/analyze

# Add negative keyword
curl -X POST -H "X-API-Token: your_token" \
  -H "Content-Type: application/json" \
  -d '{"keyword":"irrelevant term","match_type":"phrase"}' \
  http://localhost:8080/api/negative-keywords
```

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        KeywordAI                             │
├─────────────────────────────────────────────────────────────┤
│  Web UI (Blade + Tailwind)     │  REST API (Token Auth)      │
│  • Dashboard                   │  • Search Terms             │
│  • Search Term Browser         │  • Campaigns                │
│  • AI Analysis                 │  • Negative Keywords        │
│  • Token Management            │  • Sync Operations          │
├─────────────────────────────────────────────────────────────┤
│                      Laravel Backend                         │
│  • Queue Workers (Redis)      • Google Ads API Client        │
│  • Database (SQLite/MySQL)    • AI Service Integrations      │
├─────────────────────────────────────────────────────────────┤
│                    External Services                         │
│  Google Ads API    │   Gemini   │   OpenAI   │   OpenRouter  │
└─────────────────────────────────────────────────────────────┘
```

## 🛠️ Tech Stack

- **Backend**: PHP 8.2, Laravel 11
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Database**: SQLite (default), MySQL/PostgreSQL supported
- **Queue**: Redis, Database
- **AI APIs**: Google Gemini, OpenAI GPT, OpenRouter
- **Container**: Docker, Docker Compose

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

Quick start for contributors:
```bash
# Fork and clone
git clone https://github.com/yourusername/keywordai.git
cd keywordai

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# Run development server
npm run dev
php artisan serve
```

## 🗺️ Roadmap

- [ ] Campaign performance analytics
- [ ] Automated negative keyword suggestions (scheduled)
- [ ] Integration with other ad platforms (Meta Ads, etc.)
- [ ] Machine learning for term categorization
- [ ] Multi-user teams with role-based access
- [ ] Webhook notifications
- [ ] Mobile app

## 📸 Screenshots

### Dashboard
![Dashboard](https://via.placeholder.com/600x300?text=Dashboard+Screenshot)

### Search Terms
![Search Terms](https://via.placeholder.com/600x300?text=Search+Terms+Screenshot)

### AI Analysis
![AI Analysis](https://via.placeholder.com/600x300?text=AI+Analysis+Screenshot)

### API Token Management
![API Tokens](https://via.placeholder.com/600x300?text=API+Token+Management)

## ⚠️ Important Notes

- **Google Ads API Quotas**: The tool respects Google Ads API rate limits (15,000 ops/day for standard access)
- **Data Privacy**: Your Google Ads data never leaves your server
- **API Credentials**: Never commit your `config/google_ads_php.ini` file (it's in `.gitignore`)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Google Ads API PHP Client](https://github.com/googleads/google-ads-php)
- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- All contributors who help improve this project

---

<p align="center">
  Made with ❤️ for the PPC community
</p>

<p align="center">
  <a href="https://github.com/yourusername/keywordai">⭐ Star us on GitHub</a> •
  <a href="https://github.com/yourusername/keywordai/issues">🐛 Report Bug</a> •
  <a href="https://github.com/yourusername/keywordai/issues">💡 Request Feature</a>
</p>
