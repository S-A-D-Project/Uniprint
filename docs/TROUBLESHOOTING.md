# UniPrint Troubleshooting Guide

This guide helps you resolve common issues when installing or running UniPrint.

## Table of Contents

- [Installation Issues](#installation-issues)
- [Database Issues](#database-issues)
- [Permission Issues](#permission-issues)
- [Dependency Issues](#dependency-issues)
- [Runtime Errors](#runtime-errors)
- [Performance Issues](#performance-issues)
- [Platform-Specific Issues](#platform-specific-issues)

---

## Installation Issues

### Issue: "composer: command not found"

**Cause:** Composer is not installed or not in system PATH.

**Solution:**

**Windows:**
1. Download [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Run installer
3. Restart terminal

**macOS/Linux:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Issue: "php: command not found"

**Cause:** PHP is not installed or not in system PATH.

**Solution:**

**Windows:**
1. Add PHP directory to PATH: `C:\php` or `C:\xampp\php`
2. Restart terminal

**macOS:**
```bash
brew install php@8.2
brew link php@8.2 --force
```

**Linux:**
```bash
sudo apt install php8.2-cli  # Ubuntu/Debian
sudo dnf install php-cli      # CentOS/RHEL
```

### Issue: "npm: command not found"

**Cause:** Node.js/NPM is not installed.

**Solution:**

Download and install from [nodejs.org](https://nodejs.org/)

Or use package manager:
```bash
# macOS
brew install node

# Linux
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## Database Issues

### Issue: "SQLSTATE[HY000] [2002] Connection refused"

**Cause:** Database server is not running or wrong connection details.

**Solution:**

1. **Check if database is running:**

```bash
# PostgreSQL
sudo systemctl status postgresql  # Linux
brew services list                # macOS
services.msc                      # Windows (look for postgresql)

# MySQL
sudo systemctl status mysql       # Linux
brew services list                # macOS
```

2. **Start database if stopped:**

```bash
# PostgreSQL
sudo systemctl start postgresql   # Linux
brew services start postgresql    # macOS

# MySQL
sudo systemctl start mysql        # Linux
brew services start mysql         # macOS
```

3. **Verify connection details in `.env`:**
```env
DB_HOST=127.0.0.1
DB_PORT=5432          # PostgreSQL default
# DB_PORT=3306        # MySQL default
DB_DATABASE=uniprint
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. **Test connection manually:**
```bash
# PostgreSQL
psql -h 127.0.0.1 -U your_username -d uniprint

# MySQL
mysql -h 127.0.0.1 -u your_username -p uniprint
```

### Issue: "SQLSTATE[42P01]: Undefined table"

**Cause:** Database migrations haven't been run.

**Solution:**
```bash
php artisan migrate
# or
php artisan migrate:fresh --seed
```

### Issue: "SQLSTATE[42000]: Access denied"

**Cause:** Wrong database credentials or insufficient permissions.

**Solution:**

1. **Verify credentials in `.env`**

2. **Grant proper permissions:**

**PostgreSQL:**
```sql
psql -U postgres
GRANT ALL PRIVILEGES ON DATABASE uniprint TO your_username;
GRANT ALL ON SCHEMA public TO your_username;
\q
```

**MySQL:**
```sql
mysql -u root -p
GRANT ALL PRIVILEGES ON uniprint.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Issue: "SQLSTATE[22P02]: Invalid text representation"

**Cause:** Data type mismatch (usually UUID vs integer).

**Solution:**

This was fixed in the latest version. Run:
```bash
php artisan migrate:fresh --seed
```

---

## Permission Issues

### Issue: "Permission denied" on storage or bootstrap/cache

**Cause:** Web server doesn't have write permissions.

**Solution:**

**Linux/macOS:**
```bash
# Set ownership
sudo chown -R $USER:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache

# For SELinux (CentOS/RHEL)
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

**Windows:**
```cmd
# Run as Administrator
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

### Issue: "failed to open stream: Permission denied"

**Cause:** Laravel can't write to log files or cache.

**Solution:**
```bash
# Linux/macOS
sudo chmod -R 775 storage/logs
sudo chown -R $USER:www-data storage/logs

# Windows
icacls storage\logs /grant Users:F /T
```

---

## Dependency Issues

### Issue: "Class 'X' not found"

**Cause:** Autoloader not updated or missing dependency.

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
```

### Issue: "Your requirements could not be resolved"

**Cause:** Composer dependency conflicts.

**Solution:**
```bash
# Update Composer
composer self-update

# Clear cache
composer clear-cache

# Try installing with different flags
composer install --ignore-platform-reqs
# or
composer update --with-all-dependencies
```

### Issue: NPM install fails with "EACCES" error

**Cause:** Permission issues with global NPM packages.

**Solution:**

**Option 1: Fix NPM permissions**
```bash
mkdir ~/.npm-global
npm config set prefix '~/.npm-global'
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

**Option 2: Use sudo (not recommended)**
```bash
sudo npm install
```

**Option 3: Clear cache and retry**
```bash
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

---

## Runtime Errors

### Issue: "419 Page Expired" on form submission

**Cause:** CSRF token mismatch or session issues.

**Solution:**

1. **Clear cache:**
```bash
php artisan cache:clear
php artisan config:clear
```

2. **Check session configuration in `.env`:**
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

3. **Run session table migration:**
```bash
php artisan migrate
```

4. **Clear browser cookies and try again**

### Issue: "500 Internal Server Error"

**Cause:** Various reasons - check logs.

**Solution:**

1. **Enable debug mode in `.env`:**
```env
APP_DEBUG=true
```

2. **Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

3. **Check web server logs:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

4. **Common fixes:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Issue: "Vite manifest not found"

**Cause:** Assets haven't been built.

**Solution:**
```bash
npm run build
# or for development
npm run dev
```

### Issue: "Mix manifest does not exist"

**Cause:** Old Laravel Mix configuration (shouldn't occur in Laravel 12).

**Solution:**
```bash
npm run build
```

---

## Performance Issues

### Issue: Application is slow

**Solutions:**

1. **Enable caching:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Optimize Composer autoloader:**
```bash
composer install --optimize-autoloader --no-dev
```

3. **Use OPcache (add to php.ini):**
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

4. **Use Redis for caching (optional):**
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Issue: High memory usage

**Solution:**

1. **Increase PHP memory limit in php.ini:**
```ini
memory_limit = 512M
```

2. **Optimize database queries**

3. **Use queue workers for heavy tasks:**
```bash
php artisan queue:work
```

---

## Platform-Specific Issues

### Windows Issues

#### Issue: "The system cannot find the path specified"

**Solution:**
- Use forward slashes in paths: `/` instead of `\`
- Or escape backslashes: `C:\\path\\to\\file`

#### Issue: Symlink creation fails

**Solution:**
Run terminal as Administrator:
```cmd
php artisan storage:link
```

#### Issue: Port already in use

**Solution:**
```cmd
# Find process using port 8000
netstat -ano | findstr :8000

# Kill process
taskkill /PID <PID> /F

# Or use different port
php artisan serve --port=8080
```

### macOS Issues

#### Issue: "Operation not permitted" even with sudo

**Solution:**

Disable System Integrity Protection (SIP) temporarily:
1. Restart in Recovery Mode (Cmd+R)
2. Open Terminal
3. Run: `csrutil disable`
4. Restart normally
5. After fixing issue, re-enable: `csrutil enable`

#### Issue: Homebrew PHP conflicts

**Solution:**
```bash
brew unlink php
brew link php@8.2 --force --overwrite
```

### Linux Issues

#### Issue: SELinux blocking file access (CentOS/RHEL)

**Solution:**
```bash
# Set proper context
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache

# Or disable SELinux temporarily (not recommended for production)
sudo setenforce 0
```

#### Issue: AppArmor blocking operations (Ubuntu)

**Solution:**
```bash
# Check AppArmor status
sudo aa-status

# Put profile in complain mode
sudo aa-complain /etc/apparmor.d/usr.sbin.php-fpm*
```

---

## Getting More Help

### Enable Debug Mode

Edit `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx

# PHP-FPM logs
tail -f /var/log/php-fpm/error.log
```

### Run System Check

```bash
php scripts/check-requirements.php
```

### Test Database Connection

```bash
php artisan db:show
php artisan tinker
>>> DB::connection()->getPdo();
```

### Clear All Caches

```bash
php artisan optimize:clear
# This runs:
# - cache:clear
# - config:clear
# - route:clear
# - view:clear
# - clear-compiled
```

### Reinstall Dependencies

```bash
# PHP dependencies
rm -rf vendor
composer install

# Node dependencies
rm -rf node_modules package-lock.json
npm install
```

---

## Still Having Issues?

1. Check [GitHub Issues](https://github.com/your-repo/issues)
2. Review [Laravel Documentation](https://laravel.com/docs)
3. Check [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)
4. Contact support: support@uniprint.com

---

**Remember:** Always backup your database and files before making major changes!
