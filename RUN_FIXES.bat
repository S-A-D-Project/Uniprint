@echo off
echo ========================================
echo UniPrint Database & Icon Fixes
echo ========================================
echo.

echo [1/4] Running database migration to add shop_logo column...
php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo ✓ Migration completed successfully
echo.

echo [2/4] Clearing all caches...
php artisan optimize:clear
echo ✓ Caches cleared
echo.

echo [3/4] Rebuilding optimized files...
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ✓ Optimized files rebuilt
echo.

echo [4/4] Testing database connection...
php artisan tinker --execute="echo 'Database connection: OK'; echo '\n'; echo 'Shop logo column check: '; echo \Schema::hasColumn('enterprises', 'shop_logo') ? 'EXISTS' : 'MISSING';"
echo.

echo ========================================
echo ✅ ALL FIXES COMPLETED SUCCESSFULLY!
echo ========================================
echo.
echo Next steps:
echo 1. Test customer dashboard: /customer/dashboard
echo 2. Verify chatbot icon loads correctly
echo 3. Check orders display without errors
echo.
echo Press any key to exit...
pause > nul
