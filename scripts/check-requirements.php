#!/usr/bin/env php
<?php

/**
 * UniPrint System Requirements Checker
 * 
 * This script checks if your system meets all requirements to run UniPrint.
 * Run this before installation to identify any missing dependencies.
 */

class RequirementsChecker
{
    private $errors = [];
    private $warnings = [];
    private $passed = [];
    private $isWindows;
    private $isMac;
    private $isLinux;

    public function __construct()
    {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $this->isMac = PHP_OS === 'Darwin';
        $this->isLinux = !$this->isWindows && !$this->isMac;
    }

    public function run()
    {
        $this->printHeader();
        
        echo "\n🔍 Checking System Requirements...\n\n";
        
        $this->checkPHPVersion();
        $this->checkPHPExtensions();
        $this->checkComposer();
        $this->checkNode();
        $this->checkNPM();
        $this->checkDatabase();
        $this->checkFilePermissions();
        $this->checkMemory();
        $this->checkDiskSpace();
        
        $this->printResults();
        
        return empty($this->errors);
    }

    private function printHeader()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║         UniPrint System Requirements Checker              ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        
        echo "\n📊 System Information:\n";
        echo "   OS: " . PHP_OS . " (" . php_uname('s') . " " . php_uname('r') . ")\n";
        echo "   PHP: " . PHP_VERSION . "\n";
        echo "   SAPI: " . PHP_SAPI . "\n\n";
    }

    private function checkPHPVersion()
    {
        $required = '8.2.0';
        $current = PHP_VERSION;
        
        if (version_compare($current, $required, '>=')) {
            $this->passed[] = "✅ PHP Version: $current (>= $required required)";
        } else {
            $this->errors[] = "❌ PHP Version: $current (>= $required required)";
        }
    }

    private function checkPHPExtensions()
    {
        $required = [
            'mbstring' => 'Multi-byte string support',
            'xml' => 'XML processing',
            'curl' => 'HTTP requests',
            'zip' => 'Archive handling',
            'pdo' => 'Database abstraction',
            'tokenizer' => 'Code parsing',
            'json' => 'JSON handling',
            'bcmath' => 'Arbitrary precision math',
            'fileinfo' => 'File type detection',
            'openssl' => 'Encryption',
        ];

        $optional = [
            'gd' => 'Image processing',
            'imagick' => 'Advanced image processing',
            'redis' => 'Redis caching',
            'memcached' => 'Memcached support',
        ];

        $database = [
            'pdo_pgsql' => 'PostgreSQL support',
            'pdo_mysql' => 'MySQL support',
        ];

        echo "📦 Required PHP Extensions:\n";
        foreach ($required as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->passed[] = "✅ $ext ($description)";
                echo "   ✅ $ext\n";
            } else {
                $this->errors[] = "❌ $ext ($description) - REQUIRED";
                echo "   ❌ $ext - MISSING (REQUIRED)\n";
            }
        }

        echo "\n📦 Database Extensions (at least one required):\n";
        $hasDatabase = false;
        foreach ($database as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->passed[] = "✅ $ext ($description)";
                echo "   ✅ $ext\n";
                $hasDatabase = true;
            } else {
                echo "   ⚠️  $ext - Not installed\n";
            }
        }

        if (!$hasDatabase) {
            $this->errors[] = "❌ No database extension found. Install at least one: pdo_pgsql or pdo_mysql";
        }

        echo "\n📦 Optional PHP Extensions:\n";
        foreach ($optional as $ext => $description) {
            if (extension_loaded($ext)) {
                $this->passed[] = "✅ $ext ($description)";
                echo "   ✅ $ext\n";
            } else {
                $this->warnings[] = "⚠️  $ext ($description) - Recommended";
                echo "   ⚠️  $ext - Not installed (Recommended)\n";
            }
        }
        echo "\n";
    }

    private function checkComposer()
    {
        $output = [];
        $return = 0;
        
        exec('composer --version 2>&1', $output, $return);
        
        if ($return === 0 && !empty($output)) {
            $version = $this->extractVersion($output[0]);
            $this->passed[] = "✅ Composer: $version";
            echo "✅ Composer: $version\n";
        } else {
            $this->errors[] = "❌ Composer not found or not in PATH";
            echo "❌ Composer not found or not in PATH\n";
            echo "   Install from: https://getcomposer.org\n";
        }
    }

    private function checkNode()
    {
        $output = [];
        $return = 0;
        
        exec('node --version 2>&1', $output, $return);
        
        if ($return === 0 && !empty($output)) {
            $version = trim($output[0]);
            $required = 'v18.0.0';
            
            if (version_compare(ltrim($version, 'v'), ltrim($required, 'v'), '>=')) {
                $this->passed[] = "✅ Node.js: $version (>= $required required)";
                echo "✅ Node.js: $version\n";
            } else {
                $this->errors[] = "❌ Node.js: $version (>= $required required)";
                echo "❌ Node.js: $version (>= $required required)\n";
            }
        } else {
            $this->errors[] = "❌ Node.js not found or not in PATH";
            echo "❌ Node.js not found or not in PATH\n";
            echo "   Install from: https://nodejs.org\n";
        }
    }

    private function checkNPM()
    {
        $output = [];
        $return = 0;
        
        exec('npm --version 2>&1', $output, $return);
        
        if ($return === 0 && !empty($output)) {
            $version = trim($output[0]);
            $this->passed[] = "✅ NPM: $version";
            echo "✅ NPM: $version\n";
        } else {
            $this->errors[] = "❌ NPM not found or not in PATH";
            echo "❌ NPM not found or not in PATH\n";
        }
    }

    private function checkDatabase()
    {
        echo "\n🗄️  Database Servers:\n";
        
        // Check PostgreSQL
        $output = [];
        $return = 0;
        exec('psql --version 2>&1', $output, $return);
        
        if ($return === 0 && !empty($output)) {
            $version = $this->extractVersion($output[0]);
            echo "   ✅ PostgreSQL: $version\n";
        } else {
            echo "   ⚠️  PostgreSQL not found in PATH\n";
        }
        
        // Check MySQL
        $output = [];
        $return = 0;
        exec('mysql --version 2>&1', $output, $return);
        
        if ($return === 0 && !empty($output)) {
            $version = $this->extractVersion($output[0]);
            echo "   ✅ MySQL: $version\n";
        } else {
            echo "   ⚠️  MySQL not found in PATH\n";
        }
        
        echo "\n";
    }

    private function checkFilePermissions()
    {
        if ($this->isWindows) {
            echo "⚠️  File permissions check skipped on Windows\n";
            return;
        }

        $directories = [
            'storage',
            'storage/app',
            'storage/framework',
            'storage/logs',
            'bootstrap/cache',
        ];

        $allWritable = true;
        foreach ($directories as $dir) {
            if (file_exists($dir)) {
                if (is_writable($dir)) {
                    echo "✅ $dir is writable\n";
                } else {
                    $this->errors[] = "❌ $dir is not writable";
                    echo "❌ $dir is not writable\n";
                    $allWritable = false;
                }
            }
        }

        if ($allWritable && !empty($directories)) {
            $this->passed[] = "✅ File permissions are correct";
        }
    }

    private function checkMemory()
    {
        $memory = ini_get('memory_limit');
        $memoryBytes = $this->convertToBytes($memory);
        $required = 512 * 1024 * 1024; // 512MB

        if ($memoryBytes >= $required || $memory === '-1') {
            $this->passed[] = "✅ Memory Limit: $memory";
            echo "✅ Memory Limit: $memory\n";
        } else {
            $this->warnings[] = "⚠️  Memory Limit: $memory (512M recommended)";
            echo "⚠️  Memory Limit: $memory (512M recommended)\n";
        }
    }

    private function checkDiskSpace()
    {
        $free = disk_free_space('.');
        $freeGB = round($free / (1024 * 1024 * 1024), 2);
        $required = 0.5; // 500MB

        if ($freeGB >= $required) {
            $this->passed[] = "✅ Free Disk Space: {$freeGB}GB";
            echo "✅ Free Disk Space: {$freeGB}GB\n";
        } else {
            $this->errors[] = "❌ Free Disk Space: {$freeGB}GB (0.5GB minimum required)";
            echo "❌ Free Disk Space: {$freeGB}GB (0.5GB minimum required)\n";
        }
    }

    private function printResults()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║                    RESULTS SUMMARY                         ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        echo "✅ Passed: " . count($this->passed) . "\n";
        echo "⚠️  Warnings: " . count($this->warnings) . "\n";
        echo "❌ Errors: " . count($this->errors) . "\n\n";

        if (!empty($this->errors)) {
            echo "❌ CRITICAL ISSUES (Must be fixed):\n";
            foreach ($this->errors as $error) {
                echo "   $error\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS (Recommended to fix):\n";
            foreach ($this->warnings as $warning) {
                echo "   $warning\n";
            }
            echo "\n";
        }

        if (empty($this->errors)) {
            echo "🎉 SUCCESS! Your system meets all requirements.\n";
            echo "   You can proceed with the installation.\n\n";
            echo "Next steps:\n";
            echo "   1. Run: composer install\n";
            echo "   2. Run: npm install\n";
            echo "   3. Copy .env.example to .env\n";
            echo "   4. Run: php artisan key:generate\n";
            echo "   5. Configure database in .env\n";
            echo "   6. Run: php artisan migrate --seed\n";
            echo "   7. Run: npm run build\n";
            echo "   8. Run: php artisan serve\n\n";
        } else {
            echo "❌ FAILED! Please fix the errors above before proceeding.\n\n";
            echo "Installation guides:\n";
            echo "   - Windows: See INSTALLATION.md (Windows section)\n";
            echo "   - macOS: See INSTALLATION.md (macOS section)\n";
            echo "   - Linux: See INSTALLATION.md (Linux section)\n\n";
        }
    }

    private function extractVersion($string)
    {
        if (preg_match('/(\d+\.\d+\.\d+)/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }

    private function convertToBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}

// Run the checker
$checker = new RequirementsChecker();
$success = $checker->run();

exit($success ? 0 : 1);
