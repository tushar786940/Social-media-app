<?php

/**
 * Configuration Check Script
 * 
 * This script verifies the Laravel application configuration
 * Run with: php config-check.php
 */

echo "==========================================\n";
echo "Laravel Configuration Check\n";
echo "==========================================\n\n";

// Check PHP version
$phpVersion = PHP_VERSION;
echo "PHP Version: $phpVersion\n";
if (version_compare($phpVersion, '8.3.0', '>=')) {
    echo "  ✓ PHP version is compatible (>= 8.3.0)\n";
} else {
    echo "  ✗ PHP version is too old (requires >= 8.3.0)\n";
}
echo "\n";

// Check Composer
$composerPath = __DIR__ . '/composer.json';
if (file_exists($composerPath)) {
    $composer = json_decode(file_get_contents($composerPath), true);
    echo "Composer Configuration:\n";
    echo "  - Name: " . ($composer['name'] ?? 'N/A') . "\n";
    echo "  - Laravel Version: " . ($composer['require']['laravel/framework'] ?? 'N/A') . "\n";
    echo "  - PHP Version: " . ($composer['require']['php'] ?? 'N/A') . "\n";
    
    // Check dependencies
    $dependencies = $composer['require'] ?? [];
    echo "\n  Required Dependencies:\n";
    foreach ($dependencies as $name => $version) {
        echo "    - $name: $version\n";
    }
    
    $devDependencies = $composer['require-dev'] ?? [];
    echo "\n  Dev Dependencies:\n";
    foreach ($devDependencies as $name => $version) {
        echo "    - $name: $version\n";
    }
} else {
    echo "✗ Composer.json not found\n";
}
echo "\n";

// Check Package.json
$packagePath = __DIR__ . '/package.json';
if (file_exists($packagePath)) {
    $package = json_decode(file_get_contents($packagePath), true);
    echo "NPM Configuration:\n";
    echo "  - Name: " . ($package['name'] ?? 'N/A') . "\n";
    echo "  - Type: " . ($package['type'] ?? 'N/A') . "\n";
    
    $devDeps = $package['devDependencies'] ?? [];
    echo "\n  Dev Dependencies:\n";
    foreach ($devDeps as $name => $version) {
        echo "    - $name: $version\n";
    }
    
    $scripts = $package['scripts'] ?? [];
    echo "\n  Available Scripts:\n";
    foreach ($scripts as $name => $command) {
        echo "    - $name: $command\n";
    }
} else {
    echo "✗ Package.json not found\n";
}
echo "\n";

// Check Environment File
$envPath = __DIR__ . '/.env';
$envExamplePath = __DIR__ . '/.env.example';

echo "Environment Configuration:\n";
if (file_exists($envPath)) {
    echo "  ✓ .env file exists\n";
    
    // Check for required env variables
    $envContent = file_get_contents($envPath);
    $requiredVars = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'APP_URL',
        'DB_CONNECTION',
    ];
    
    foreach ($requiredVars as $var) {
        if (strpos($envContent, $var) !== false) {
            echo "  ✓ $var is defined\n";
        } else {
            echo "  ✗ $var is missing\n";
        }
    }
} else {
    echo "  ✗ .env file not found\n";
}

if (file_exists($envExamplePath)) {
    echo "  ✓ .env.example file exists\n";
} else {
    echo "  ✗ .env.example file not found\n";
}
echo "\n";

// Check Directory Structure
echo "Directory Structure:\n";
$directories = [
    'app/Http/Controllers',
    'app/Models',
    'app/Providers',
    'resources/views',
    'resources/css',
    'resources/js',
    'routes',
    'config',
    'database/migrations',
    'database/seeders',
    'database/factories',
    'public',
    'storage/app',
    'tests/Feature',
    'tests/Unit',
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "  ✓ $dir\n";
    } else {
        echo "  ✗ $dir (missing)\n";
    }
}
echo "\n";

// Check Key Files
echo "Key Files:\n";
$keyFiles = [
    'app/Models/User.php',
    'app/Providers/AppServiceProvider.php',
    'app/Http/Controllers/Controller.php',
    'routes/web.php',
    'resources/views/welcome.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/css/app.css',
    'resources/js/app.js',
    'vite.config.js',
    'composer.json',
    'package.json',
    'phpunit.xml',
];

foreach ($keyFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✓ $file\n";
    } else {
        echo "  ✗ $file (missing)\n";
    }
}
echo "\n";

// Check Routes
echo "Routes Configuration:\n";
$routesPath = __DIR__ . '/routes/web.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    // Check for common routes
    $routeChecks = [
        '/' => 'Home route',
        'home' => 'Named home route',
        'search' => 'Search route',
        'health' => 'Health check route',
    ];
    
    foreach ($routeChecks as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            echo "  ✓ $description ($route)\n";
        } else {
            echo "  - $description (not found)\n";
        }
    }
} else {
    echo "  ✗ routes/web.php not found\n";
}
echo "\n";

// Check View Files
echo "View Files:\n";
$viewDirectories = [
    'resources/views/layouts',
    'resources/views/components',
    'resources/views/errors',
    'resources/views/pages',
];

foreach ($viewDirectories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        $fileCount = count(array_filter($files, function($f) use ($fullPath) {
            return is_file($fullPath . '/' . $f) && $f !== '.gitkeep';
        }));
        echo "  ✓ $dir ($fileCount files)\n";
    } else {
        echo "  ✗ $dir (missing)\n";
    }
}
echo "\n";

// Check Configuration Files
echo "Configuration Files:\n";
$configFiles = [
    'app.php',
    'auth.php',
    'database.php',
    'filesystems.php',
    'view.php',
];

foreach ($configFiles as $file) {
    $fullPath = __DIR__ . '/config/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✓ config/$file\n";
    } else {
        echo "  ✗ config/$file (missing)\n";
    }
}
echo "\n";

echo "==========================================\n";
echo "Configuration Check Complete\n";
echo "==========================================\n";
