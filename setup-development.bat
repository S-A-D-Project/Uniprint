@echo off
echo ============================================
echo UniPrint Development Setup Script
echo ============================================
echo.

echo [1/5] Copying development environment configuration...
copy .env.development .env
if %errorlevel% neq 0 (
    echo ERROR: Failed to copy .env.development to .env
    pause
    exit /b 1
)
echo ✓ Environment file configured

echo.
echo [2/5] Generating application key...
php artisan key:generate
if %errorlevel% neq 0 (
    echo ERROR: Failed to generate application key
    pause
    exit /b 1
)
echo ✓ Application key generated

echo.
echo [3/5] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo WARNING: Database migration failed - check your database connection
)
echo ✓ Database migrations attempted

echo.
echo [4/5] Clearing application cache...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ✓ Application cache cleared

echo.
echo [5/5] Setting up storage links...
php artisan storage:link
echo ✓ Storage links created

echo.
echo ============================================
echo Development Setup Complete!
echo ============================================
echo.
echo Your application is now configured for development with:
echo - Relaxed security settings
echo - CORS enabled for all origins
echo - URL rewriting enabled
echo - Debug mode enabled
echo - Pusher real-time features configured
echo.
echo To start the development server, run:
echo php artisan serve
echo.
echo Your application will be available at:
echo http://localhost:8000
echo.
pause
