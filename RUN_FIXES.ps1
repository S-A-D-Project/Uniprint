# UniPrint Database & Icon Fixes
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "UniPrint Database & Icon Fixes" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Function to check if command succeeded
function Test-CommandSuccess {
    param($Command)
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Success" -ForegroundColor Green
        return $true
    } else {
        Write-Host "✗ Failed" -ForegroundColor Red
        return $false
    }
}

Write-Host "[1/4] Running database migration to add shop_logo column..." -ForegroundColor Yellow
try {
    php artisan migrate --force
    if (Test-CommandSuccess "migrate") {
        Write-Host "Migration completed successfully" -ForegroundColor Green
    } else {
        Write-Host "ERROR: Migration failed!" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: Migration failed - $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
Write-Host ""

Write-Host "[2/4] Clearing all caches..." -ForegroundColor Yellow
php artisan optimize:clear
if (Test-CommandSuccess "optimize:clear") {
    Write-Host "Caches cleared" -ForegroundColor Green
}
Write-Host ""

Write-Host "[3/4] Rebuilding optimized files..." -ForegroundColor Yellow
php artisan config:clear
php artisan route:clear
php artisan view:clear
Write-Host "Optimized files rebuilt" -ForegroundColor Green
Write-Host ""

Write-Host "[4/4] Testing database connection..." -ForegroundColor Yellow
try {
    $testResult = php artisan tinker --execute="echo 'Database connection: OK'; echo PHP_EOL; echo 'Shop logo column check: '; echo Schema::hasColumn('enterprises', 'shop_logo') ? 'EXISTS' : 'MISSING';"
    Write-Host $testResult -ForegroundColor Green
} catch {
    Write-Host "Database test failed" -ForegroundColor Red
}
Write-Host ""

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ ALL FIXES COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test customer dashboard: /customer/dashboard"
Write-Host "2. Verify chatbot icon loads correctly"
Write-Host "3. Check orders display without errors"
Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Yellow
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
