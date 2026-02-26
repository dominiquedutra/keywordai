#!/bin/bash

# KeywordAI Backup Script
# Creates backups of database and storage

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

BACKUP_DIR="./backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Detect compose command
if docker-compose version &> /dev/null; then
    COMPOSE_CMD="docker-compose -f docker-compose.prod.yml"
else
    COMPOSE_CMD="docker compose -f docker-compose.prod.yml"
fi

mkdir -p "$BACKUP_DIR"

echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  KeywordAI Backup${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""

# Backup database
echo -e "${YELLOW}Backing up database...${NC}"
$COMPOSE_CMD exec -T app cat /var/www/html/database/database.sqlite > "$BACKUP_DIR/database_$TIMESTAMP.sqlite" || {
    echo -e "${RED}Database backup failed!${NC}"
    exit 1
}
echo -e "${GREEN}✓ Database saved: backups/database_$TIMESTAMP.sqlite${NC}"

# Backup storage
echo -e "${YELLOW}Backing up storage...${NC}"
tar -czf "$BACKUP_DIR/storage_$TIMESTAMP.tar.gz" storage/ 2>/dev/null || {
    echo -e "${YELLOW}Warning: Could not backup storage files${NC}"
}
echo -e "${GREEN}✓ Storage saved: backups/storage_$TIMESTAMP.tar.gz${NC}"

# Backup .env
echo -e "${YELLOW}Backing up configuration...${NC}"
cp .env "$BACKUP_DIR/env_$TIMESTAMP" 2>/dev/null || true
if [ -f config/google_ads_php.ini ]; then
    cp config/google_ads_php.ini "$BACKUP_DIR/google_ads_php_$TIMESTAMP.ini" 2>/dev/null || true
fi
echo -e "${GREEN}✓ Configuration saved${NC}"

# Cleanup old backups (keep last 10)
echo -e "${YELLOW}Cleaning up old backups...${NC}"
ls -t "$BACKUP_DIR"/database_*.sqlite 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
ls -t "$BACKUP_DIR"/storage_*.tar.gz 2>/dev/null | tail -n +11 | xargs rm -f 2>/dev/null || true
echo -e "${GREEN}✓ Old backups cleaned${NC}"

echo ""
echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  Backup Complete!${NC}"
echo -e "${GREEN}==========================================${NC}"
echo ""
echo "Backup location: $BACKUP_DIR/"
echo ""
ls -lh "$BACKUP_DIR/" | tail -n +2
