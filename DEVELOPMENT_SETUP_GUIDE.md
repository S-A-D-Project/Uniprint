# UniPrint Development Setup Guide

## Overview
This guide provides instructions for setting up the UniPrint application for development with maximum accessibility and minimal security restrictions.

## Files Created/Modified

### 1. Root .htaccess (`/.htaccess`)
- **Purpose**: Handles URL rewriting for the entire application
- **Features**:
  - Enables mod_rewrite functionality
  - Sets RewriteBase to root directory
  - Handles Laravel routing through public/index.php
  - Enables CORS for all origins
  - Removes security restrictions for development
  - Enables file compression and caching

### 2. Public .htaccess (`/public/.htaccess`)
- **Purpose**: Laravel-specific URL rewriting and routing
- **Features**:
  - Enhanced Laravel routing with QSA (Query String Append)
  - CORS headers for API access
  - Proper MIME type handling
  - File compression and caching
  - Relaxed security for development

### 3. Development Environment (`.env.development`)
- **Purpose**: Complete environment configuration for development
- **Features**:
  - Debug mode enabled
  - Database configuration (Supabase PostgreSQL)
  - Pusher real-time configuration with working credentials
  - Relaxed security settings
  - Development-friendly logging
  - CORS and CSRF protection disabled

## Setup Instructions

### Method 1: Automated Setup (Recommended)
1. Run the setup script:
   ```bash
   setup-development.bat
   ```

### Method 2: Manual Setup
1. **Copy Environment Configuration**:
   ```bash
   copy .env.development .env
   ```

2. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

3. **Run Database Migrations**:
   ```bash
   php artisan migrate
   ```

4. **Clear Application Cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

5. **Create Storage Links**:
   ```bash
   php artisan storage:link
   ```

6. **Start Development Server**:
   ```bash
   php artisan serve
   ```

## Configuration Details

### Database Configuration
- **Type**: PostgreSQL (Supabase)
- **Host**: db.qycrekfjvpcfkqserpab.supabase.co
- **Database**: postgres
- **Credentials**: Configured in .env.development

### Pusher Real-time Configuration
- **App ID**: 2077111
- **Key**: f7ca062b8f895c3f2497
- **Secret**: 9829cf3fa2e92e92ab08
- **Cluster**: ap1

### Security Settings (Development Only)
⚠️ **WARNING**: These settings are for development only and should NEVER be used in production!

- **CSRF Protection**: Disabled
- **CORS**: Allows all origins (*)
- **Debug Mode**: Enabled
- **Error Display**: Detailed errors shown
- **File Access**: All files accessible
- **Rate Limiting**: Disabled

### URL Rewriting Rules

#### Root Level (/.htaccess)
```apache
# Redirect all requests to Laravel's public directory
RewriteRule ^(.*)$ /public/index.php/$1 [L,QSA]
```

#### Public Level (/public/.htaccess)
```apache
# Send all requests to Laravel's front controller
RewriteRule ^ index.php [L,QSA]
```

## Testing URL Accessibility

After setup, test these URLs to ensure proper routing:

### Basic Routes
- `http://localhost:8000/` - Homepage
- `http://localhost:8000/login` - Login page
- `http://localhost:8000/register` - Registration page
- `http://localhost:8000/dashboard` - Dashboard (requires login)

### API Routes
- `http://localhost:8000/api/user` - User API
- `http://localhost:8000/api/products` - Products API

### Static Assets
- `http://localhost:8000/css/app.css` - Stylesheets
- `http://localhost:8000/js/app.js` - JavaScript files
- `http://localhost:8000/images/` - Image files

## Troubleshooting

### Common Issues

1. **404 Errors on Routes**
   - Ensure mod_rewrite is enabled on your server
   - Check that .htaccess files are being read
   - Verify RewriteBase settings

2. **CORS Errors**
   - Check that mod_headers is enabled
   - Verify CORS headers in browser developer tools
   - Ensure preflight OPTIONS requests are handled

3. **Database Connection Issues**
   - Verify database credentials in .env
   - Check network connectivity to Supabase
   - Ensure PostgreSQL extensions are installed

4. **Pusher Connection Issues**
   - Verify Pusher credentials
   - Check network connectivity
   - Ensure WebSocket connections are allowed

### Server Requirements
- **PHP**: 8.1 or higher
- **Apache Modules**: mod_rewrite, mod_headers (recommended)
- **PHP Extensions**: PDO, pdo_pgsql, openssl, mbstring, tokenizer, xml, ctype, json
- **Database**: PostgreSQL access

## Production Considerations

When moving to production:

1. **Replace .env with production values**
2. **Enable security restrictions in .htaccess**
3. **Enable CSRF protection**
4. **Restrict CORS to specific domains**
5. **Disable debug mode**
6. **Enable proper error logging**
7. **Set up proper SSL/TLS certificates**

## Support

If you encounter issues:
1. Check Laravel logs in `storage/logs/`
2. Enable query logging in .env
3. Use `php artisan tinker` for debugging
4. Check Apache error logs
5. Verify server configuration

---

**Note**: This configuration prioritizes accessibility and ease of development over security. Always use appropriate security measures in production environments.
