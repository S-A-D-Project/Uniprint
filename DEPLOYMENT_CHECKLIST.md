# UniPrint Deployment Checklist

Use this checklist to ensure a smooth deployment to production.

## Pre-Deployment

### Code Preparation

- [ ] All features tested locally
- [ ] All tests passing (`php artisan test`)
- [ ] Code reviewed and approved
- [ ] No debug statements (dd, dump, console.log)
- [ ] No commented-out code
- [ ] Dependencies updated (`composer update`, `npm update`)
- [ ] Changelog updated
- [ ] Version tagged in git

### Environment Configuration

- [ ] `.env` file configured for production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` set to production domain
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials configured
- [ ] Mail configuration set up
- [ ] Pusher credentials configured (if using real-time features)
- [ ] All API keys and secrets set
- [ ] Logging configured appropriately

### Security

- [ ] All default passwords changed
- [ ] Database user has minimum required privileges
- [ ] SSL/TLS certificate installed
- [ ] HTTPS enforced
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] CSRF protection enabled (default in Laravel)
- [ ] XSS protection enabled
- [ ] SQL injection protection verified (use Eloquent)
- [ ] File upload validation implemented
- [ ] Security headers configured

### Database

- [ ] Production database created
- [ ] Database user created with proper privileges
- [ ] Database connection tested
- [ ] Migrations ready to run
- [ ] Seeders prepared (if needed)
- [ ] Backup strategy planned
- [ ] Database indexes optimized

### Server Requirements

- [ ] PHP 8.2+ installed
- [ ] Required PHP extensions installed
- [ ] Composer installed
- [ ] Node.js and NPM installed
- [ ] Web server configured (Apache/Nginx)
- [ ] PostgreSQL/MySQL installed and running
- [ ] Sufficient disk space (min 1GB)
- [ ] Sufficient RAM (min 1GB)
- [ ] Firewall configured

## Deployment Steps

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y  # Ubuntu/Debian
# or
sudo dnf update -y  # CentOS/RHEL

# Install dependencies (if not already installed)
# See INSTALLATION.md for platform-specific instructions
```

### 2. Clone Repository

```bash
# Clone to web directory
cd /var/www
sudo git clone <repository-url> uniprint
cd uniprint

# Set ownership
sudo chown -R www-data:www-data /var/www/uniprint
```

### 3. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm ci --production

# Build assets
npm run build
```

### 4. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env

# Generate application key
php artisan key:generate
```

### 5. Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache

# For SELinux (CentOS/RHEL)
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

### 6. Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

### 7. Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Create storage link
php artisan storage:link
```

### 8. Configure Web Server

#### Apache

Create virtual host (`/etc/apache2/sites-available/uniprint.conf`):

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/uniprint/public

    <Directory /var/www/uniprint/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/uniprint-error.log
    CustomLog ${APACHE_LOG_DIR}/uniprint-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite uniprint.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Nginx

Create server block (`/etc/nginx/sites-available/uniprint`):

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/uniprint/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/uniprint /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. Configure SSL/TLS

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # Apache
# or
sudo apt install certbot python3-certbot-nginx   # Nginx

# Obtain certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com  # Apache
# or
sudo certbot --nginx -d your-domain.com -d www.your-domain.com   # Nginx

# Test auto-renewal
sudo certbot renew --dry-run
```

### 10. Setup Queue Workers

Create supervisor configuration (`/etc/supervisor/conf.d/uniprint-worker.conf`):

```ini
[program:uniprint-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/uniprint/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/uniprint/storage/logs/worker.log
stopwaitsecs=3600
```

