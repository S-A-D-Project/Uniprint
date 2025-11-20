@echo off
echo ========================================
echo UniPrint Database Setup Script
echo ========================================
echo.

echo Step 1: Backing up current .env file...
copy .env .env.backup >nul 2>&1

echo Step 2: Copying new configuration...
copy /Y .env.local .env

echo Step 3: Verifying PostgreSQL connection...
echo Please ensure PostgreSQL is running and configured in .env
echo.
echo Required .env settings:
echo   DB_CONNECTION=pgsql
echo   DB_HOST=127.0.0.1
echo   DB_PORT=5432
echo   DB_DATABASE=uniprint
echo   DB_USERNAME=your_username
echo   DB_PASSWORD=your_password

echo.
echo Step 4: Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo.
echo Step 5: Running migrations...
php artisan migrate:fresh --force

echo.
echo Step 6: Seeding database...
php artisan db:seed

echo.
echo ========================================
echo Fix complete! Starting server...
echo ========================================
echo.
echo Visit: http://uniprint.test
echo.
php artisan serve

pause
