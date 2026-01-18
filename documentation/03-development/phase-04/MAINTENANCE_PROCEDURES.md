# Maintenance Procedures

## Overview
This document outlines maintenance procedures, schedules, and best practices for The Strengths Toolbox website.

## Daily Maintenance

### Automated Tasks
- [ ] Monitor error logs: `tail -f storage/logs/laravel.log`
- [ ] Check health endpoints: `curl https://your-domain.com/health`
- [ ] Review form submissions
- [ ] Check disk space usage
- [ ] Monitor server resources (CPU, memory)

### Commands
```bash
# Check application health
curl https://your-domain.com/health/detailed

# View recent errors
tail -n 100 storage/logs/laravel.log | grep ERROR

# Check disk space
df -h
```

## Weekly Maintenance

### Tasks
- [ ] Review and respond to form submissions
- [ ] Check for security updates
- [ ] Review performance metrics
- [ ] Clean up old logs
- [ ] Verify backups are working

### Commands
```bash
# Run security audit
php artisan security:audit

# Check backup status
ls -lh storage/backups/

# Clear old logs (keep last 7 days)
find storage/logs -name "*.log" -mtime +7 -delete
```

## Monthly Maintenance

### Tasks
- [ ] Update dependencies
- [ ] Review and optimize database
- [ ] Check for Laravel security patches
- [ ] Review and update content
- [ ] Performance benchmarking
- [ ] Full security audit

### Commands
```bash
# Update dependencies
composer update --no-dev
npm update

# Run performance benchmark
php artisan benchmark:performance --url=https://your-domain.com

# Database optimization
php artisan db:show
php artisan db:table --table=blog_posts

# Backup database
php artisan backup:database --compress
```

## Quarterly Maintenance

### Tasks
- [ ] Full security audit
- [ ] Performance review
- [ ] Content audit
- [ ] SEO review
- [ ] Backup verification
- [ ] Disaster recovery test

## Maintenance Commands

### Database Backup
```bash
# Create backup
php artisan backup:database

# Create compressed backup
php artisan backup:database --compress

# Custom path
php artisan backup:database --path=storage/custom-backups
```

### Maintenance Mode
```bash
# Enable maintenance mode
php artisan maintenance:manage enable --message="Scheduled maintenance"

# Disable maintenance mode
php artisan maintenance:manage disable

# Check status
php artisan maintenance:manage status
```

### Performance Benchmarking
```bash
# Benchmark all routes
php artisan benchmark:performance

# Custom URL and iterations
php artisan benchmark:performance --url=https://your-domain.com --iterations=20
```

### Security Audit
```bash
# Run security audit
php artisan security:audit
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## Automated Maintenance (Cron Jobs)

### Recommended Cron Schedule

```cron
# Daily database backup at 2 AM
0 2 * * * cd /path/to/project && php artisan backup:database --compress >> /dev/null 2>&1

# Weekly security audit (Monday 3 AM)
0 3 * * 1 cd /path/to/project && php artisan security:audit >> /var/log/security-audit.log 2>&1

# Daily log cleanup (keep last 7 days)
0 4 * * * find /path/to/project/storage/logs -name "*.log" -mtime +7 -delete

# Clear application cache daily
0 5 * * * cd /path/to/project && php artisan cache:clear >> /dev/null 2>&1
```

## Update Procedures

### Laravel Framework Updates
```bash
# 1. Backup database
php artisan backup:database --compress

# 2. Enable maintenance mode
php artisan maintenance:manage enable

# 3. Update Composer dependencies
composer update

# 4. Run migrations
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan optimize:clear
php artisan optimize

# 6. Test application
php artisan test

# 7. Disable maintenance mode
php artisan maintenance:manage disable
```

### Dependency Updates
```bash
# Update PHP dependencies
composer update --no-dev --optimize-autoloader

# Update Node dependencies
npm update
npm run build

# Clear caches
php artisan optimize:clear
php artisan optimize
```

## Monitoring & Alerts

### Health Monitoring
- Monitor `/health` endpoint every 5 minutes
- Alert if status is not "ok"
- Monitor `/health/detailed` for system health

### Error Monitoring
- Monitor error logs for critical errors
- Set up alerts for error rate spikes
- Track form submission failures

### Performance Monitoring
- Monitor page load times
- Track database query performance
- Monitor cache hit rates
- Alert if performance degrades

## Backup Strategy

### Database Backups
- **Frequency:** Daily
- **Retention:** 7 days locally, 30 days offsite
- **Compression:** Enabled
- **Verification:** Weekly restore test

### File Backups
- **Frequency:** Weekly
- **Retention:** 4 weeks
- **Include:** Storage directory, uploaded files

### Backup Verification
```bash
# Test restore from backup
mysql -u username -p database_name < backup_file.sql
```

## Troubleshooting

### Common Issues

#### High Error Rate
1. Check error logs: `tail -f storage/logs/laravel.log`
2. Review recent changes
3. Check database connectivity
4. Verify cache is working

#### Slow Performance
1. Run performance benchmark
2. Check database query performance
3. Verify cache is working
4. Check server resources
5. Review slow query log

#### Form Submission Failures
1. Check rate limiting
2. Verify email service
3. Check form configuration
4. Review validation rules

## Emergency Procedures

### Site Down
1. Enable maintenance mode
2. Check error logs
3. Verify database connectivity
4. Check server status
5. Restore from backup if needed

### Security Incident
1. Enable maintenance mode immediately
2. Review access logs
3. Change all credentials
4. Review and patch vulnerabilities
5. Notify stakeholders

### Data Loss
1. Stop all operations
2. Assess damage
3. Restore from most recent backup
4. Verify data integrity
5. Document incident

## Documentation Updates

### When to Update
- After major changes
- After security patches
- After infrastructure changes
- Quarterly review

### What to Document
- Configuration changes
- New procedures
- Known issues
- Performance optimizations

---

**Last Updated:** 2025-01-27  
**Next Review:** Quarterly
