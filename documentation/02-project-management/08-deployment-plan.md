# Deployment Plan

## 1. Overview

This document provides a comprehensive deployment plan for The Strengths Toolbox website to cPanel shared hosting. It includes pre-deployment checklists, step-by-step deployment procedures, rollback procedures, and post-deployment validation.

## 2. Deployment Objectives

### 2.1 Primary Objectives

- Deploy application to cPanel production environment
- Configure production database
- Set up SSL/HTTPS
- Verify all functionality works
- Ensure performance meets targets
- Complete security hardening
- Launch website successfully

### 2.2 Success Criteria

- Website accessible via production URL
- All pages load correctly
- HTTPS working
- All forms functional
- Database connections working
- No critical errors
- Performance acceptable
- Security measures in place

## 3. Pre-Deployment Checklist

### 3.1 Code Readiness

- [ ] All code committed to version control
- [ ] All tests passing
- [ ] Code review completed
- [ ] No critical bugs remaining
- [ ] Documentation updated
- [ ] Version tagged in Git

### 3.2 Content Readiness

- [ ] All content migrated
- [ ] Brand name replacement verified (100%)
- [ ] All images optimized and uploaded
- [ ] All links verified
- [ ] Content approved by stakeholder

### 3.3 Environment Readiness

- [ ] cPanel hosting account active
- [ ] Database created on cPanel
- [ ] Database user created with permissions
- [ ] PHP version verified (8.1+)
- [ ] Required PHP extensions installed
- [ ] SSL certificate available (Let's Encrypt)

### 3.4 Configuration Readiness

- [ ] Production .env file prepared
- [ ] Database credentials ready
- [ ] Email SMTP settings ready
- [ ] OpenAI API key ready
- [ ] Analytics tracking ID ready (if applicable)
- [ ] Domain DNS configured (if needed)

### 3.5 Testing Readiness

- [ ] All testing phases completed
- [ ] User acceptance testing passed
- [ ] Performance testing passed
- [ ] Security testing passed
- [ ] Cross-browser testing completed
- [ ] Mobile testing completed

### 3.6 Backup Readiness

- [ ] Backup strategy defined
- [ ] Backup tools configured
- [ ] Backup location determined
- [ ] Restore procedure documented
- [ ] Test backup/restore completed

## 4. Deployment Environment Setup

### 4.1 cPanel Access

**Requirements:**
- cPanel login credentials
- FTP/SFTP access
- SSH access (if available)
- File Manager access
- Database management access

**Steps:**
1. Log in to cPanel
2. Verify PHP version (should be 8.1 or higher)
3. Verify required PHP extensions are available
4. Note file paths and directory structure

### 4.2 Database Setup

**Steps:**
1. Navigate to "MySQL Databases" in cPanel
2. Create new database: `strengthstoolbox_db` (or similar)
3. Create database user: `strengthstoolbox_user` (or similar)
4. Grant all privileges to user on database
5. Note database credentials:
   - Database name
   - Database user
   - Database password
   - Database host (usually `localhost`)

**Verification:**
- Test database connection from cPanel
- Verify user has correct permissions

### 4.3 File Structure Setup

**Option 1: Root Deployment (Recommended)**
- Document root: `public_html/`
- Laravel files in `public_html/`
- Public assets in `public_html/public/` (or adjust document root)

**Option 2: Subdirectory Deployment**
- Laravel app in `public_html/thestrengthstoolbox/`
- Adjust document root or use .htaccess redirects

**Steps:**
1. Determine deployment structure
2. Create necessary directories
3. Set up file permissions

## 5. Deployment Steps

### 5.1 Step 1: Prepare Local Environment

**Tasks:**
1. Ensure all changes committed to Git
2. Create production build:
   ```bash
   npm run build
   ```
3. Run final tests
4. Create deployment package (if using FTP)

**Commands:**
```bash
# Final commit
git add .
git commit -m "Ready for production deployment"
git tag -a v1.0.0 -m "Production release v1.0.0"

# Build production assets
npm run build

# Run tests (if applicable)
php artisan test
```

### 5.2 Step 2: Upload Files

**Method A: Git (If Available on cPanel)**
```bash
# SSH into server
ssh username@server

# Navigate to directory
cd public_html

# Clone or pull repository
git clone repository-url .
# OR
git pull origin main
```

**Method B: FTP/SFTP**
1. Connect via FTP client
2. Upload all files except:
   - `.env` (create on server)
   - `node_modules/` (not needed)
   - `.git/` (optional)
3. Maintain directory structure

**Method C: cPanel File Manager**
1. Log in to cPanel
2. Navigate to File Manager
3. Upload ZIP file
4. Extract files
5. Organize files correctly

**Files to Upload:**
- All application files (`app/`, `config/`, `routes/`, etc.)
- `vendor/` directory (or install via Composer on server)
- `public/` directory contents
- `resources/` directory
- `storage/` directory structure
- `bootstrap/` directory
- `database/` directory
- `composer.json`, `package.json`, etc.

**Files NOT to Upload:**
- `.env` file (create on server)
- `node_modules/` (install on server)
- `.git/` (optional)
- Development files

### 5.3 Step 3: Install Dependencies

**Via SSH (Preferred):**
```bash
# Navigate to application directory
cd /home/username/public_html

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies (if needed for future builds)
npm install --production

# Build assets (if not done locally)
npm run build
```

**Via cPanel Terminal:**
1. Open Terminal in cPanel
2. Navigate to application directory
3. Run Composer and npm commands

**Note:** If Composer not available via command line, may need to:
- Use Composer PHAR file
- Or install dependencies locally and upload `vendor/`

### 5.4 Step 4: Configure Environment

**Create .env File:**
1. Copy `.env.example` to `.env` (if available)
2. Or create new `.env` file
3. Configure all settings:

```env
APP_NAME="The Strengths Toolbox"
APP_ENV=production
APP_KEY=base64:... # Generate via: php artisan key:generate
APP_DEBUG=false
APP_URL=https://www.thestrengthstoolbox.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=strengthstoolbox_db
DB_USERNAME=strengthstoolbox_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@thestrengthstoolbox.com
MAIL_PASSWORD=email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@thestrengthstoolbox.com
MAIL_FROM_NAME="${APP_NAME}"

OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=500

GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

**Generate Application Key:**
```bash
php artisan key:generate
```

**Set File Permissions:**
```bash
# Storage and cache directories (writable)
chmod -R 775 storage bootstrap/cache

# .env file (readable only by owner)
chmod 600 .env

# Other files and directories
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

### 5.5 Step 5: Run Database Migrations

**Run Migrations:**
```bash
php artisan migrate --force
```

**Seed Database (If Needed):**
```bash
php artisan db:seed --force
```

**Verify:**
- Check database tables created
- Verify data integrity
- Test database connections

### 5.6 Step 6: Configure Web Server

**.htaccess Configuration:**
Ensure `public_html/.htaccess` (or appropriate location) contains:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Force www or non-www (choose one)
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Document Root Configuration:**
- If Laravel in root: Point document root to `public_html/`
- If Laravel in subdirectory: Adjust paths accordingly

### 5.7 Step 7: Install SSL Certificate

**Via cPanel:**
1. Navigate to "SSL/TLS Status"
2. Select domain
3. Click "Run AutoSSL" or "Install SSL Certificate"
4. Select Let's Encrypt
5. Install certificate

**Verify:**
- Test HTTPS access
- Verify certificate valid
- Check for mixed content warnings

### 5.8 Step 8: Clear and Cache

**Clear All Caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Cache for Production:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Optimize:**
```bash
php artisan optimize
```

### 5.9 Step 9: Verify Deployment

**Checklist:**
- [ ] Website accessible via URL
- [ ] HTTPS working
- [ ] Homepage loads
- [ ] All pages accessible
- [ ] Forms submit
- [ ] Database connections work
- [ ] Images load
- [ ] No error messages
- [ ] Admin panel accessible
- [ ] Performance acceptable

## 6. Post-Deployment Validation

### 6.1 Functional Validation

**Test All Pages:**
1. Homepage
2. Strengths Programme page
3. About Us page
4. All existing content pages
5. Blog listing
6. Blog posts
7. Contact page
8. Admin panel

**Test All Features:**
1. Contact form submission
2. eBook sign-up form
3. Search functionality
4. Navigation
5. Blog pagination
6. Booking calendar
7. Chatbot (if implemented)

### 6.2 Performance Validation

**Check:**
- Page load times (< 3 seconds)
- Core Web Vitals
- Image loading
- Database query performance
- API response times

**Tools:**
- Google PageSpeed Insights
- GTmetrix
- Chrome DevTools

### 6.3 Security Validation

**Check:**
- HTTPS enforced
- Security headers present
- Forms have CSRF protection
- Admin panel protected
- No sensitive data exposed
- Error messages don't reveal info

### 6.4 SEO Validation

**Check:**
- Meta tags present
- Schema markup working
- Sitemap accessible
- Robots.txt configured
- Canonical URLs set
- Open Graph tags present

## 7. Rollback Procedures

### 7.1 When to Rollback

**Immediate Rollback Triggers:**
- Critical security vulnerability discovered
- Complete site failure
- Data loss or corruption
- Critical functionality broken

**Consider Rollback:**
- Multiple high-priority bugs
- Performance degradation
- User complaints
- Stakeholder request

### 7.2 Rollback Steps

**Step 1: Assess Situation**
- Determine severity
- Identify affected areas
- Decide: full rollback or partial fix

**Step 2: Backup Current State**
```bash
# Backup database
mysqldump -u username -p database_name > backup_before_rollback.sql

# Backup files (if needed)
tar -czf files_backup.tar.gz public_html/
```

**Step 3: Restore Previous Version**

**Option A: Git Rollback (If Using Git)**
```bash
# Revert to previous tag/commit
git checkout v0.9.0
# OR
git reset --hard previous_commit_hash
```

**Option B: File Restore**
- Restore files from backup
- Restore database from backup

**Step 4: Clear Caches**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Step 5: Verify Rollback**
- Test all critical functionality
- Verify site accessible
- Check for errors
- Monitor logs

**Step 6: Communicate**
- Notify stakeholders
- Document rollback reason
- Plan fix and redeployment

### 7.3 Rollback Testing

**Before Deployment:**
- Test rollback procedure
- Verify backup/restore works
- Document rollback steps
- Prepare rollback scripts

## 8. Deployment Scripts

### 8.1 Deployment Script (deploy.sh)

```bash
#!/bin/bash

echo "Starting deployment..."

# Navigate to application directory
cd /home/username/public_html

# Pull latest changes (if using Git)
# git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm install --production
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize
php artisan optimize

# Set permissions
chmod -R 775 storage bootstrap/cache
chmod 600 .env

echo "Deployment complete!"
```

### 8.2 Backup Script (backup.sh)

```bash
#!/bin/bash

echo "Starting backup..."

# Create backup directory
mkdir -p /home/username/backups

# Backup database
mysqldump -u username -p'password' database_name > /home/username/backups/db_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf /home/username/backups/files_$(date +%Y%m%d_%H%M%S).tar.gz /home/username/public_html

echo "Backup complete!"
```

### 8.3 Rollback Script (rollback.sh)

```bash
#!/bin/bash

echo "Starting rollback..."

# Restore database
mysql -u username -p'password' database_name < /home/username/backups/db_backup.sql

# Restore files (if needed)
# tar -xzf /home/username/backups/files_backup.tar.gz -C /

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Rollback complete!"
```

## 9. Monitoring and Maintenance

### 9.1 Post-Deployment Monitoring

**First 24 Hours:**
- Monitor error logs continuously
- Check performance metrics
- Monitor user feedback
- Watch for critical issues

**First Week:**
- Daily error log review
- Performance monitoring
- User feedback collection
- Bug tracking

**Ongoing:**
- Weekly log review
- Monthly performance review
- Regular security updates
- Dependency updates

### 9.2 Error Monitoring

**Laravel Logs:**
- Location: `storage/logs/laravel.log`
- Monitor for errors, warnings
- Set up log rotation

**Apache Logs:**
- Access logs
- Error logs
- Monitor via cPanel

**Application Monitoring:**
- Set up uptime monitoring (UptimeRobot, Pingdom)
- Error tracking (if implemented)
- Performance monitoring

### 9.3 Maintenance Tasks

**Regular Tasks:**
- Clear old log files
- Update dependencies
- Security patches
- Database optimization
- Cache clearing (if needed)
- Backup verification

**Scheduled Tasks (Cron Jobs):**
- Daily database backups
- Weekly file backups
- Log rotation
- Cache warming (if needed)

## 10. Troubleshooting

### 10.1 Common Issues

**500 Internal Server Error:**
- Check file permissions
- Check .env configuration
- Check Laravel logs
- Check Apache error logs
- Verify PHP version

**Database Connection Error:**
- Verify database credentials in .env
- Check database user permissions
- Verify database exists
- Test connection from cPanel

**Asset Loading Issues:**
- Run `npm run build`
- Check `public/build/` directory
- Verify Vite manifest
- Clear browser cache

**Permission Denied:**
- Check `storage/` and `bootstrap/cache/` permissions
- Verify web server user has write access
- Check file ownership

**SSL Certificate Issues:**
- Verify certificate installed
- Check certificate expiration
- Verify domain matches certificate
- Test HTTPS redirect

### 10.2 Debug Mode

**Never Enable in Production:**
```env
APP_DEBUG=false
```

**If Debugging Needed Temporarily:**
1. Enable debug mode
2. Fix issue
3. Disable immediately
4. Clear config cache

## 11. Deployment Checklist Summary

### Pre-Deployment
- [ ] Code ready and tested
- [ ] Content migrated and validated
- [ ] Environment configured
- [ ] Backups prepared
- [ ] Deployment plan reviewed

### Deployment
- [ ] Files uploaded
- [ ] Dependencies installed
- [ ] Environment configured
- [ ] Database migrated
- [ ] SSL installed
- [ ] Caches cleared and optimized

### Post-Deployment
- [ ] All pages verified
- [ ] Forms tested
- [ ] Performance validated
- [ ] Security verified
- [ ] Monitoring set up
- [ ] Stakeholder notified

## 12. Communication Plan

### 12.1 Pre-Deployment Communication

- Notify stakeholders of deployment schedule
- Schedule maintenance window (if needed)
- Prepare rollback communication

### 12.2 During Deployment

- Status updates during deployment
- Immediate notification of issues
- Progress updates

### 12.3 Post-Deployment Communication

- Deployment completion notification
- Post-deployment status report
- Known issues communication
- Next steps

---

**Document Version:** 1.0  
**Date Created:** 2025  
**Status:** Active  
**Next Review:** Before deployment phase begins
