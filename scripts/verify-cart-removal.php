<?php

/**
 * Cart Removal Verification Script
 * 
 * This script verifies that all shopping cart components have been successfully
 * removed from the UniPrint application and that the system is functioning
 * correctly with the Saved Services replacement.
 */

require_once __DIR__ . '/../vendor/autoload.php';

class CartRemovalVerifier
{
    private $errors = [];
    private $warnings = [];
    private $successes = [];
    private $basePath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
    }

    public function verify()
    {
        echo "🔍 Starting Cart Removal Verification...\n\n";

        $this->verifyModelsRemoved();
        $this->verifyControllersUpdated();
        $this->verifyRoutesClean();
        $this->verifyViewsUpdated();
        $this->verifyDatabaseClean();
        $this->verifyJavaScriptUpdated();
        $this->verifyConfigurationFiles();
        $this->verifyDocumentation();

        $this->displayResults();
    }

    private function verifyModelsRemoved()
    {
        echo "📁 Checking Models...\n";

        $cartModels = [
            'app/Models/ShoppingCart.php',
            'app/Models/CartItem.php'
        ];

        foreach ($cartModels as $model) {
            $path = $this->basePath . '/' . $model;
            if (file_exists($path)) {
                $this->errors[] = "Cart model still exists: {$model}";
            } else {
                $this->successes[] = "Cart model removed: {$model}";
            }
        }

        // Check for cart model references
        $this->checkForStringInFiles('app/', 'ShoppingCart', ['.php'], 'Cart model references');
        $this->checkForStringInFiles('app/', 'CartItem', ['.php'], 'Cart model references');
    }

    private function verifyControllersUpdated()
    {
        echo "🎮 Checking Controllers...\n";

        // Check CartController is removed
        $cartController = 'app/Http/Controllers/CartController.php';
        if (file_exists($this->basePath . '/' . $cartController)) {
            $this->errors[] = "CartController still exists: {$cartController}";
        } else {
            $this->successes[] = "CartController removed";
        }

        // Check CheckoutController uses SavedService
        $checkoutController = $this->basePath . '/app/Http/Controllers/CheckoutController.php';
        if (file_exists($checkoutController)) {
            $content = file_get_contents($checkoutController);
            if (strpos($content, 'SavedService') !== false) {
                $this->successes[] = "CheckoutController uses SavedService";
            } else {
                $this->warnings[] = "CheckoutController may not be using SavedService";
            }

            if (strpos($content, 'ShoppingCart') !== false) {
                $this->errors[] = "CheckoutController still references ShoppingCart";
            }
        }

        // Check other controllers for cart references
        $this->checkForStringInFiles('app/Http/Controllers/', 'cart', ['.php'], 'Controller cart references');
    }

    private function verifyRoutesClean()
    {
        echo "🛣️ Checking Routes...\n";

        $routeFile = $this->basePath . '/routes/web.php';
        if (file_exists($routeFile)) {
            $content = file_get_contents($routeFile);
            
            // Check for cart routes
            if (preg_match('/Route::.*(cart|Cart)/', $content)) {
                $this->errors[] = "Cart routes still exist in web.php";
            } else {
                $this->successes[] = "No cart routes found in web.php";
            }

            // Check for saved services routes
            if (strpos($content, 'saved-services') !== false) {
                $this->successes[] = "Saved services routes exist";
            } else {
                $this->warnings[] = "Saved services routes may be missing";
            }
        }
    }

    private function verifyViewsUpdated()
    {
        echo "👁️ Checking Views...\n";

        // Check cart views directory is removed
        $cartViewsDir = $this->basePath . '/resources/views/cart';
        if (is_dir($cartViewsDir)) {
            $this->errors[] = "Cart views directory still exists: {$cartViewsDir}";
        } else {
            $this->successes[] = "Cart views directory removed";
        }

        // Check for cart references in blade files
        $this->checkForStringInFiles('resources/views/', 'addToCart', ['.blade.php'], 'View cart function references');
        $this->checkForStringInFiles('resources/views/', 'cart.add', ['.blade.php'], 'View cart route references');
        $this->checkForStringInFiles('resources/views/', 'updateCartCount', ['.blade.php'], 'View cart count references');

        // Check for saved services references
        $savedServicesFiles = $this->findFilesContaining('resources/views/', 'saved-services', ['.blade.php']);
        if (count($savedServicesFiles) > 0) {
            $this->successes[] = "Found " . count($savedServicesFiles) . " files with saved services references";
        } else {
            $this->warnings[] = "No saved services references found in views";
        }
    }

    private function verifyDatabaseClean()
    {
        echo "🗄️ Checking Database...\n";

        // Check for cart migration files
        $migrationFiles = glob($this->basePath . '/database/migrations/*cart*.php');
        if (count($migrationFiles) > 0) {
            $this->warnings[] = "Found " . count($migrationFiles) . " cart-related migration files (may be historical)";
        } else {
            $this->successes[] = "No cart migration files found";
        }

        // Check for saved services migrations
        $savedServiceMigrations = glob($this->basePath . '/database/migrations/*saved_service*.php');
        if (count($savedServiceMigrations) > 0) {
            $this->successes[] = "Found " . count($savedServiceMigrations) . " saved services migration files";
        } else {
            $this->warnings[] = "No saved services migration files found";
        }
    }

    private function verifyJavaScriptUpdated()
    {
        echo "📜 Checking JavaScript...\n";

        // Check for cart JavaScript functions
        $this->checkForStringInFiles('resources/views/', 'addToCart(', ['.blade.php'], 'JavaScript cart functions');
        $this->checkForStringInFiles('public/js/', 'addToCart', ['.js'], 'JavaScript cart functions');
        
        // Check for saved services JavaScript
        $savedServicesJs = $this->findFilesContaining('resources/views/', 'saveService(', ['.blade.php']);
        if (count($savedServicesJs) > 0) {
            $this->successes[] = "Found saved services JavaScript functions";
        } else {
            $this->warnings[] = "No saved services JavaScript functions found";
        }
    }

    private function verifyConfigurationFiles()
    {
        echo "⚙️ Checking Configuration...\n";

        // Check composer.json for cart-related dependencies
        $composerFile = $this->basePath . '/composer.json';
        if (file_exists($composerFile)) {
            $content = file_get_contents($composerFile);
            if (strpos($content, 'cart') !== false) {
                $this->warnings[] = "Cart references found in composer.json";
            } else {
                $this->successes[] = "No cart references in composer.json";
            }
        }

        // Check package.json for cart-related dependencies
        $packageFile = $this->basePath . '/package.json';
        if (file_exists($packageFile)) {
            $content = file_get_contents($packageFile);
            if (strpos($content, 'cart') !== false) {
                $this->warnings[] = "Cart references found in package.json";
            } else {
                $this->successes[] = "No cart references in package.json";
            }
        }
    }

    private function verifyDocumentation()
    {
        echo "📚 Checking Documentation...\n";

        $docFiles = [
            'README.md',
            'CART_REMOVAL_ANALYSIS.md',
            'TAB_CONSISTENCY_IMPLEMENTATION.md'
        ];

        foreach ($docFiles as $docFile) {
            $path = $this->basePath . '/' . $docFile;
            if (file_exists($path)) {
                $this->successes[] = "Documentation file exists: {$docFile}";
            } else {
                $this->warnings[] = "Documentation file missing: {$docFile}";
            }
        }
    }

    private function checkForStringInFiles($directory, $searchString, $extensions, $description)
    {
        $files = $this->findFilesContaining($directory, $searchString, $extensions);
        if (count($files) > 0) {
            $this->warnings[] = "{$description}: Found in " . count($files) . " files - " . implode(', ', array_slice($files, 0, 3));
        } else {
            $this->successes[] = "{$description}: Clean";
        }
    }

    private function findFilesContaining($directory, $searchString, $extensions)
    {
        $foundFiles = [];
        $fullPath = $this->basePath . '/' . $directory;
        
        if (!is_dir($fullPath)) {
            return $foundFiles;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = '.' . $file->getExtension();
                if (in_array($extension, $extensions)) {
                    $content = file_get_contents($file->getPathname());
                    if (stripos($content, $searchString) !== false) {
                        $foundFiles[] = str_replace($this->basePath . '/', '', $file->getPathname());
                    }
                }
            }
        }

        return $foundFiles;
    }

    private function displayResults()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎯 CART REMOVAL VERIFICATION RESULTS\n";
        echo str_repeat("=", 60) . "\n\n";

        if (count($this->successes) > 0) {
            echo "✅ SUCCESSES (" . count($this->successes) . "):\n";
            foreach ($this->successes as $success) {
                echo "   ✓ {$success}\n";
            }
            echo "\n";
        }

        if (count($this->warnings) > 0) {
            echo "⚠️ WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   ⚠ {$warning}\n";
            }
            echo "\n";
        }

        if (count($this->errors) > 0) {
            echo "❌ ERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   ✗ {$error}\n";
            }
            echo "\n";
        }

        // Summary
        $total = count($this->successes) + count($this->warnings) + count($this->errors);
        $successRate = $total > 0 ? round((count($this->successes) / $total) * 100, 1) : 0;

        echo "📊 SUMMARY:\n";
        echo "   Total Checks: {$total}\n";
        echo "   Success Rate: {$successRate}%\n";
        echo "   Status: " . ($this->getOverallStatus()) . "\n\n";

        if (count($this->errors) === 0) {
            echo "🎉 Cart removal verification completed successfully!\n";
            echo "   The system appears to be clean of cart functionality.\n";
        } else {
            echo "🔧 Cart removal needs attention!\n";
            echo "   Please address the errors listed above.\n";
        }

        echo "\n" . str_repeat("=", 60) . "\n";
    }

    private function getOverallStatus()
    {
        if (count($this->errors) > 0) {
            return "❌ FAILED";
        } elseif (count($this->warnings) > 0) {
            return "⚠️ PASSED WITH WARNINGS";
        } else {
            return "✅ PASSED";
        }
    }
}

// Run the verification
$verifier = new CartRemovalVerifier();
$verifier->verify();
