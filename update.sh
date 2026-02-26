#!/bin/bash

# KeywordAI Update Script
# Use this to update the application to the latest version

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  KeywordAI Update${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""

# Detect compose command
if docker-compose version &> /dev/null; then
    COMPOSE_CMD="docker-compose -f docker-compose.prod.yml"
else
    COMPOSE_CMD="docker compose -f docker-compose.prod.yml"
fi

# Create backup first
echo -e "${YELLOW}Creating backup before update...${NC}"
./backup.sh || {
    echo -e "${RED}Backup failed! Aborting update.${NC}"
    exit 1
}

# Pull latest code
echo -e "${YELLOW}Pulling latest code...${NC}"
git pull origin main || {
    echo -e "${YELLOW}Warning: Could not pull latest code. Using current version.${NC}"
}

# Rebuild containers
echo -e "${YELLOW}Rebuilding containers...${NC}"
$COMPOSE_CMD down
$COMPOSE_CMD pull
$COMPOSE_CMD build --no-cache
$COMPOSE_CMD up -d

# Run migrations
echo -e "${YELLOW}Running database migrations...${NC}"
sleep 5
$COMPOSE_CMD exec app php artisan migrate --force || {
    echo -e "${YELLOW}Migration note: Some migrations may have already been run.${NC}"
}

# Clear caches
echo -e "${YELLOW}Clearing caches...${NC}"
$COMPOSE_CMD exec app php artisan cache:clear 2>/dev/null || true
$COMPOSE_CMD exec app php artisan config:clear 2>/dev/null || true
$COMPOSE_CMD exec app php artisan view:clear 2>/dev/null || true

echo ""
echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  Update Complete!${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""
echo "Check status with: $COMPOSE_CMD ps"
echo "View logs with:    $COMPOSE_CMD logs -f"
