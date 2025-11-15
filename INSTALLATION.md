# UniPrint Installation Guide

This guide provides detailed, platform-specific instructions for installing and configuring UniPrint on Windows, macOS, and Linux systems.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Windows Installation](#windows-installation)
- [macOS Installation](#macos-installation)
- [Linux Installation](#linux-installation)
- [Post-Installation Setup](#post-installation-setup)
- [Verification](#verification)
- [Common Issues](#common-issues)

---

## Prerequisites

Before installing UniPrint, ensure you have:

- **Administrator/sudo access** on your system
- **Internet connection** for downloading dependencies
- **At least 1GB free disk space**
- **Basic command line knowledge**

---

## Windows Installation

### Step 1: Install PHP 8.2+

#### Option A: Using XAMPP (Recommended for Beginners)

1. Download [XAMPP](https://www.apachefriends.org/download.html) (PHP 8.2 version)
2. Run the installer and select:
   - Apache
   - PHP
   - MySQL (optional, if not using PostgreSQL)
3. Install to `C:\xampp`
4. Add PHP to PATH:
   - Open System Properties → Environment Variables
   - Edit `Path` variable
   - Add `C:\xampp\php`
5. Verify installation:
   ```cmd
   php -v
   ```

#### Option B: Standalone PHP Installation

1. Download PHP 8.2+ from [windows.php.net](https://windows.php.net/download/)
2. Extract to `C:\php`
3. Copy `php.ini-development` to `php.ini`
4. Edit `php.ini` and enable extensions:
   ```ini
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=openssl
   extension=pdo_pgsql
   extension=pgsql
   extension=zip
   ```
5. Add `C:\php` to PATH
6. Verify: `php -v`

### Step 2: Install Composer

1. Download [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Run installer (it will auto-detect PHP)
3. Verify installation:
   ```cmd
   composer --version
   ```

### Step 3: Install Node.js and NPM

1. Download [Node.js LTS](https://nodejs.org/) (includes NPM)
2. Run installer with default options
3. Verify installation:
   ```cmd
   node -v
   npm -v
   ```

### Step 4: Install PostgreSQL

1. Download [PostgreSQL 14+](https://www.postgresql.org/download/windows/)
2. Run installer:
   - Remember the superuser password
   - Default port: 5432
   - Install Stack Builder (optional)
3. Add PostgreSQL to PATH:
   - Add `C:\Program Files\PostgreSQL\14\bin` to PATH
4. Verify installation:
   ```cmd
   psql --version
   ```

### Step 5: Clone and Setup UniPrint

```cmd
# Clone repository
git clone <repository-url> uniprint
cd uniprint

# Install dependencies
composer install
npm install

# Setup environment
copy .env.example .env
php artisan key:generate

# Create database
psql -U postgres
CREATE DATABASE uniprint;
\q

# Configure .env file
notepad .env
# Update DB_USERNAME and DB_PASSWORD

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start server
php artisan serve
```

### Step 6: Configure Windows Firewall (Optional)

If you need to access the application from other devices:

```cmd
netsh advfirewall firewall add rule name="Laravel Dev Server" dir=in action=allow protocol=TCP localport=8000
```

---

## macOS Installation

### Step 1: Install Homebrew

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Step 2: Install PHP 8.2+

```bash
# Install PHP
brew install php@8.2

# Link PHP
brew link php@8.2 --force

# Verify installation
php -v

# Check enabled extensions
php -m
```

### Step 3: Install Required PHP Extensions

Most extensions come with Homebrew's PHP. If missing:

```bash
# Install additional extensions if needed
brew install php@8.2-pgsql
brew install php@8.2-gd
```

### Step 4: Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### Step 5: Install Node.js and NPM

```bash
# Install Node.js
brew install node

# Verify installation
node -v
npm -v
```

### Step 6: Install PostgreSQL

```bash
# Install PostgreSQL
brew install postgresql@14

# Start PostgreSQL service
brew services start postgresql@14

# Create database user (if needed)
createuser -s postgres

# Verify installation
psql --version
```

### Step 7: Clone and Setup UniPrint

```bash
# Clone repository
git clone <repository-url> uniprint
cd uniprint

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
psql postgres
CREATE DATABASE uniprint;
\q

# Configure .env file
nano .env
# Update DB_USERNAME and DB_PASSWORD

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:_www storage bootstrap/cache

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start server
php artisan serve
```

---

## Linux Installation

### Ubuntu/Debian

#### Step 1: Update System

```bash
sudo apt update
sudo apt upgrade -y
```

#### Step 2: Install PHP 8.2+

```bash
# Add PHP repository
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-fpm \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
    php8.2-pgsql php8.2-pdo php8.2-gd php8.2-bcmath \
    php8.2-tokenizer php8.2-fileinfo

# Verify installation
php -v
```

#### Step 3: Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

#### Step 4: Install Node.js and NPM

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installation
node -v
npm -v
```

#### Step 5: Install PostgreSQL

```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Start PostgreSQL service
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Verify installation
psql --version
```

#### Step 6: Configure PostgreSQL

```bash
# Switch to postgres user
sudo -u postgres psql

# Create database and user
CREATE DATABASE uniprint;
CREATE USER uniprint_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE uniprint TO uniprint_user;
\q
```

#### Step 7: Clone and Setup UniPrint

```bash
# Clone repository
git clone <repository-url> uniprint
cd uniprint

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure .env file
nano .env
# Update database credentials:
# DB_DATABASE=uniprint
# DB_USERNAME=uniprint_user
# DB_PASSWORD=your_secure_password

# Set proper permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start server
php artisan serve
```

### CentOS/RHEL/Fedora

#### Step 1: Install PHP 8.2+

```bash
# Enable EPEL and Remi repositories
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# Enable PHP 8.2 module
sudo dnf module reset php
sudo dnf module enable php:remi-8.2

# Install PHP and extensions
sudo dnf install -y php php-cli php-common php-mbstring php-xml \
    php-curl php-zip php-pgsql php-pdo php-gd php-bcmath \
    php-json php-tokenizer

# Verify installation
php -v
```

#### Step 2: Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version
```

#### Step 3: Install Node.js

```bash
# Install Node.js 18.x
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo dnf install -y nodejs

# Verify installation
node -v
npm -v
```

#### Step 4: Install PostgreSQL

```bash
# Install PostgreSQL
sudo dnf install -y postgresql-server postgresql-contrib

# Initialize database
sudo postgresql-setup --initdb

# Start and enable service
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

Follow the same setup steps as Ubuntu from Step 6 onwards.

---

## Post-Installation Setup

### 1. Environment Configuration

Edit `.env` file and configure:

```env
APP_NAME="UniPrint"
APP_ENV=production  # Change to 'production' for live sites
APP_DEBUG=false     # Set to 'false' in production
APP_URL=http://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Optional: Configure Pusher for real-time chat
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### 2. File Permissions (Linux/macOS)

```bash
# Set ownership
sudo chown -R $USER:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache

# For SELinux (CentOS/RHEL)
sudo chcon -R -t httpd_sys_rw_content_t storage bootstrap/cache
```

### 3. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 4. Setup Queue Worker (Production)

#### Using Supervisor (Linux)

```bash
# Install supervisor
sudo apt install supervisor  # Ubuntu/Debian
# or
sudo dnf install supervisor  # CentOS/RHEL

# Create configuration
sudo nano /etc/supervisor/conf.d/uniprint-worker.conf
```

Add this configuration:

```ini
[program:uniprint-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/uniprint/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/uniprint/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start uniprint-worker:*
```

#### Using Windows Task Scheduler

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger (At startup)
4. Action: Start a program
   - Program: `C:\php\php.exe`
   - Arguments: `C:\path\to\uniprint\artisan queue:work`

---

## Verification

### 1. Check System Requirements

```bash
php scripts/check-requirements.php
```

### 2. Test Database Connection

```bash
php artisan db:show
```

### 3. Run Application Tests

```bash
php artisan test
```

### 4. Access Application

Open browser and navigate to:
- Development: `http://localhost:8000`
- Production: `http://your-domain.com`

### 5. Test Login

Use default credentials:
- **Admin**: username: `admin`, password: `admin123`
- **Business**: username: `quickprint`, password: `business123`
- **Customer**: username: `customer`, password: `customer123`

---

## Common Issues

### Issue: "Class not found" errors

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

### Issue: Database connection refused

**Solution:**
1. Check if PostgreSQL is running:
   ```bash
   # Linux
   sudo systemctl status postgresql
   
   # macOS
   brew services list
   
   # Windows
   services.msc (look for postgresql service)
   ```
2. Verify credentials in `.env`
3. Test connection: `psql -U username -d uniprint`

### Issue: Permission denied (Linux/macOS)

**Solution:**
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Port 8000 already in use

**Solution:**
```bash
# Use different port
php artisan serve --port=8080

# Or find and kill process using port 8000
# Linux/macOS
lsof -ti:8000 | xargs kill -9

# Windows
netstat -ano | findstr :8000
taskkill /PID <PID> /F
```

### Issue: NPM install fails

**Solution:**
```bash
# Clear NPM cache
npm cache clean --force

# Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

### Issue: Composer install fails

**Solution:**
```bash
# Update Composer
composer self-update

# Clear Composer cache
composer clear-cache

# Install with verbose output
composer install -vvv
```

### Issue: Missing PHP extensions

**Solution:**

Check which extensions are missing:
```bash
php -m
```

Install missing extensions:
```bash
# Ubuntu/Debian
sudo apt install php8.2-<extension-name>

# CentOS/RHEL
sudo dnf install php-<extension-name>

# macOS
brew install php@8.2-<extension-name>

# Windows
# Edit php.ini and uncomment: extension=<extension-name>
```

---

## Next Steps

After successful installation:

1. Read [Configuration Guide](docs/CONFIGURATION.md) for advanced settings
2. Review [Security Best Practices](docs/SECURITY.md)
3. Setup [Web Server Configuration](docs/WEB_SERVER_CONFIG.md) for production
4. Configure [Backup Strategy](docs/BACKUP.md)
5. Review [Deployment Guide](docs/DEPLOYMENT.md) for production deployment

---

## Getting Help

If you encounter issues not covered here:

1. Check [Troubleshooting Guide](docs/TROUBLESHOOTING.md)
2. Review Laravel logs: `storage/logs/laravel.log`
3. Enable debug mode: Set `APP_DEBUG=true` in `.env`
4. Search [GitHub Issues](https://github.com/your-repo/issues)
5. Contact support: support@uniprint.com

---

**Installation complete! Welcome to UniPrint! 🎉**
