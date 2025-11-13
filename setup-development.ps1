# UniPrint Development Setup Script (PowerShell)
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "UniPrint Development Setup Script" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Change to project directory
Set-Location -Path $PSScriptRoot

Write-Host "[1/6] Copying development environment configuration..." -ForegroundColor Yellow
try {
    Copy-Item -Path ".env.development" -Destination ".env" -Force
    Write-Host "✓ Environment file configured" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Failed to copy .env.development to .env" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "[2/6] Checking PHP and Composer..." -ForegroundColor Yellow
try {
    $phpVersion = php -v 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ PHP is available" -ForegroundColor Green
    } else {
        Write-Host "WARNING: PHP not found in PATH" -ForegroundColor Yellow
    }
} catch {
    Write-Host "WARNING: PHP not available" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[3/6] Installing/updating Composer dependencies..." -ForegroundColor Yellow
try {
    composer install --no-dev --optimize-autoloader 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Composer dependencies installed" -ForegroundColor Green
    } else {
        Write-Host "WARNING: Composer install failed - trying without flags" -ForegroundColor Yellow
        composer install 2>$null
    }
} catch {
    Write-Host "WARNING: Composer not available or failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[4/6] Generating application key..." -ForegroundColor Yellow
try {
    php artisan key:generate --force 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Application key generated" -ForegroundColor Green
    } else {
        Write-Host "WARNING: Failed to generate application key" -ForegroundColor Yellow
    }
} catch {
    Write-Host "WARNING: Artisan key:generate failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[5/6] Clearing application cache..." -ForegroundColor Yellow
try {
    php artisan config:clear 2>$null
    php artisan cache:clear 2>$null
    php artisan route:clear 2>$null
    php artisan view:clear 2>$null
    Write-Host "✓ Application cache cleared" -ForegroundColor Green
} catch {
    Write-Host "WARNING: Cache clearing failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[6/6] Setting up storage links..." -ForegroundColor Yellow
try {
    php artisan storage:link 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Storage links created" -ForegroundColor Green
    } else {
        Write-Host "WARNING: Storage link creation failed" -ForegroundColor Yellow
    }
} catch {
    Write-Host "WARNING: Storage link setup failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "Development Setup Complete!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your application is now configured for development with:" -ForegroundColor White
Write-Host "- Relaxed security settings" -ForegroundColor Gray
Write-Host "- CORS enabled for all origins" -ForegroundColor Gray
Write-Host "- URL rewriting enabled" -ForegroundColor Gray
Write-Host "- Debug mode enabled" -ForegroundColor Gray
Write-Host "- Pusher real-time features configured" -ForegroundColor Gray
Write-Host ""
Write-Host "To start the development server, run:" -ForegroundColor Yellow
Write-Host "php artisan serve" -ForegroundColor Cyan
Write-Host ""
Write-Host "Your application will be available at:" -ForegroundColor Yellow
Write-Host "http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "For detailed setup information, see:" -ForegroundColor Yellow
Write-Host "DEVELOPMENT_SETUP_GUIDE.md" -ForegroundColor Cyan
Write-Host ""

# Test URL accessibility
Write-Host "Testing URL accessibility..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Root .htaccess: " -NoNewline
if (Test-Path ".htaccess") {
    Write-Host "✓ Present" -ForegroundColor Green
} else {
    Write-Host "✗ Missing" -ForegroundColor Red
}

Write-Host "Public .htaccess: " -NoNewline
if (Test-Path "public\.htaccess") {
    Write-Host "✓ Present" -ForegroundColor Green
} else {
    Write-Host "✗ Missing" -ForegroundColor Red
}

Write-Host "Environment file: " -NoNewline
if (Test-Path ".env") {
    Write-Host "✓ Present" -ForegroundColor Green
} else {
    Write-Host "✗ Missing" -ForegroundColor Red
}

Write-Host ""
Read-Host "Press Enter to exit"
