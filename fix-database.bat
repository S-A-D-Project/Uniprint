@echo off
echo ========================================
echo UniPrint Database Fix Script
echo ========================================
echo.

echo Step 1: Backing up current .env file...
copy .env .env.backup >nul 2>&1

echo Step 2: Copying new configuration...
copy /Y .env.local .env

echo Step 3: Creating SQLite database...
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo SQLite database created.
) else (
    echo SQLite database already exists.
)

echo Step 4: Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo Step 5: Running migrations...
php artisan migrate:fresh --force

echo Step 6: Seeding database...
php artisan db:seed --class=EnterprisesTableSeeder

echo.
echo ========================================
echo Fix complete! Starting server...
echo ========================================
echo.
echo Visit: http://uniprint.test
echo.
php artisan serve

pause
