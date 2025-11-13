<?php
/**
 * URL Accessibility Test Script
 * Tests various URLs to ensure proper routing and accessibility
 */

echo "=== UniPrint URL Accessibility Test ===\n\n";

// Test URLs
$testUrls = [
    'Basic Routes' => [
        '/' => 'Homepage',
        '/login' => 'Login page',
        '/register' => 'Registration page',
    ],
    'API Routes' => [
        '/api/user' => 'User API endpoint',
        '/api/health' => 'Health check endpoint',
    ],
    'Customer Routes' => [
        '/customer/dashboard' => 'Customer dashboard',
        '/customer/orders' => 'Customer orders',
        '/customer/enterprises' => 'Browse enterprises',
    ],
    'Business Routes' => [
        '/business/dashboard' => 'Business dashboard',
        '/business/products' => 'Business products',
        '/business/orders' => 'Business orders',
    ],
    'Admin Routes' => [
        '/admin/dashboard' => 'Admin dashboard',
        '/admin/users' => 'User management',
        '/admin/enterprises' => 'Enterprise management',
    ],
    'Static Assets' => [
        '/css/app.css' => 'Main stylesheet',
        '/js/app.js' => 'Main JavaScript',
        '/favicon.ico' => 'Favicon',
    ]
];

// Base URL (adjust as needed)
$baseUrl = 'http://localhost:8000';

// Function to test URL accessibility
function testUrl($url, $description) {
    global $baseUrl;
    $fullUrl = $baseUrl . $url;
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'UniPrint URL Tester/1.0');
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Determine status
    if ($error) {
        return ['status' => 'ERROR', 'message' => $error, 'code' => 0];
    } elseif ($httpCode >= 200 && $httpCode < 400) {
        return ['status' => 'OK', 'message' => 'Accessible', 'code' => $httpCode];
    } elseif ($httpCode == 404) {
        return ['status' => 'NOT_FOUND', 'message' => 'Route not found', 'code' => $httpCode];
    } elseif ($httpCode >= 400 && $httpCode < 500) {
        return ['status' => 'CLIENT_ERROR', 'message' => 'Client error', 'code' => $httpCode];
    } elseif ($httpCode >= 500) {
        return ['status' => 'SERVER_ERROR', 'message' => 'Server error', 'code' => $httpCode];
    } else {
        return ['status' => 'UNKNOWN', 'message' => 'Unknown status', 'code' => $httpCode];
    }
}

// Function to display status with colors
function displayStatus($status, $code) {
    switch ($status) {
        case 'OK':
            return "✓ OK ($code)";
        case 'NOT_FOUND':
            return "✗ 404 Not Found";
        case 'CLIENT_ERROR':
            return "⚠ Client Error ($code)";
        case 'SERVER_ERROR':
            return "✗ Server Error ($code)";
        case 'ERROR':
            return "✗ Connection Error";
        default:
            return "? Unknown ($code)";
    }
}

// Check if server is running
echo "Testing server connectivity...\n";
$serverTest = testUrl('/', 'Server check');
if ($serverTest['status'] === 'ERROR') {
    echo "ERROR: Cannot connect to server at $baseUrl\n";
    echo "Make sure the development server is running with: php artisan serve\n\n";
    exit(1);
} else {
    echo "✓ Server is responding at $baseUrl\n\n";
}

// Test all URLs
$totalTests = 0;
$passedTests = 0;

foreach ($testUrls as $category => $urls) {
    echo "=== $category ===\n";
    
    foreach ($urls as $url => $description) {
        $totalTests++;
        $result = testUrl($url, $description);
        $status = displayStatus($result['status'], $result['code']);
        
        if ($result['status'] === 'OK') {
            $passedTests++;
        }
        
        printf("%-30s %-40s %s\n", $url, $description, $status);
    }
    
    echo "\n";
}

// Summary
echo "=== Test Summary ===\n";
echo "Total tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";

$successRate = ($totalTests > 0) ? round(($passedTests / $totalTests) * 100, 1) : 0;
echo "Success rate: $successRate%\n\n";

if ($successRate >= 80) {
    echo "✓ URL accessibility looks good!\n";
} elseif ($successRate >= 60) {
    echo "⚠ Some URLs may need attention.\n";
} else {
    echo "✗ Many URLs are not accessible. Check your configuration.\n";
}

echo "\n=== Configuration Check ===\n";

// Check .htaccess files
echo ".htaccess (root): " . (file_exists('.htaccess') ? "✓ Present" : "✗ Missing") . "\n";
echo ".htaccess (public): " . (file_exists('public/.htaccess') ? "✓ Present" : "✗ Missing") . "\n";
echo ".env file: " . (file_exists('.env') ? "✓ Present" : "✗ Missing") . "\n";

// Check Laravel installation
if (file_exists('artisan')) {
    echo "Laravel installation: ✓ Present\n";
} else {
    echo "Laravel installation: ✗ Missing artisan file\n";
}

echo "\n=== Recommendations ===\n";

if (!file_exists('.htaccess')) {
    echo "- Create .htaccess file in root directory\n";
}

if (!file_exists('public/.htaccess')) {
    echo "- Ensure public/.htaccess exists and is configured\n";
}

if (!file_exists('.env')) {
    echo "- Copy .env.development to .env\n";
    echo "- Run: php artisan key:generate\n";
}

if ($successRate < 80) {
    echo "- Check Apache mod_rewrite is enabled\n";
    echo "- Verify .htaccess files are being read\n";
    echo "- Check Laravel routes are properly defined\n";
    echo "- Ensure database is connected and migrated\n";
}

echo "\nFor detailed setup instructions, see: DEVELOPMENT_SETUP_GUIDE.md\n";
?>
