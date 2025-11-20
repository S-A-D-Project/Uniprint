# Database Configuration Guide

This guide covers database setup and configuration for UniPrint across different database systems.

## Table of Contents

- [Supported Databases](#supported-databases)
- [PostgreSQL Setup](#postgresql-setup)
- [MySQL Setup](#mysql-setup)
- [Environment Configuration](#environment-configuration)
- [Migration and Seeding](#migration-and-seeding)
- [Database Testing](#database-testing)
- [Backup and Restore](#backup-and-restore)
- [Performance Optimization](#performance-optimization)

---

## Supported Databases

UniPrint supports the following database systems:

| Database | Recommended | Production Ready | Notes |
|----------|-------------|------------------|-------|
| PostgreSQL 14+ | ✅ Yes | ✅ Yes | Best performance, UUID support |
| MySQL 8.0+ | ⚠️ Supported | ✅ Yes | Widely available |

---

## PostgreSQL Setup

### Installation

#### Windows
1. Download from [postgresql.org](https://www.postgresql.org/download/windows/)
2. Run installer
3. Remember superuser password
4. Default port: 5432

#### macOS
```bash
brew install postgresql@14
brew services start postgresql@14
```

#### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install -y postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

#### Linux (CentOS/RHEL)
```bash
sudo dnf install -y postgresql-server postgresql-contrib
sudo postgresql-setup --initdb
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### Database Creation

```bash
# Connect as postgres user
sudo -u postgres psql

# Or on Windows/macOS
psql -U postgres
```

```sql
-- Create database
CREATE DATABASE uniprint;

-- Create user
CREATE USER uniprint_user WITH PASSWORD 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE uniprint TO uniprint_user;

-- Connect to database
\c uniprint

-- Grant schema privileges (PostgreSQL 15+)
GRANT ALL ON SCHEMA public TO uniprint_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO uniprint_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO uniprint_user;

-- Exit
\q
```

### Configuration

Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=uniprint_user
DB_PASSWORD=secure_password_here
```

### Remote Access (Optional)

Edit `postgresql.conf`:
```conf
listen_addresses = '*'
```

Edit `pg_hba.conf`:
```conf
# Allow connections from specific IP
host    uniprint    uniprint_user    192.168.1.0/24    md5

# Or allow from anywhere (not recommended for production)
host    all         all              0.0.0.0/0         md5
```

Restart PostgreSQL:
```bash
sudo systemctl restart postgresql
```

---

## MySQL Setup

### Installation

#### Windows
1. Download [MySQL Installer](https://dev.mysql.com/downloads/installer/)
2. Run installer
3. Choose "Server only" or "Full"
4. Remember root password

#### macOS
```bash
brew install mysql
brew services start mysql
```

#### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
sudo mysql_secure_installation
```

#### Linux (CentOS/RHEL)
```bash
sudo dnf install -y mysql-server
sudo systemctl start mysqld
sudo systemctl enable mysqld
sudo mysql_secure_installation
```

### Database Creation

```bash
# Connect as root
mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE uniprint CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'uniprint_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON uniprint.* TO 'uniprint_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Configuration

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniprint
DB_USERNAME=uniprint_user
DB_PASSWORD=secure_password_here
```

### Remote Access (Optional)

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```conf
bind-address = 0.0.0.0
```

Grant remote access:
```sql
CREATE USER 'uniprint_user'@'%' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON uniprint.* TO 'uniprint_user'@'%';
FLUSH PRIVILEGES;
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

---

## Environment Configuration

### Basic Configuration

Minimum required settings in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uniprint
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Advanced Configuration

Additional database settings:

```env
# Connection pooling
DB_POOL_MIN=2
DB_POOL_MAX=10

# Timeout settings
DB_TIMEOUT=60

# SSL/TLS (for remote connections)
DB_SSLMODE=require
DB_SSLCERT=/path/to/client-cert.pem
DB_SSLKEY=/path/to/client-key.pem
DB_SSLROOTCERT=/path/to/ca-cert.pem

# Charset (MySQL)
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Schema (PostgreSQL)
DB_SCHEMA=public
```

### Multiple Database Connections

Edit `config/database.php` to add additional connections:

```php
'connections' => [
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'uniprint'),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD', ''),
        // ...
    ],
    
    'pgsql_reporting' => [
        'driver' => 'pgsql',
        'host' => env('DB_REPORTING_HOST', '127.0.0.1'),
        'port' => env('DB_REPORTING_PORT', '5432'),
        'database' => env('DB_REPORTING_DATABASE', 'uniprint_reporting'),
        'username' => env('DB_REPORTING_USERNAME', 'postgres'),
        'password' => env('DB_REPORTING_PASSWORD', ''),
        // ...
    ],
],
```

---

## Migration and Seeding

### Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Run migrations with output
php artisan migrate --verbose

# Run migrations in production (skip confirmation)
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Drop all tables and re-run migrations
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed
```

### Seeding Database

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=NewUsersSeeder

# Force seeding in production
php artisan db:seed --force
```

### Available Seeders

- `RoleTypesSeeder` - User role types
- `StatusesSeeder` - Order statuses
- `NewUsersSeeder` - Sample users and conversations
- `EnterprisesSeeder` - Sample printing businesses
- `ProductsSeeder` - Sample products
- `SampleOrdersSeeder` - Sample orders

### Creating Custom Seeders

```bash
php artisan make:seeder YourSeederName
```

---

## Database Testing

### Test Connection

```bash
# Show database information
php artisan db:show

# Test connection in Tinker
php artisan tinker
>>> DB::connection()->getPdo();
>>> DB::table('users')->count();
>>> exit
```

### Manual Testing

**PostgreSQL:**
```bash
psql -h 127.0.0.1 -U uniprint_user -d uniprint -c "SELECT version();"
```

**MySQL:**
```bash
mysql -h 127.0.0.1 -u uniprint_user -p uniprint -e "SELECT VERSION();"
```

### Connection Troubleshooting

```bash
# Test with verbose output
php artisan tinker
>>> config('database.connections.pgsql');
>>> DB::connection()->getConfig();
```

---

## Backup and Restore

### PostgreSQL

**Backup:**
```bash
# Full database backup
pg_dump -U uniprint_user -h 127.0.0.1 uniprint > backup.sql

# Compressed backup
pg_dump -U uniprint_user -h 127.0.0.1 uniprint | gzip > backup.sql.gz

# Custom format (faster restore)
pg_dump -U uniprint_user -h 127.0.0.1 -Fc uniprint > backup.dump
```

**Restore:**
```bash
# From SQL file
psql -U uniprint_user -h 127.0.0.1 uniprint < backup.sql

# From compressed file
gunzip -c backup.sql.gz | psql -U uniprint_user -h 127.0.0.1 uniprint

# From custom format
pg_restore -U uniprint_user -h 127.0.0.1 -d uniprint backup.dump
```

### MySQL

**Backup:**
```bash
# Full database backup
mysqldump -u uniprint_user -p uniprint > backup.sql

# Compressed backup
mysqldump -u uniprint_user -p uniprint | gzip > backup.sql.gz
```

**Restore:**
```bash
# From SQL file
mysql -u uniprint_user -p uniprint < backup.sql

# From compressed file
gunzip -c backup.sql.gz | mysql -u uniprint_user -p uniprint
```

### Automated Backups

Create a backup script (`scripts/backup-database.sh`):

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_NAME="uniprint"
DB_USER="uniprint_user"

# PostgreSQL
pg_dump -U $DB_USER $DB_NAME | gzip > "$BACKUP_DIR/backup_$DATE.sql.gz"

# MySQL
# mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > "$BACKUP_DIR/backup_$DATE.sql.gz"

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

Schedule with cron (Linux/macOS):
```bash
crontab -e
# Add line: Run daily at 2 AM
0 2 * * * /path/to/scripts/backup-database.sh
```

---

## Performance Optimization

### PostgreSQL Optimization

Edit `postgresql.conf`:

```conf
# Memory settings
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
work_mem = 16MB

# Connection settings
max_connections = 100

# Query planning
random_page_cost = 1.1
effective_io_concurrency = 200

# Logging
log_min_duration_statement = 1000  # Log slow queries (>1s)
```

### MySQL Optimization

Edit `my.cnf`:

```conf
[mysqld]
# Memory settings
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M

# Connection settings
max_connections = 100

# Query cache (MySQL 5.7)
query_cache_type = 1
query_cache_size = 32M

# Logging
slow_query_log = 1
long_query_time = 1
```

### Laravel Query Optimization

```bash
# Enable query logging in .env
DB_LOG_QUERIES=true
LOG_LEVEL=debug

# Monitor queries in logs
tail -f storage/logs/laravel.log | grep "select"
```

### Database Indexes

The application includes performance indexes. To add custom indexes:

```bash
php artisan make:migration add_custom_indexes_to_table
```

```php
Schema::table('your_table', function (Blueprint $table) {
    $table->index('column_name');
    $table->index(['column1', 'column2'], 'custom_index_name');
});
```

---

## Security Best Practices

1. **Use strong passwords** for database users
2. **Limit database user privileges** to only what's needed
3. **Use SSL/TLS** for remote connections
4. **Regular backups** - automate and test restores
5. **Monitor logs** for suspicious activity
6. **Keep database software updated**
7. **Use environment variables** - never hardcode credentials
8. **Restrict network access** - use firewall rules
9. **Regular security audits**
10. **Separate production and development databases**

---

## Additional Resources

- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Laravel Migrations](https://laravel.com/docs/migrations)
- [Laravel Seeding](https://laravel.com/docs/seeding)

---

**Need help?** Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or contact support.
