@echo off
REM UniPrint Windows Setup Script
REM This script automates the setup process on Windows

echo.
echo ========================================
echo UniPrint Windows Setup Script
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Warning: Not running as administrator.
    echo Some operations may fail.
    echo.
    pause
)

echo Step 1: Checking requirements...
php scripts\check-requirements.php
if %errorLevel% neq 0 (
    echo.
    echo ERROR: System requirements check failed!
    echo Please install missing dependencies and try again.
    pause
    exit /b 1
)

echo.
echo Step 2: Installing Composer dependencies...
call composer install
if %errorLevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)

echo.
echo Step 3: Installing NPM dependencies...
call npm install
if %errorLevel% neq 0 (
    echo ERROR: NPM install failed!
    pause
    exit /b 1
)

echo.
echo Step 4: Setting up environment file...
if not exist .env (
    copy .env.example .env
    echo .env file created from .env.example
) else (
    echo .env file already exists, skipping...
)

echo.
echo Step 5: Generating application key...
php artisan key:generate

echo.
echo Step 6: Creating storage directories...
if not exist storage\app\public mkdir storage\app\public
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views
if not exist storage\logs mkdir storage\logs

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Edit .env file with your database credentials
echo 2. Create database: psql -U postgres -c "CREATE DATABASE uniprint;"
echo 3. Run migrations: php artisan migrate --seed
echo 4. Build assets: npm run build
echo 5. Start server: php artisan serve
echo.
echo For detailed instructions, see INSTALLATION.md
echo.
pause
