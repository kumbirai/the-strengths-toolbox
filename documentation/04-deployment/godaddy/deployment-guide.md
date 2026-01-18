# GoDaddy cPanel Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying The Strengths Toolbox Laravel 12 application on GoDaddy cPanel hosting. GoDaddy's shared hosting environment has specific requirements and limitations that this guide addresses.

## Prerequisites

### GoDaddy Account Requirements

- GoDaddy cPanel Linux hosting account (shared hosting or higher)
- SSH access enabled (required for Composer and Laravel commands)
- Domain name configured and pointing to GoDaddy
- Access to cPanel dashboard

### Server Requirements Verification

Before deployment, verify your GoDaddy hosting meets these requirements:

1. **PHP Version**: PHP 8.2 or higher (Laravel 12 requirement)
   - Check via cPanel: "Server Settings" → "PHP Version"
   - Select PHP 8.2 or higher if available
   - If only PHP 8.1 is available, consider upgrading hosting plan

2. **Required PHP Extensions** (verify via cPanel → "Select PHP Version" → "Extensions"):
   - `php-mbstring`
   - `php-xml`
   - `php-curl`
   - `php-zip`
   - `php-gd`
   - `php-mysql` or `php-mysqli`
   - `php-openssl`
   - `php-json`
   - `php-fileinfo`
   - `php-tokenizer`
   - `php-pdo`

3. **PHP Configuration** (via cPanel → "Select PHP Version" → "Options"):
   - `memory_limit`: 256M (minimum)
   - `upload_max_filesize`: 10M
   - `post_max_size`: 10M
   - `max_execution_time`: 300
   - `allow_url_fopen`: On (required for Composer)

4. **Composer**: 
   - May need to install locally in home directory if not available globally
   - SSH access required to run Composer commands

5. **Node.js** (for building frontend assets):
   - GoDaddy may have limited Node.js support on shared hosting
   - **Recommendation**: Build assets locally before deployment
   - If Node.js is available, verify version is Node.js LTS 24 (older versions deprecated)

6. **Apache mod_rewrite**: Should be enabled by default on GoDaddy

## Pre-Deployment Preparation

### 1. Build Assets Locally (Recommended)

Since GoDaddy shared hosting may have limited Node.js support or resource constraints, build production assets locally:

```bash
# On your local machine
cd /path/to/the-strengths-toolbox
npm install
npm run build
```

This creates optimized assets in `public/build/` that will be uploaded to the server.

### 2. Prepare Files for Upload

Create a deployment package excluding unnecessary files:

```bash
# Files to exclude from upload:
# - node_modules/
# - .git/
# - .env (create on server)
# - storage/logs/*.log
# - tests/
# - .github/
```

## Deployment Steps

### Step 1: Access cPanel

1. Log in to your GoDaddy account
2. Navigate to "My Products" → "Web Hosting" → "Manage"
3. Click "cPanel Admin" to access cPanel dashboard

### Step 2: Set PHP Version

1. In cPanel, go to "Server Settings" → "PHP Version" (or "Select PHP Version")
2. Select PHP 8.2 or higher
3. Click "Set as current"
4. Verify required extensions are enabled (see Prerequisites)

### Step 3: Create Database

1. In cPanel, go to "Databases" → "MySQL Databases"
2. Create a new database:
   - Enter database name (e.g., `strengthstoolbox_db`)
   - Click "Create Database"
   - Note the full database name (usually `username_strengthstoolbox_db`)

3. Create a database user:
   - Go to "MySQL Users" section
   - Enter username and strong password
   - Click "Create User"
   - Note the full username (usually `username_dbuser`)

4. Add user to database:
   - In "Add User to Database" section
   - Select the user and database
   - Click "Add"
   - Grant "ALL PRIVILEGES"
   - Click "Make Changes"

5. **Save these credentials** for `.env` file configuration

### Step 4: Upload Application Files

#### Option A: Via cPanel File Manager

