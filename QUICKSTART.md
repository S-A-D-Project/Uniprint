# UniPrint Quick Start Guide

Get UniPrint up and running in minutes!

## Prerequisites Check

Before starting, ensure you have:
- ✅ PHP 8.2 or higher
- ✅ Composer 2.0 or higher
- ✅ Node.js 18.x or higher
- ✅ PostgreSQL 14+ or MySQL 8.0+

**Not sure?** Run the requirements checker:
```bash
php scripts/check-requirements.php
```

---

## Installation Methods

Choose the method that works best for you:

### Method 1: Automated Setup (Recommended)

#### Windows
```cmd
git clone <repository-url> uniprint
cd uniprint
scripts\setup-windows.bat
```

#### macOS/Linux
```bash
git clone <repository-url> uniprint
cd uniprint
chmod +x scripts/setup-unix.sh
./scripts/setup-unix.sh
```

### Method 2: Manual Setup

```bash
# 1. Clone repository
git clone <repository-url> uniprint
cd uniprint

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database (edit .env file)
# Update these lines:
# DB_DATABASE=uniprint
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 5. Create database
# PostgreSQL:
psql -U postgres -c "CREATE DATABASE uniprint;"

# MySQL:
mysql -u root -p -e "CREATE DATABASE uniprint;"

# 6. Run migrations
php artisan migrate --seed

# 7. Build assets
npm run build

# 8. Start server
php artisan serve
```

### Method 3: One-Command Setup

```bash
git clone <repository-url> uniprint && cd uniprint && composer run-script setup
```

---

## Post-Installation

### 1. Access the Application

Open your browser and visit:
```
http://localhost:8000
```

### 2. Login with Default Accounts

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Business Account:**
- Username: `quickprint`
- Password: `business123`

**Customer Account:**
- Username: `customer`
- Password: `customer123`

### 3. Change Default Passwords

⚠️ **Important:** Change all default passwords immediately!

```bash
php artisan tinker
>>> $user = User::where('email', 'admin@uniprint.com')->first();
>>> $user->password = bcrypt('your_new_password');
>>> $user->save();
```

---

## Common Tasks

### Start Development Server

```bash
# Option 1: Simple
php artisan serve

# Option 2: With queue worker and Vite
composer run dev

# Option 3: Custom port
php artisan serve --port=8080
```

### Run Database Migrations

```bash
# Run migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# With seeders
php artisan migrate:fresh --seed
```

### Build Assets

```bash
# Development (with hot reload)
npm run dev

# Production
npm run build

# Watch for changes
npm run watch
```

### Clear Caches

```bash
# Clear all caches
php artisan optimize:clear

# Or individually
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Troubleshooting

### Issue: Port 8000 already in use

```bash
# Use different port
php artisan serve --port=8080

# Or kill process using port 8000
# Windows:
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Linux/macOS:
lsof -ti:8000 | xargs kill -9
```

### Issue: Database connection error

1. Check if database server is running
2. Verify credentials in `.env`
3. Test connection:
   ```bash
   php artisan db:show
   ```

### Issue: Permission errors (Linux/macOS)

```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Composer/NPM errors

```bash
# Clear caches
composer clear-cache
npm cache clean --force

# Reinstall
rm -rf vendor node_modules
composer install
npm install
```

---

## Next Steps

### For Developers

1. Read [CONTRIBUTING.md](CONTRIBUTING.md)
2. Check [docs/](docs/) for detailed documentation
3. Review code structure in `app/` directory
4. Set up IDE helpers:
   ```bash
   composer require --dev barryvdh/laravel-ide-helper
   php artisan ide-helper:generate
   ```

### For Production Deployment

1. Read [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)
2. Configure web server (Apache/Nginx)
3. Set up SSL certificate
4. Configure queue workers
5. Set up automated backups
6. Enable caching:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Configure Real-time Chat

1. Sign up for [Pusher](https://pusher.com/)
2. Create a new app
3. Update `.env`:
   ```env
   BROADCAST_CONNECTION=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=mt1
   ```
4. Restart server

---

## Useful Commands

```bash
# Check Laravel version
php artisan --version

# List all routes
php artisan route:list

# Create new controller
php artisan make:controller YourController

# Create new model with migration
php artisan make:model YourModel -m

# Run tests
php artisan test

# Open Tinker (Laravel REPL)
php artisan tinker

# Check database status
php artisan db:show

# View logs
tail -f storage/logs/laravel.log
```

---

## Getting Help

- 📖 **Documentation**: See [README.md](README.md) and [docs/](docs/)
- 🐛 **Issues**: Check [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)
- 💬 **Support**: support@uniprint.com
- 🔧 **Installation**: See [INSTALLATION.md](INSTALLATION.md)

---

## Platform-Specific Notes

### Windows

- Use `\` for paths or escape them: `C:\\path\\to\\file`
- Run Command Prompt or PowerShell as Administrator for some operations
- Consider using [Git Bash](https://git-scm.com/downloads) for Unix-like commands

### macOS

- Use Homebrew for installing dependencies
- May need to disable SIP for some operations
- Use `sudo` for permission-related commands

### Linux

- Use package manager (apt, dnf, yum) for dependencies
- Set proper file permissions for web server
- Configure SELinux/AppArmor if enabled

---

## Development Workflow

1. **Pull latest changes**
   ```bash
   git pull origin main
   composer install
   npm install
   php artisan migrate
   ```

2. **Create feature branch**
   ```bash
   git checkout -b feature/your-feature
   ```

3. **Make changes and test**
   ```bash
   php artisan test
   npm run dev
   ```

4. **Commit and push**
   ```bash
   git add .
   git commit -m "Add your feature"
   git push origin feature/your-feature
   ```

5. **Create Pull Request**

---

## Security Checklist

Before going live:

- [ ] Change all default passwords
- [ ] Set `APP_DEBUG=false` in production
- [ ] Set `APP_ENV=production`
- [ ] Use HTTPS (SSL/TLS)
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Enable CSRF protection (enabled by default)
- [ ] Validate all user inputs
- [ ] Use prepared statements (Eloquent does this)
- [ ] Keep dependencies updated
- [ ] Review `.env` file - no sensitive data in version control

---

## Performance Tips

- Enable OPcache in `php.ini`
- Use Redis for caching (optional)
- Optimize images before uploading
- Use CDN for static assets (production)
- Enable Gzip compression
- Minify CSS/JS (done automatically with `npm run build`)
- Use database indexes (already included)
- Monitor slow queries

---

**You're all set! Happy coding! 🚀**

For detailed information, see [README.md](README.md) and [INSTALLATION.md](INSTALLATION.md).
