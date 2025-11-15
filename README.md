# UniPrint - Online Printing Services Platform

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14+-blue.svg)](https://postgresql.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Table of Contents

- [About UniPrint](#about-uniprint)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Quick Start](#quick-start)
- [Detailed Installation](#detailed-installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## 🎯 About UniPrint

UniPrint is a comprehensive online printing services platform built with Laravel 12. It connects customers with printing businesses, enabling seamless order management, real-time chat communication, and efficient workflow tracking.

### Key Capabilities

- **Multi-tenant Architecture**: Support for multiple printing businesses
- **Real-time Chat**: Built-in messaging system using Pusher
- **Order Management**: Complete order lifecycle tracking
- **Product Customization**: Flexible product options and pricing
- **User Roles**: Admin, Business Owner, and Customer roles
- **Rating System**: Customer reviews and ratings
- **Design Upload**: File management for print designs

## ✨ Features

- 🏪 **Enterprise Management**: Multiple printing shops with individual profiles
- 📦 **Order Processing**: Complete order workflow from creation to delivery
- 💬 **Real-time Chat**: Customer-business communication
- 🎨 **Product Customization**: Dynamic pricing and customization options
- 📊 **Dashboard Analytics**: Business insights and reporting
- 🔐 **Role-based Access Control**: Secure multi-user system
- 📱 **Responsive Design**: Mobile-friendly interface
- 🔔 **Notifications**: Real-time order updates

## 💻 System Requirements

### Minimum Requirements

- **PHP**: 8.2 or higher
- **Composer**: 2.0 or higher
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher
- **Database**: PostgreSQL 14+ (recommended) or MySQL 8.0+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum (1GB recommended)
- **Disk Space**: 500MB minimum

### Required PHP Extensions

```bash
- php-mbstring
- php-xml
- php-curl
- php-zip
- php-pgsql (for PostgreSQL) or php-mysql (for MySQL)
- php-pdo
- php-tokenizer
- php-json
- php-bcmath
- php-fileinfo
- php-gd (for image processing)
```

### Supported Operating Systems

- ✅ Windows 10/11
- ✅ macOS 11+ (Big Sur and later)
- ✅ Linux (Ubuntu 20.04+, Debian 11+, CentOS 8+)

## 🚀 Quick Start

### One-Command Setup (After Prerequisites)

```bash
# Clone the repository
git clone <repository-url> uniprint
cd uniprint

# Run automated setup
php scripts/check-requirements.php
composer run-script setup
```

For detailed platform-specific instructions, see [INSTALLATION.md](INSTALLATION.md)

## 📦 Detailed Installation

### Step 1: Install Prerequisites

#### Windows

1. Download and install [PHP 8.2+](https://windows.php.net/download/)
2. Download and install [Composer](https://getcomposer.org/Composer-Setup.exe)
3. Download and install [Node.js](https://nodejs.org/)
4. Download and install [PostgreSQL](https://www.postgresql.org/download/windows/)

#### macOS

```bash
# Using Homebrew
brew install php@8.2
brew install composer
brew install node
brew install postgresql@14
brew services start postgresql@14
```

#### Linux (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-pgsql php8.2-pdo \
    php8.2-tokenizer php8.2-json php8.2-bcmath php8.2-fileinfo php8.2-gd
sudo apt install -y composer nodejs npm postgresql postgresql-contrib
```

### Step 2: Clone and Setup Project

```bash
# Clone repository
git clone <repository-url> uniprint
cd uniprint

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Configure Environment

Edit `.env` file with your settings:

```env
APP_NAME="UniPrint"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session Configuration
SESSION_DRIVER=database

# Queue Configuration
QUEUE_CONNECTION=database

# Pusher Configuration (for real-time chat)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### Step 4: Database Setup

```bash
# Create database (PostgreSQL)
psql -U postgres
CREATE DATABASE uniprint;
\q

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### Step 5: Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### Step 6: Start Application

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# In another terminal, start Vite dev server (for hot reload)
npm run dev
```

Visit `http://localhost:8000` in your browser.

## ⚙️ Configuration

### Database Configuration

The application supports multiple database systems:

#### PostgreSQL (Recommended)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

#### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniprint
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### SQLite (Development Only)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### Web Server Configuration

See [docs/WEB_SERVER_CONFIG.md](docs/WEB_SERVER_CONFIG.md) for Apache and Nginx configuration examples.

## 🗄️ Database Setup

### Creating the Database

#### PostgreSQL

```bash
# Using psql
psql -U postgres
CREATE DATABASE uniprint;
GRANT ALL PRIVILEGES ON DATABASE uniprint TO your_username;
\q
```

#### MySQL

```bash
# Using mysql
mysql -u root -p
CREATE DATABASE uniprint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON uniprint.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Fresh migration (drops all tables and re-migrates)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

### Seeding Data

The application includes comprehensive seeders:

```bash
# Seed all data
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=NewUsersSeeder
```

**Default Accounts Created:**

- **Admin**: username: `admin`, password: `admin123`
- **Business**: username: `quickprint`, password: `business123`
- **Customer**: username: `customer`, password: `customer123`

## 🏃 Running the Application

### Development Mode

```bash
# Option 1: Using Laravel's built-in server
php artisan serve

# Option 2: Using Composer script (runs server + queue + vite)
composer run dev

# Option 3: Manual (in separate terminals)
php artisan serve          # Terminal 1
php artisan queue:work     # Terminal 2
npm run dev                # Terminal 3
```

### Production Mode

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set up supervisor for queue workers (Linux)
sudo apt install supervisor
sudo nano /etc/supervisor/conf.d/uniprint-worker.conf
```

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for complete production deployment guide.

## 🧪 Testing

### System Requirements Check

```bash
# Check if your system meets all requirements
php scripts/check-requirements.php
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Database Connection Test

```bash
# Test database connection
php artisan db:show

# Test database with sample query
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::table('users')->count();
```

## 🚢 Deployment

For production deployment instructions, see:

- [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) - General deployment guide
- [docs/DOCKER.md](docs/DOCKER.md) - Docker deployment
- [docs/CLOUD_DEPLOYMENT.md](docs/CLOUD_DEPLOYMENT.md) - Cloud platforms (AWS, DigitalOcean, etc.)

## 🔧 Troubleshooting

### Common Issues

#### "Class not found" errors

```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

#### Database connection errors

1. Verify database credentials in `.env`
2. Ensure database server is running
3. Check firewall settings
4. Test connection: `php artisan db:show`

#### Permission errors (Linux/macOS)

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### NPM/Node errors

```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Getting Help

- Check [docs/TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) for detailed solutions
- Review Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: Set `APP_DEBUG=true` in `.env`

## 📚 Documentation

- [Installation Guide](INSTALLATION.md) - Detailed platform-specific installation
- [Configuration Guide](docs/CONFIGURATION.md) - Advanced configuration options
- [API Documentation](docs/API.md) - API endpoints and usage
- [Database Schema](docs/DATABASE_SCHEMA.md) - Database structure
- [Deployment Guide](docs/DEPLOYMENT.md) - Production deployment
- [Troubleshooting](docs/TROUBLESHOOTING.md) - Common issues and solutions

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guide](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with [Laravel 12](https://laravel.com)
- Real-time features powered by [Pusher](https://pusher.com)
- UI components from [Tailwind CSS](https://tailwindcss.com)

## 📞 Support

For support and questions:

- 📧 Email: support@uniprint.com
- 📖 Documentation: [docs/](docs/)
- 🐛 Issues: [GitHub Issues](https://github.com/your-repo/issues)

---

**Made with ❤️ for the printing industry**