1. Navigate to "Files" → "File Manager"
2. Go to `public_html/` directory (or subdirectory if using one)
3. Upload files:
   - **Method 1**: Upload ZIP file and extract
     - Compress project files (excluding node_modules, .git, .env)
     - Upload ZIP to `public_html/`
     - Right-click ZIP → "Extract"
   - **Method 2**: Upload files individually via drag-and-drop

#### Option B: Via FTP/SFTP

1. Get FTP credentials from cPanel → "FTP Accounts"
2. Use FTP client (FileZilla, WinSCP, etc.)
3. Connect to server
4. Upload all files to `public_html/` (or appropriate directory)

#### Option C: Via Git (if SSH access available)

```bash
# Via SSH terminal
cd ~/public_html
git clone https://github.com/kumbirai/the-strengths-toolbox.git .
# Or if deploying to subdirectory:
# git clone https://github.com/kumbirai/the-strengths-toolbox.git thestrengthstoolbox
```

### Step 5: Configure File Structure

For GoDaddy cPanel, you have two deployment options:

#### Option 1: Root Deployment (Recommended for main domain)

If deploying to your main domain (`yourdomain.com`):

1. Laravel's `public/` directory contents should be in `public_html/`
2. All other Laravel directories (app, bootstrap, config, etc.) should be one level up from `public_html/`

**Structure:**
```
/home/username/
├── public_html/          # Document root (Laravel public/ contents)
│   ├── index.php
│   ├── .htaccess
│   ├── build/            # Built assets
│   └── ...
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── storage/
├── vendor/
├── .env
└── composer.json
```

**To achieve this:**
- Move contents of `public/` to `public_html/`
- Update `public_html/index.php` paths (see Step 6)

#### Option 2: Subdirectory Deployment

If deploying to a subdirectory (`yourdomain.com/app`):

1. Create subdirectory in `public_html/` (e.g., `thestrengthstoolbox`)
2. Upload entire Laravel application to subdirectory
3. Configure `.htaccess` to point to subdirectory

### Step 6: Update index.php Paths

Edit `public_html/index.php` (or `public_html/thestrengthstoolbox/public/index.php` for subdirectory):

**For Root Deployment:**
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

**For Subdirectory Deployment:**
```php
// Paths should point to parent directory
require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
```

### Step 7: Create .env File

1. In cPanel File Manager, navigate to application root (one level up from `public_html/`)
2. Create new file named `.env`
3. Copy contents from `.env.example` (if available) or create new file
4. Configure with your GoDaddy-specific values:

```env
APP_NAME="The Strengths Toolbox"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_strengthstoolbox_db
DB_USERNAME=username_dbuser
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=relay-hosting.secureserver.net
MAIL_PORT=25
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=500

GOOGLE_ANALYTICS_ID=your_ga_id
```

**Important Notes:**
- `DB_HOST` is usually `localhost` on GoDaddy
- `DB_DATABASE` and `DB_USERNAME` must include the full names (with username prefix)
- GoDaddy mail server: `relay-hosting.secureserver.net` (port 25, no encryption for shared hosting)
- Set `APP_DEBUG=false` for production

### Step 8: Install Composer Dependencies

**Via SSH (Recommended):**

1. Enable SSH access in cPanel if not already enabled:
   - Go to "Security" → "SSH Access"
   - Generate SSH key or use password authentication

2. Connect via SSH terminal
3. Navigate to application directory:
   ```bash
   cd ~/public_html
   # Or for subdirectory:
   # cd ~/public_html/thestrengthstoolbox
   ```

4. Install Composer locally (if not available globally):
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install --no-dev --optimize-autoloader
   ```

5. Or if Composer is available globally:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

**Via cPanel Terminal:**
- Use cPanel → "Advanced" → "Terminal" (if available)
- Run the same commands as above

### Step 9: Generate Application Key

```bash
php artisan key:generate
```

This will automatically update the `APP_KEY` in your `.env` file.

### Step 10: Set File Permissions

**Via SSH or cPanel File Manager:**

```bash
# Navigate to application root
cd ~/public_html

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage and cache writable
chmod -R 775 storage bootstrap/cache

