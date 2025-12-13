<?php

/**
 * InfinityFree Deployment Configuration
 * 
 * This configuration file contains settings optimized for InfinityFree hosting.
 * Copy your .env.example to .env and update with your InfinityFree credentials.
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Asset URL Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree requires proper asset URL configuration. Update this
    | based on your actual domain.
    |
    */
    
    'asset_url' => env('ASSET_URL', 'https://yourdomain.infinityfreeapp.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree provides MySQL databases with specific naming conventions.
    | Format: [account_id]_[database_name]
    |
    */
    
    'database' => [
        'host' => env('DB_HOST', 'sqlXXX.infinityfree.net'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'epizXXXXXXX_uniprint'),
        'username' => env('DB_USERNAME', 'epizXXXXXXX_user'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => false,
        'engine' => 'InnoDB',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Storage Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree has specific file storage limitations.
    | - Max file size: 10MB per file
    | - Total storage: varies by plan
    |
    */
    
    'storage' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // in KB (10MB)
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => env('UPLOAD_PATH', 'htdocs/storage/app/public'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Settings to optimize performance on shared hosting.
    |
    */
    
    'performance' => [
        'enable_opcache' => true,
        'enable_gzip' => true,
        'minify_html' => env('MINIFY_HTML', true),
        'minify_css' => env('MINIFY_CSS', true),
        'minify_js' => env('MINIFY_JS', true),
        'lazy_load_images' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree session settings for better performance.
    |
    */
    
    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'path' => env('SESSION_PATH', '/'),
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE_COOKIE', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree email settings. Note: SMTP may be limited on free plan.
    |
    */
    
    'mail' => [
        'driver' => env('MAIL_DRIVER', 'mail'), // Use 'mail' for PHP mail()
        'host' => env('MAIL_HOST', 'smtp.gmail.com'),
        'port' => env('MAIL_PORT', 587),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@yourdomain.com'),
            'name' => env('MAIL_FROM_NAME', 'UniPrint'),
        ],
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Use CDN for external libraries to save bandwidth.
    |
    */
    
    'cdn' => [
        'enabled' => env('CDN_ENABLED', true),
        'tailwindcss' => 'https://cdn.tailwindcss.com',
        'lucide_icons' => 'https://unpkg.com/lucide@latest',
        'alpine_js' => 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | InfinityFree cache settings.
    |
    */
    
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('CACHE_TTL', 3600), // 1 hour
        'prefix' => env('CACHE_PREFIX', 'uniprint_cache'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Deployment Checklist
    |--------------------------------------------------------------------------
    |
    | Items to verify before deploying to InfinityFree:
    |
    | 1. Update .env file with InfinityFree database credentials
    | 2. Set APP_ENV=production
    | 3. Set APP_DEBUG=false
    | 4. Generate new APP_KEY
    | 5. Configure asset URLs
    | 6. Upload files via FTP to htdocs directory
    | 7. Import database via phpMyAdmin
    | 8. Set proper file permissions (755 for directories, 644 for files)
    | 9. Copy .htaccess.infinityfree to .htaccess
    | 10. Test all routes and functionality
    |
    */
    
    'deployment' => [
        'checklist' => [
            'env_configured' => false,
            'database_imported' => false,
            'storage_linked' => false,
            'cache_cleared' => false,
            'permissions_set' => false,
        ],
    ],
    
];