Start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start uniprint-worker:*
```

### 11. Setup Scheduled Tasks

Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add line:
```
* * * * * cd /var/www/uniprint && php artisan schedule:run >> /dev/null 2>&1
```

### 12. Configure Logging

Edit `config/logging.php` or set in `.env`:
```env
LOG_CHANNEL=stack
LOG_LEVEL=error
```

Setup log rotation (`/etc/logrotate.d/uniprint`):
```
/var/www/uniprint/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 www-data www-data
    sharedscripts
}
```

## Post-Deployment

### Verification

- [ ] Website accessible via domain
- [ ] HTTPS working correctly
- [ ] All pages loading without errors
- [ ] Database connections working
- [ ] File uploads working
- [ ] Email sending working
- [ ] Real-time chat working (if configured)
- [ ] Queue workers running
- [ ] Scheduled tasks running
- [ ] Logs being written correctly

### Testing

- [ ] Login functionality
- [ ] User registration
- [ ] Order creation
- [ ] Payment processing (if applicable)
- [ ] File uploads
- [ ] Search functionality
- [ ] Admin panel access
- [ ] API endpoints (if applicable)
- [ ] Mobile responsiveness

### Monitoring Setup

- [ ] Server monitoring configured
- [ ] Application monitoring configured
- [ ] Error tracking configured (e.g., Sentry)
- [ ] Uptime monitoring configured
- [ ] Performance monitoring configured
- [ ] Log monitoring configured
- [ ] Backup monitoring configured

### Backup Configuration

- [ ] Database backup script created
- [ ] File backup script created
- [ ] Backup schedule configured
- [ ] Backup restoration tested
- [ ] Off-site backup configured
- [ ] Backup retention policy set

### Documentation

- [ ] Deployment notes documented
- [ ] Server credentials documented (securely)
- [ ] API documentation updated
- [ ] User documentation updated
- [ ] Admin documentation updated

## Maintenance

### Regular Tasks

**Daily:**
- [ ] Check error logs
- [ ] Monitor server resources
- [ ] Check backup status

**Weekly:**
- [ ] Review security logs
- [ ] Check disk space
- [ ] Review performance metrics
- [ ] Test backup restoration

**Monthly:**
- [ ] Update dependencies
- [ ] Review and rotate logs
- [ ] Security audit
- [ ] Performance optimization
- [ ] Database optimization

### Update Procedure

```bash
# 1. Backup
./scripts/backup-database.sh
tar -czf backup-files.tar.gz /var/www/uniprint

# 2. Enable maintenance mode
php artisan down

# 3. Pull latest code
git pull origin main

# 4. Update dependencies
composer install --optimize-autoloader --no-dev
npm ci --production
npm run build

# 5. Run migrations
php artisan migrate --force

# 6. Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo supervisorctl restart uniprint-worker:*

# 8. Disable maintenance mode
php artisan up

# 9. Verify deployment
curl -I https://your-domain.com
```

## Rollback Procedure

If deployment fails:

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Restore database
psql -U uniprint_user uniprint < backup.sql

# 3. Restore files
tar -xzf backup-files.tar.gz -C /

# 4. Revert code
git reset --hard <previous-commit-hash>

# 5. Reinstall dependencies
composer install --optimize-autoloader --no-dev
npm ci --production
npm run build

# 6. Clear caches
php artisan optimize:clear

# 7. Disable maintenance mode
php artisan up
```

## Emergency Contacts

- **System Administrator**: [contact info]
- **Database Administrator**: [contact info]
- **Lead Developer**: [contact info]
- **Hosting Provider Support**: [contact info]
- **SSL Certificate Provider**: [contact info]

## Useful Commands

```bash
# Check application status
php artisan about

# View logs
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:work --once

# Check scheduled tasks
php artisan schedule:list

# Clear all caches
php artisan optimize:clear

# Check database connection
php artisan db:show

# Monitor queue
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

## Security Checklist

- [ ] Firewall configured (allow only 80, 443, 22)
- [ ] SSH key authentication enabled
- [ ] Root login disabled
- [ ] Fail2ban installed and configured
- [ ] Database not accessible from internet
- [ ] `.env` file not web-accessible
- [ ] Directory listing disabled
- [ ] Error messages don't reveal sensitive info
- [ ] File upload restrictions in place
- [ ] Rate limiting configured
- [ ] Security headers configured
- [ ] Regular security updates scheduled

## Performance Checklist

- [ ] OPcache enabled
- [ ] Redis/Memcached configured (optional)
- [ ] CDN configured for static assets
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Database queries optimized
- [ ] Images optimized
- [ ] CSS/JS minified
- [ ] HTTP/2 enabled
- [ ] Database indexes created

---

**Deployment Date**: _______________

**Deployed By**: _______________

**Version**: _______________

**Notes**: 
_______________________________________________
_______________________________________________
_______________________________________________

---

**Congratulations on your deployment! 🎉**

For issues, see [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)