# Secure .env file
chmod 600 .env
```

**Via cPanel File Manager:**
- Right-click `storage/` → "Change Permissions" → Set to `775`
- Right-click `bootstrap/cache/` → "Change Permissions" → Set to `775`
- Right-click `.env` → "Change Permissions" → Set to `600`

### Step 11: Create Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public` for uploaded files.

### Step 12: Run Database Migrations

```bash
php artisan migrate --force
```

The `--force` flag is required in production environment.

### Step 13: Seed Database (Optional)

```bash
php artisan db:seed --class=ProductionContentSeeder
```

### Step 14: Optimize Application

```bash
# Clear all caches first
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 15: Configure .htaccess

Ensure `public_html/.htaccess` (or `public_html/thestrengthstoolbox/public/.htaccess`) contains proper Laravel rewrite rules. The file should already be present from the uploaded files.

Verify it includes:
- mod_rewrite rules for Laravel routing
- Security headers
- HTTPS redirect (if SSL is configured)

### Step 16: Configure SSL/HTTPS

1. In cPanel, go to "Security" → "SSL/TLS Status"
2. For your domain, click "Run AutoSSL" or "Install SSL Certificate"
3. Select "Let's Encrypt" (free SSL)
4. Click "Install"
5. Wait for certificate installation (may take a few minutes)
6. Verify HTTPS is working by visiting `https://yourdomain.com`

### Step 17: Verify Deployment

1. **Test Homepage**: Visit `https://yourdomain.com`
2. **Check Health Endpoint**: Visit `https://yourdomain.com/health`
3. **Test Forms**: Submit contact form
4. **Check Admin Panel**: Access admin routes
5. **Verify Assets**: Ensure CSS/JS/images load correctly
6. **Check Logs**: Review `storage/logs/laravel.log` for errors

## Post-Deployment Configuration

### Set Up Automated Backups

1. In cPanel, go to "Files" → "Backup Wizard"
2. Configure automated backups:
   - Full backups (weekly recommended)
   - Database-only backups (daily recommended)
3. Download backups regularly and store off-server

### Configure Cron Jobs (if needed)

If your application uses scheduled tasks:

1. In cPanel, go to "Advanced" → "Cron Jobs"
2. Add cron job for Laravel scheduler:
   ```bash
   * * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
   (Adjust path based on your deployment structure)

### Monitor Logs

Regularly check:
- Application logs: `storage/logs/laravel.log`
- Error logs: cPanel → "Metrics" → "Errors"
- Access logs: cPanel → "Metrics" → "Raw Access"

## Troubleshooting

### Issue: 500 Internal Server Error

**Solutions:**
1. Check file permissions (Step 10)
2. Verify `.env` file exists and is configured correctly
3. Check `storage/logs/laravel.log` for specific errors
4. Verify PHP version is 8.2+
5. Check Apache error logs in cPanel → "Metrics" → "Errors"

### Issue: Database Connection Error

**Solutions:**
1. Verify database credentials in `.env` match cPanel MySQL database
2. Ensure database name includes username prefix (e.g., `username_dbname`)
3. Verify database user has proper permissions
4. Check `DB_HOST` is set to `localhost`

### Issue: Assets Not Loading (CSS/JS)

**Solutions:**
1. Verify `npm run build` was run and `public/build/` directory exists
2. Check file permissions on `public/build/`
3. Clear browser cache
4. Verify `.htaccess` allows access to static files
5. Check `APP_URL` in `.env` matches your domain

### Issue: Permission Denied Errors

**Solutions:**
1. Set `storage/` and `bootstrap/cache/` to 775 permissions
2. Verify web server user can write to these directories
3. Check `.env` file permissions (should be 600)

### Issue: Composer Not Found

**Solutions:**
1. Install Composer locally in home directory:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install --no-dev --optimize-autoloader
   ```
