#!/bin/bash

# KeywordAI Home Server Deployment Script
# This script helps deploy KeywordAI on a home server

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  KeywordAI Home Server Setup${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker is not installed!${NC}"
    echo "Please install Docker first: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}Docker Compose is not installed!${NC}"
    echo "Please install Docker Compose: https://docs.docker.com/compose/install/"
    exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env file from example...${NC}"
    cp .env.docker.example .env
    
    # Generate APP_KEY
    echo -e "${YELLOW}Generating APP_KEY...${NC}"
    APP_KEY=$(openssl rand -base64 32)
    echo "APP_KEY=base64:${APP_KEY}" >> .env
    
    echo -e "${GREEN}✓ .env file created!${NC}"
    echo -e "${YELLOW}Please review and update .env file if needed.${NC}"
fi

# Check if Google Ads config exists
if [ ! -f config/google_ads_php.ini ]; then
    echo -e "${YELLOW}Google Ads config not found!${NC}"
    echo -e "${YELLOW}Please copy the example file and configure:${NC}"
    echo "  cp config/google_ads_php.ini.example config/google_ads_php.ini"
    echo "  nano config/google_ads_php.ini"
    echo ""
    echo -e "${YELLOW}The application will start but won't sync with Google Ads until configured.${NC}"
fi

# Create required directories
echo -e "${YELLOW}Creating required directories...${NC}"
mkdir -p storage/logs storage/framework/{cache,sessions,views} storage/app/public database bootstrap/cache

# Set permissions (if running as non-root)
if [ "$EUID" -ne 0 ]; then
    chmod -R 775 storage bootstrap/cache database 2>/dev/null || true
fi

echo -e "${GREEN}✓ Directories created${NC}"

# Build and start containers
echo -e "${YELLOW}Building and starting containers...${NC}"
echo "This may take a few minutes on first run..."
echo ""

if docker-compose version &> /dev/null; then
    COMPOSE_CMD="docker-compose"
else
    COMPOSE_CMD="docker compose"
fi

$COMPOSE_CMD pull
$COMPOSE_CMD build --no-cache
$COMPOSE_CMD up -d

echo ""
echo -e "${GREEN}✓ Containers started!${NC}"
echo ""

# Wait for services to be ready
echo -e "${YELLOW}Waiting for services to be ready...${NC}"
sleep 10

# Check if app is healthy
echo -e "${YELLOW}Checking application health...${NC}"
for i in {1..30}; do
    if curl -s http://localhost:8080/health > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Application is healthy!${NC}"
        break
    fi
    echo -n "."
    sleep 2
done

echo ""
echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  Setup Complete!${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""
echo "KeywordAI is now running at:"
echo "  🌐 http://localhost:8080"
echo ""
echo "API Documentation:"
echo "  📖 http://localhost:8080/api/docs"
echo ""
echo "To create an admin user, run:"
echo "  $COMPOSE_CMD exec app php artisan tinker"
echo "  >>> User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('password')])"
echo ""
echo "Useful commands:"
echo "  View logs:        $COMPOSE_CMD logs -f"
echo "  Stop services:    $COMPOSE_CMD down"
echo "  Restart:          $COMPOSE_CMD restart"
echo "  Update:           ./update.sh"
echo ""
echo -e "${YELLOW}Don't forget to:${NC}"
echo "  1. Configure Google Ads API in config/google_ads_php.ini"
echo "  2. Set up your admin user"
echo "  3. Review and customize .env settings"
echo ""
