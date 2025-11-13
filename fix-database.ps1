# UniPrint Database Fix Script (PowerShell)
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "UniPrint Database Fix Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Backup current .env
Write-Host "Step 1: Backing up current .env file..." -ForegroundColor Yellow
Copy-Item .env .env.backup -Force -ErrorAction SilentlyContinue

# Step 2: Copy new configuration
Write-Host "Step 2: Copying new configuration..." -ForegroundColor Yellow
Copy-Item .env.local .env -Force

# Step 3: Ensure SQLite database exists
Write-Host "Step 3: Creating SQLite database..." -ForegroundColor Yellow
if (-not (Test-Path "database\database.sqlite")) {
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    Write-Host "  SQLite database created." -ForegroundColor Green
} else {
    Write-Host "  SQLite database already exists." -ForegroundColor Green
}

# Step 4: Clear all caches
Write-Host "Step 4: Clearing caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Step 5: Run migrations
Write-Host "Step 5: Running migrations..." -ForegroundColor Yellow
php artisan migrate:fresh --force

# Step 6: Seed database
Write-Host "Step 6: Seeding database..." -ForegroundColor Yellow
php artisan db:seed --class=EnterprisesTableSeeder

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Fix complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Visit: http://uniprint.test" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting development server..." -ForegroundColor Yellow
php artisan serve