2. Use full path: `php ~/composer.phar install`

### Issue: Node.js Build Fails

**Solutions:**
1. Build assets locally before deployment (recommended)
2. If Node.js is available, verify version is LTS 24
3. Check memory limits if build fails
4. Upload pre-built `public/build/` directory

### Issue: mod_rewrite Not Working

**Solutions:**
1. Verify `.htaccess` file exists in `public_html/`
2. Check Apache mod_rewrite is enabled (contact GoDaddy support if needed)
3. Verify `.htaccess` contains proper Laravel rewrite rules

### Issue: SSL Certificate Issues

**Solutions:**
1. Wait 24-48 hours for AutoSSL to process
2. Manually install Let's Encrypt certificate via cPanel
3. Verify domain DNS is pointing to GoDaddy
4. Check SSL/TLS Status in cPanel for errors

## Maintenance Procedures

### Updating the Application

1. **Enable Maintenance Mode:**
   ```bash
   php artisan down --message="Updating application..."
   ```

2. **Update Code:**
   - Via Git: `git pull origin main`
   - Via FTP: Upload changed files

3. **Update Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Run Migrations:**
   ```bash
   php artisan migrate --force
   ```

5. **Rebuild Assets** (if frontend changed):
   - Build locally: `npm run build`
   - Upload `public/build/` directory

6. **Clear and Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Disable Maintenance Mode:**
   ```bash
   php artisan up
   ```

### Regular Maintenance Tasks

**Weekly:**
- Check application logs for errors
- Verify backups are running
- Monitor disk space usage

**Monthly:**
- Review and update dependencies
- Check for security updates
- Review performance metrics

**Quarterly:**
- Full security audit
- Performance optimization review
- Backup restoration test

## GoDaddy-Specific Considerations

### Resource Limitations

- **Memory Limits**: Shared hosting has memory constraints; monitor usage
- **Execution Time**: Long-running scripts may timeout; optimize queries
- **File Upload Size**: Limited by PHP settings; adjust if needed
- **Database Size**: Monitor database size; optimize tables regularly

### Support and Documentation

- GoDaddy Support: Available 24/7 via phone, chat, or ticket
- cPanel Documentation: Available in cPanel dashboard
- Laravel Documentation: https://laravel.com/docs

### When to Consider Upgrading

Consider upgrading to VPS or dedicated server if:
- Application requires long-running processes
- Need WebSocket support
- Require more control over server configuration
- Need guaranteed resources
- Application has high traffic

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] `.env` file permissions set to 600
- [ ] Storage and cache directories properly secured
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Security headers configured in `.htaccess`
- [ ] Regular backups configured
- [ ] File permissions set correctly
- [ ] Sensitive directories protected

## Performance Optimization

1. **Enable Caching:**
   - Config cache: `php artisan config:cache`
   - Route cache: `php artisan route:cache`
   - View cache: `php artisan view:cache`

2. **Optimize Assets:**
   - Build production assets: `npm run build`
   - Enable Gzip compression (via `.htaccess`)
   - Configure browser caching

3. **Database Optimization:**
   - Regular database maintenance
   - Optimize slow queries
   - Use database indexes

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [GoDaddy cPanel Help](https://www.godaddy.com/help/cpanel)
- [GoDaddy PHP Version Guide](https://www.godaddy.com/help/view-or-change-the-php-version-for-my-web-hosting-cpanel-16090)
- Application Architecture: `documentation/01-architecture/10-deployment-architecture.md`
- General Deployment Guide: `documentation/03-development/phase-04/DEPLOYMENT_GUIDE.md`

---

**Last Updated:** 2025-01-27  
**Application Version:** Laravel 12  
**GoDaddy Compatibility:** Shared Hosting, cPanel Linux
