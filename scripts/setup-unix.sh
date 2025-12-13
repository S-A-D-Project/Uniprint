#!/bin/bash

# UniPrint Unix/Linux/macOS Setup Script
# This script automates the setup process on Unix-based systems

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo "========================================"
echo "UniPrint Unix/Linux/macOS Setup Script"
echo "========================================"
echo ""

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    print_warning "Running as root is not recommended"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 1: Check requirements
print_info "Step 1: Checking system requirements..."
if php scripts/check-requirements.php; then
    print_success "System requirements check passed"
else
    print_error "System requirements check failed!"
    echo "Please install missing dependencies and try again."
    exit 1
fi

# Step 2: Install Composer dependencies
echo ""
print_info "Step 2: Installing Composer dependencies..."
if composer install; then
    print_success "Composer dependencies installed"
else
    print_error "Composer install failed!"
    exit 1
fi

# Step 3: Install NPM dependencies
echo ""
print_info "Step 3: Installing NPM dependencies..."
if npm install; then
    print_success "NPM dependencies installed"
else
    print_error "NPM install failed!"
    exit 1
fi

# Step 4: Setup environment file
echo ""
print_info "Step 4: Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_success ".env file created from .env.example"
else
    print_warning ".env file already exists, skipping..."
fi

# Step 5: Generate application key
echo ""
print_info "Step 5: Generating application key..."
php artisan key:generate
print_success "Application key generated"

# Step 6: Create storage directories
echo ""
print_info "Step 6: Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
print_success "Storage directories created"

# Step 7: Set permissions
echo ""
print_info "Step 7: Setting file permissions..."
chmod -R 775 storage bootstrap/cache
print_success "File permissions set"

# Detect OS and set ownership
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    WEB_USER="_www"
elif [[ -f /etc/debian_version ]]; then
    # Debian/Ubuntu
    WEB_USER="www-data"
elif [[ -f /etc/redhat-release ]]; then
    # CentOS/RHEL
    WEB_USER="apache"
else
    WEB_USER="www-data"
fi

if groups | grep -q "$WEB_USER"; then
    chown -R $USER:$WEB_USER storage bootstrap/cache 2>/dev/null || print_warning "Could not set ownership (may need sudo)"
    print_success "File ownership set"
else
    print_warning "Web server user '$WEB_USER' not found, skipping ownership change"
fi

# Final message
echo ""
echo "========================================"
echo "Setup Complete!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Edit .env file with your database credentials:"
echo "   nano .env"
echo ""
echo "2. Create database:"
echo "   PostgreSQL: psql -U postgres -c 'CREATE DATABASE uniprint;'"
echo "   MySQL: mysql -u root -p -e 'CREATE DATABASE uniprint;'"
echo ""
echo "3. Run migrations:"
echo "   php artisan migrate --seed"
echo ""
echo "4. Build assets:"
echo "   npm run build"
echo ""
echo "5. Start server:"
echo "   php artisan serve"
echo ""
echo "For detailed instructions, see INSTALLATION.md"
echo ""
