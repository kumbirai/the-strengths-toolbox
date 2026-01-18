#!/bin/bash

# Production Deployment Script
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting production deployment..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Are you in the Laravel root directory?${NC}"
    exit 1
fi

# Step 1: Maintenance mode
echo -e "${YELLOW}Step 1: Enabling maintenance mode...${NC}"
php artisan down --message="Deploying updates..." --retry=60 || true

# Step 2: Pull latest code (if using git)
if [ -d ".git" ]; then
    echo -e "${YELLOW}Step 2: Pulling latest code...${NC}"
    git pull origin main || git pull origin master
fi

# Step 3: Install/update dependencies
echo -e "${YELLOW}Step 3: Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Step 4: Install npm dependencies and build assets
echo -e "${YELLOW}Step 4: Building frontend assets...${NC}"
npm ci
npm run build:production

# Step 5: Run migrations
echo -e "${YELLOW}Step 5: Running database migrations...${NC}"
php artisan migrate --force

# Step 6: Clear and cache configuration
echo -e "${YELLOW}Step 6: Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Step 7: Optimize autoloader
echo -e "${YELLOW}Step 7: Optimizing autoloader...${NC}"
composer dump-autoload --optimize --classmap-authoritative

# Step 8: Clear application cache
echo -e "${YELLOW}Step 8: Clearing application cache...${NC}"
php artisan cache:clear
php artisan optimize:clear

# Step 9: Re-optimize
echo -e "${YELLOW}Step 9: Final optimization...${NC}"
php artisan optimize

# Step 10: Set permissions
echo -e "${YELLOW}Step 10: Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Step 11: Create storage link if it doesn't exist
echo -e "${YELLOW}Step 11: Creating storage link...${NC}"
php artisan storage:link || true

# Step 12: Run health check
echo -e "${YELLOW}Step 12: Running health check...${NC}"
php artisan test:routes || echo -e "${YELLOW}Warning: Route tests failed, but continuing...${NC}"

# Step 13: Disable maintenance mode
echo -e "${YELLOW}Step 13: Disabling maintenance mode...${NC}"
php artisan up

echo ""
echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "Next steps:"
echo "  1. Test the application: curl https://your-domain.com/health"
echo "  2. Monitor error logs: tail -f storage/logs/laravel.log"
echo "  3. Check application status"
echo ""
