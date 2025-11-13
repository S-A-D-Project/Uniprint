# UniPrint Frontend Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the enhanced UniPrint frontend system with all new components, real-time chat functionality, and modern development practices.

## Prerequisites

### System Requirements
- **PHP**: 8.1 or higher
- **Laravel**: 10.x
- **Node.js**: 16.x or higher (for testing and build tools)
- **Database**: PostgreSQL 13+ or MySQL 8+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for Pusher WebSocket connections

### External Services
- **Pusher Account**: For real-time chat functionality
- **CDN**: Optional but recommended for static assets

## Pre-Deployment Checklist

### 1. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Configure database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Configure Pusher (Required for chat)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

# Configure broadcasting
BROADCAST_DRIVER=pusher

# Configure cache (recommended for production)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Configure sessions
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Configure mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### 2. Pusher Configuration

1. **Create Pusher Account**:
   - Visit https://pusher.com/
   - Create a new app
   - Note down App ID, Key, Secret, and Cluster

2. **Configure Channels**:
   - Enable client events
   - Enable presence channels
   - Set up authentication endpoint: `/api/chat/pusher/auth`

3. **Test Configuration**:
   ```bash
   php artisan tinker
   >>> \App\Services\PusherService::test()
   ```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Create storage link
php artisan storage:link
```

### 4. Dependencies Installation

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies (for testing)
npm install

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Deployment Steps

### 1. File Upload and Permissions

```bash
# Upload files to server
rsync -avz --exclude-from='.gitignore' ./ user@server:/var/www/uniprint/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/uniprint
sudo chmod -R 755 /var/www/uniprint
sudo chmod -R 775 /var/www/uniprint/storage
sudo chmod -R 775 /var/www/uniprint/bootstrap/cache
```

### 2. Web Server Configuration

#### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName uniprint.yourdomain.com
    DocumentRoot /var/www/uniprint/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    # Compression
    LoadModule deflate_module modules/mod_deflate.so
    <Location />
        SetOutputFilter DEFLATE
        SetEnvIfNoCase Request_URI \
            \.(?:gif|jpe?g|png|ico)$ no-gzip dont-vary
        SetEnvIfNoCase Request_URI \
            \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
    </Location>
    
    # Cache Control
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 year"
    </FilesMatch>
    
    # Laravel Configuration
    <Directory /var/www/uniprint/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/uniprint_error.log
    CustomLog ${APACHE_LOG_DIR}/uniprint_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name uniprint.yourdomain.com;
    root /var/www/uniprint/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    
    # Security Headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # Cache Control
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # WebSocket Support for Pusher
    location /app/ {
        proxy_pass https://ws-mt1.pusher.com;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }
    
    access_log /var/log/nginx/uniprint_access.log;
    error_log /var/log/nginx/uniprint_error.log;
}
```

### 3. Queue Configuration (for background jobs)

```bash
# Install supervisor
sudo apt-get install supervisor

# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/uniprint-worker.conf
```

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

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start uniprint-worker:*
```

### 4. Cron Jobs

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/uniprint && php artisan schedule:run >> /dev/null 2>&1
```

## Frontend Asset Optimization

### 1. CSS Optimization

```bash
# Minify CSS files
find public/css -name "*.css" -exec sh -c 'csso "$1" --output "${1%.css}.min.css"' _ {} \;

# Update references in templates to use minified versions
```

### 2. JavaScript Optimization

```bash
# Minify JavaScript files
find public/js -name "*.js" -exec sh -c 'terser "$1" --compress --mangle --output "${1%.js}.min.js"' _ {} \;
```

### 3. Image Optimization

```bash
# Install optimization tools
sudo apt-get install jpegoptim optipng

# Optimize images
find public -name "*.jpg" -exec jpegoptim --strip-all {} \;
find public -name "*.png" -exec optipng -o2 {} \;
```

## Testing Deployment

### 1. Functionality Tests

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test Pusher connection
php artisan tinker
>>> \App\Services\PusherService::test();

# Test cache
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

### 2. Frontend Tests

```bash
# Run JavaScript tests
npm test

# Test chat functionality
# Visit /chat and /business/chat
# Send messages between different user types
# Verify real-time delivery
```

### 3. Performance Tests

```bash
# Test page load speeds
curl -w "@curl-format.txt" -o /dev/null -s "https://uniprint.yourdomain.com"

# Test API endpoints
curl -X POST "https://uniprint.yourdomain.com/api/chat/messages" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_token" \
  -d '{"conversation_id":"test","message":"test"}'
```

## Monitoring and Maintenance

### 1. Log Monitoring

```bash
# Monitor Laravel logs
tail -f /var/www/uniprint/storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/uniprint_error.log
tail -f /var/log/apache2/uniprint_error.log
```

### 2. Performance Monitoring

```bash
# Monitor system resources
htop
iotop
netstat -tulpn

# Monitor database performance
# PostgreSQL
SELECT * FROM pg_stat_activity;

# MySQL
SHOW PROCESSLIST;
```

### 3. Backup Strategy

```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
pg_dump uniprint > /backups/uniprint_$DATE.sql
find /backups -name "uniprint_*.sql" -mtime +7 -delete

# File backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf /backups/uniprint_files_$DATE.tar.gz /var/www/uniprint --exclude=/var/www/uniprint/storage/logs
find /backups -name "uniprint_files_*.tar.gz" -mtime +7 -delete
```

## Security Considerations

### 1. File Permissions

```bash
# Secure file permissions
find /var/www/uniprint -type f -exec chmod 644 {} \;
find /var/www/uniprint -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/uniprint/storage
chmod -R 775 /var/www/uniprint/bootstrap/cache
```

### 2. Environment Security

```bash
# Secure .env file
chmod 600 /var/www/uniprint/.env
chown www-data:www-data /var/www/uniprint/.env
```

### 3. Firewall Configuration

```bash
# UFW configuration
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 'Apache Full'
sudo ufw enable
```

## Troubleshooting

### Common Issues

1. **Chat not working**:
   - Check Pusher credentials
   - Verify SSL certificate
   - Check browser console for WebSocket errors

2. **Slow page loads**:
   - Enable caching (Redis/Memcached)
   - Optimize database queries
   - Enable compression

3. **File upload errors**:
   - Check PHP upload limits
   - Verify storage permissions
   - Check disk space

4. **Session issues**:
   - Verify session driver configuration
   - Check Redis/database connectivity
   - Clear session storage

### Debug Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check system status
php artisan about

# Test specific features
php artisan tinker
>>> \App\Models\User::count();
>>> \App\Services\PusherService::test();
```

## Performance Optimization

### 1. Database Optimization

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_chat_messages_conversation_id ON chat_messages(conversation_id);
CREATE INDEX idx_chat_messages_created_at ON chat_messages(created_at);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
```

### 2. Laravel Optimization

```bash
# Optimize autoloader
composer dump-autoload --optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize for production
php artisan optimize
```

### 3. Frontend Optimization

```html
<!-- Preload critical resources -->
<link rel="preload" href="/css/app.css" as="style">
<link rel="preload" href="/js/app.js" as="script">

<!-- Use CDN for external libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
```

## Conclusion

Following this deployment guide ensures a secure, performant, and maintainable UniPrint frontend system. The enhanced chat functionality with Pusher integration provides real-time communication capabilities, while the new component library and state management system offer a solid foundation for future development.

Regular monitoring, backups, and maintenance are essential for optimal system performance. The testing framework ensures code quality and reliability as the system evolves.

For support and updates, refer to the project documentation and maintain regular backups of both database and application files.
