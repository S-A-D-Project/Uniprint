# UniPrint Documentation

Welcome to the UniPrint documentation! This directory contains comprehensive guides for installing, configuring, deploying, and maintaining UniPrint.

## 📚 Documentation Index

### Getting Started
- **[Main README](../README.md)** - Project overview and quick start
- **[Quick Start Guide](../QUICKSTART.md)** - Get up and running in minutes
- **[Installation Guide](../INSTALLATION.md)** - Detailed platform-specific installation

### Configuration
- **[Database Configuration](DATABASE_CONFIGURATION.md)** - PostgreSQL and MySQL setup
- **[Environment Configuration](../README.md#configuration)** - .env file setup

### Deployment
- **[Deployment Checklist](../DEPLOYMENT_CHECKLIST.md)** - Production deployment guide
- **[Portability Summary](../PORTABILITY_SUMMARY.md)** - Cross-platform compatibility details

### Troubleshooting
- **[Troubleshooting Guide](TROUBLESHOOTING.md)** - Common issues and solutions
- **[FAQ](#frequently-asked-questions)** - Frequently asked questions

### Contributing
- **[Contributing Guide](../CONTRIBUTING.md)** - How to contribute to UniPrint

## 🔧 Quick Links

### Installation
```bash
# Check system requirements
php scripts/check-requirements.php

# Automated setup (Windows)
scripts\setup-windows.bat

# Automated setup (Unix/Linux/macOS)
./scripts/setup-unix.sh
```

### Common Commands
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Build assets
npm run build

# Run tests
php artisan test

# Clear caches
php artisan optimize:clear
```

## 📖 Documentation by Topic

### Installation & Setup
1. [System Requirements](../README.md#system-requirements)
2. [Windows Installation](../INSTALLATION.md#windows-installation)
3. [macOS Installation](../INSTALLATION.md#macos-installation)
4. [Linux Installation](../INSTALLATION.md#linux-installation)
5. [Post-Installation Setup](../INSTALLATION.md#post-installation-setup)

### Database
1. [PostgreSQL Setup](DATABASE_CONFIGURATION.md#postgresql-setup)
2. [MySQL Setup](DATABASE_CONFIGURATION.md#mysql-setup)
3. [Migration & Seeding](DATABASE_CONFIGURATION.md#migration-and-seeding)
4. [Backup & Restore](DATABASE_CONFIGURATION.md#backup-and-restore)

### Deployment
1. [Pre-Deployment Checklist](../DEPLOYMENT_CHECKLIST.md#pre-deployment)
2. [Deployment Steps](../DEPLOYMENT_CHECKLIST.md#deployment-steps)
3. [Web Server Configuration](../DEPLOYMENT_CHECKLIST.md#8-configure-web-server)
4. [SSL/TLS Setup](../DEPLOYMENT_CHECKLIST.md#9-configure-ssltls)
5. [Queue Workers](../DEPLOYMENT_CHECKLIST.md#10-setup-queue-workers)

### Troubleshooting
1. [Installation Issues](TROUBLESHOOTING.md#installation-issues)
2. [Database Issues](TROUBLESHOOTING.md#database-issues)
3. [Permission Issues](TROUBLESHOOTING.md#permission-issues)
4. [Runtime Errors](TROUBLESHOOTING.md#runtime-errors)
5. [Platform-Specific Issues](TROUBLESHOOTING.md#platform-specific-issues)

## 🎯 Common Tasks

### First-Time Setup
1. Clone repository
2. Run `php scripts/check-requirements.php`
3. Run setup script for your platform
4. Configure `.env` file
5. Create database
6. Run `php artisan migrate --seed`
7. Build assets with `npm run build`
8. Start server with `php artisan serve`

### Daily Development
1. Pull latest changes: `git pull`
2. Update dependencies: `composer install && npm install`
3. Run migrations: `php artisan migrate`
4. Start dev server: `composer run dev`

### Deploying Updates
1. Backup database and files
2. Enable maintenance mode: `php artisan down`
3. Pull latest code: `git pull`
4. Update dependencies: `composer install --no-dev`
5. Run migrations: `php artisan migrate --force`
6. Clear caches: `php artisan optimize:clear`
7. Cache config: `php artisan config:cache`
8. Disable maintenance mode: `php artisan up`

## 🐛 Troubleshooting Quick Reference

### Database Connection Issues
```bash
# Test connection
php artisan db:show

# Check credentials in .env
cat .env | grep DB_

# Test manually
psql -h 127.0.0.1 -U username -d database  # PostgreSQL
mysql -h 127.0.0.1 -u username -p database  # MySQL
```

### Permission Issues (Linux/macOS)
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Cache Issues
```bash
php artisan optimize:clear
composer dump-autoload
```

### Asset Build Issues
```bash
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
npm run build
```

## 📋 Frequently Asked Questions

### General

**Q: What are the minimum system requirements?**
A: PHP 8.2+, Composer 2.0+, Node.js 18+, PostgreSQL 14+ or MySQL 8.0+, 512MB RAM, 500MB disk space.

**Q: Which database should I use?**
A: PostgreSQL 14+ is recommended for production. MySQL 8.0+ is also fully supported.

**Q: Do I need Laravel Herd?**
A: No! UniPrint is fully portable and doesn't require Laravel Herd or any platform-specific tools.

### Installation

**Q: How do I check if my system meets the requirements?**
A: Run `php scripts/check-requirements.php`

**Q: The setup script fails. What should I do?**
A: Check the error message, ensure all prerequisites are installed, and see [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

**Q: Can I use a different port than 8000?**
A: Yes! Use `php artisan serve --port=8080` (or any available port).

### Database

**Q: How do I create the database?**
A: See [Database Configuration](DATABASE_CONFIGURATION.md#creating-the-database) for platform-specific instructions.

**Q: Can I use an existing database?**
A: Yes, but ensure it's empty or backup existing data first.

**Q: How do I backup the database?**
A: See [Backup & Restore](DATABASE_CONFIGURATION.md#backup-and-restore) section.

### Deployment

**Q: How do I deploy to production?**
A: Follow the [Deployment Checklist](../DEPLOYMENT_CHECKLIST.md).

**Q: Do I need a web server?**
A: For production, yes (Apache or Nginx). For development, Laravel's built-in server is sufficient.

**Q: How do I configure SSL/TLS?**
A: See [SSL/TLS Setup](../DEPLOYMENT_CHECKLIST.md#9-configure-ssltls) in the deployment checklist.

### Development

**Q: How do I run tests?**
A: Run `php artisan test`

**Q: How do I contribute?**
A: See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines.

**Q: Where are the logs?**
A: Check `storage/logs/laravel.log`

## 🔗 External Resources

### Laravel Documentation
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Migrations](https://laravel.com/docs/12.x/migrations)
- [Laravel Eloquent ORM](https://laravel.com/docs/12.x/eloquent)
- [Laravel Queues](https://laravel.com/docs/12.x/queues)

### Database Documentation
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Web Server Documentation
- [Apache Documentation](https://httpd.apache.org/docs/)
- [Nginx Documentation](https://nginx.org/en/docs/)

### PHP Documentation
- [PHP Manual](https://www.php.net/manual/en/)
- [Composer Documentation](https://getcomposer.org/doc/)

## 📞 Getting Help

### Documentation
- Check this documentation first
- Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- Read [INSTALLATION.md](../INSTALLATION.md)

### Community
- GitHub Issues: Report bugs and request features
- GitHub Discussions: Ask questions and share ideas
- Stack Overflow: Tag questions with `uniprint` and `laravel`

### Support
- Email: support@uniprint.com
- Documentation: [docs/](.)
- Contributing: [CONTRIBUTING.md](../CONTRIBUTING.md)

## 📝 Documentation Maintenance

This documentation is maintained by the UniPrint development team. If you find any errors or have suggestions for improvements:

1. Open an issue on GitHub
2. Submit a pull request with corrections
3. Contact the development team

### Contributing to Documentation

Documentation contributions are welcome! Please:

1. Follow the existing format and style
2. Use clear, concise language
3. Include code examples where appropriate
4. Test all commands and procedures
5. Update the table of contents if adding new sections

## 🔄 Documentation Updates

- **Last Updated**: November 15, 2025
- **Version**: 1.0.0
- **Maintained By**: UniPrint Development Team

---

**Need more help?** Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or contact support@uniprint.com.
