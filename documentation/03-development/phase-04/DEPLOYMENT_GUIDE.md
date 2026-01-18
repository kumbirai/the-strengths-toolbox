# Production Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying The Strengths Toolbox to production.

## Prerequisites

- Server with PHP 8.2+, MySQL 8.0+, and Node.js 18+
- Composer installed globally
- Git access to repository
- SSL certificate for HTTPS
- Domain name configured

## Pre-Deployment Checklist

### 1. Server Requirements
- [ ] PHP 8.2 or higher
- [ ] MySQL 8.0 or higher
- [ ] Redis or Memcached for caching
- [ ] Node.js 18+ and npm
- [ ] Composer
- [ ] Nginx or Apache
- [ ] SSL certificate

### 2. Environment Setup
- [ ] Run `php artisan setup:production` to create .env
- [ ] Configure database credentials
- [ ] Configure mail settings
- [ ] Set APP_URL to production domain
- [ ] Configure Calendly URL
- [ ] Set up Redis/Memcached

### 3. Database Setup
- [ ] Create database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed production content: `php artisan db:seed --class=ProductionContentSeeder`

### 4. Asset Optimization
- [ ] Optimize images: `php artisan images:optimize`
- [ ] Build frontend assets: `npm run build`

## Deployment Methods

### Method 1: Automated Script (Recommended)

```bash
# Make script executable (if not already)
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

The script will:
1. Enable maintenance mode
2. Pull latest code
3. Install dependencies
4. Build assets
5. Run migrations
6. Optimize application
7. Set permissions
8. Run health checks
9. Disable maintenance mode

### Method 2: Manual Deployment

```bash
# 1. Enable maintenance mode
php artisan down --message="Deploying updates..." --retry=60

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Disable maintenance mode
php artisan up
```

### Method 3: Docker Deployment

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Configure .env with production values

# 3. Start services
docker-compose -f docker-compose.production.yml up -d

# 4. Run migrations
docker-compose -f docker-compose.production.yml exec app php artisan migrate --force

# 5. Seed data
docker-compose -f docker-compose.production.yml exec app php artisan db:seed --class=ProductionContentSeeder

# 6. Optimize
docker-compose -f docker-compose.production.yml exec app php artisan optimize
```

## Post-Deployment

### 1. Verify Deployment

```bash
# Health check
curl https://your-domain.com/health

# Detailed health check
curl https://your-domain.com/health/detailed
```

### 2. Test Functionality

```bash
# Test routes
php artisan test:routes

# Test forms (requires running server)
php artisan test:forms --url=https://your-domain.com
```

### 3. Monitor Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log
```

### 4. Performance Testing

- Test page load times (< 3 seconds)
- Verify caching is working
- Check database query performance
- Monitor server resources

## Configuration Files

### Nginx Configuration

Copy `nginx.conf.example` to your Nginx configuration directory and customize:

```bash
sudo cp nginx.conf.example /etc/nginx/sites-available/thestrengthstoolbox
sudo ln -s /etc/nginx/sites-available/thestrengthstoolbox /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Environment Variables

Key production environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://thestrengthstoolbox.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=strengths_toolbox
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_STORE=redis
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

CALENDLY_ENABLED=true
CALENDLY_URL=https://calendly.com/your-username/consultation
```

## Monitoring Setup

### 1. Error Tracking

Set up error tracking service (Sentry, Bugsnag, etc.):

```env
SENTRY_LARAVEL_DSN=your_sentry_dsn
```

### 2. Uptime Monitoring

Configure uptime monitoring service to check:
- `https://your-domain.com/health`
- `https://your-domain.com/health/detailed`

### 3. Performance Monitoring

Set up performance monitoring (New Relic, Datadog, etc.)

## Backup Strategy

### Database Backups

```bash
# Daily backup script
#!/bin/bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### File Backups

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/
```

### Automated Backups

Set up cron job for automated backups:

```cron
0 2 * * * /path/to/backup-script.sh
```

## Troubleshooting

### Common Issues

#### 1. 500 Error After Deployment
- Check error logs: `tail -f storage/logs/laravel.log`
- Verify permissions: `chmod -R 775 storage bootstrap/cache`
- Clear cache: `php artisan cache:clear`

#### 2. Assets Not Loading
- Rebuild assets: `npm run build`
- Clear view cache: `php artisan view:clear`
- Verify public/storage link: `php artisan storage:link`

#### 3. Database Connection Errors
- Verify database credentials in .env
- Check database server is running
- Verify network connectivity

#### 4. Slow Performance
- Enable OPcache
- Check Redis/Memcached is running
- Review slow query log
- Optimize images

## Rollback Procedure

If deployment fails:

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Revert code
git reset --hard HEAD~1
# OR
git checkout previous-commit-hash

# 3. Restore dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Disable maintenance mode
php artisan up
```

## Security Checklist

- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] CSRF protection verified
- [ ] Input validation working
- [ ] Error messages don't leak info
- [ ] File upload restrictions
- [ ] Environment variables secured
- [ ] Database credentials secure
- [ ] Regular security updates

## Performance Optimization

### 1. Enable OPcache

```ini
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 2. Configure Redis

```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 3. CDN Setup

Configure CDN for static assets:
- Images
- CSS/JS files
- Fonts

### 4. Image Optimization

```bash
php artisan images:optimize --format=webp --quality=85
```

## Maintenance Windows

Schedule regular maintenance:

1. **Weekly:**
   - Review error logs
   - Check disk space
   - Review performance metrics

2. **Monthly:**
   - Update dependencies
   - Review security patches
   - Optimize database

3. **Quarterly:**
   - Full security audit
   - Performance review
   - Backup verification

## Support

For deployment issues:
1. Check logs: `storage/logs/laravel.log`
2. Review this guide
3. Check Laravel documentation
4. Contact development team

---

**Last Updated:** 2025-01-27  
**Version:** 1.0.0
