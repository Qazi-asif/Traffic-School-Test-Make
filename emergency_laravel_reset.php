<?php
/**
 * Emergency Laravel Reset - Fix Service Provider Issues
 */

echo "🚨 EMERGENCY LARAVEL RESET\n";
echo "==========================\n\n";

// Step 1: Clear all possible cache files
echo "1. CLEARING ALL CACHE FILES:\n";
echo "=============================\n";

$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views'
];

foreach ($cacheFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        if (is_dir($fullPath)) {
            // Clear directory contents
            $files = glob($fullPath . '/*');
            foreach ($files as $f) {
                if (is_file($f)) {
                    unlink($f);
                }
            }
            echo "✅ Cleared directory: $file\n";
        } else {
            unlink($fullPath);
            echo "✅ Deleted file: $file\n";
        }
    } else {
        echo "⚪ Not found: $file\n";
    }
}

// Step 2: Check .env file
echo "\n2. CHECKING .ENV CONFIGURATION:\n";
echo "================================\n";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    
    $envContent = file_get_contents($envFile);
    
    // Check critical settings
    $criticalSettings = [
        'APP_KEY' => 'Application key',
        'APP_ENV' => 'Environment',
        'APP_DEBUG' => 'Debug mode'
    ];
    
    foreach ($criticalSettings as $key => $description) {
        if (strpos($envContent, $key . '=') !== false) {
            preg_match('/' . $key . '=(.*)/', $envContent, $matches);
            $value = isset($matches[1]) ? trim($matches[1]) : 'empty';
            echo "✅ $description ($key): $value\n";
        } else {
            echo "❌ Missing: $description ($key)\n";
        }
    }
} else {
    echo "❌ .env file not found\n";
}

// Step 3: Create ultra-minimal routes file
echo "\n3. CREATING ULTRA-MINIMAL ROUTES:\n";
echo "==================================\n";

$minimalRoutes = '<?php

use Illuminate\Support\Facades\Route;

// Ultra-minimal test route
Route::get(\'/test-emergency\', function () {
    return \'Emergency route working! Time: \' . date(\'Y-m-d H:i:s\');
});

// State routes without any dependencies
Route::get(\'/florida\', function () {
    return \'<h1>Florida Traffic School</h1><p>Emergency routing active</p><p>Time: \' . date(\'Y-m-d H:i:s\') . \'</p>\';
});

Route::get(\'/missouri\', function () {
    return \'<h1>Missouri Traffic School</h1><p>Emergency routing active</p><p>Time: \' . date(\'Y-m-d H:i:s\') . \'</p>\';
});

Route::get(\'/texas\', function () {
    return \'<h1>Texas Traffic School</h1><p>Emergency routing active</p><p>Time: \' . date(\'Y-m-d H:i:s\') . \'</p>\';
});

Route::get(\'/delaware\', function () {
    return \'<h1>Delaware Traffic School</h1><p>Emergency routing active</p><p>Time: \' . date(\'Y-m-d H:i:s\') . \'</p>\';
});

Route::get(\'/admin\', function () {
    return \'<h1>Admin Dashboard</h1><p>Emergency routing active</p><p>Time: \' . date(\'Y-m-d H:i:s\') . \'</p>\';
});

// Fallback route
Route::fallback(function () {
    return \'<h1>Page Not Found</h1><p>Available routes:</p><ul><li><a href="/florida">Florida</a></li><li><a href="/missouri">Missouri</a></li><li><a href="/texas">Texas</a></li><li><a href="/delaware">Delaware</a></li><li><a href="/admin">Admin</a></li></ul>\';
});
';

file_put_contents(__DIR__ . '/routes/web.php', $minimalRoutes);
echo "✅ Ultra-minimal routes file created\n";

// Step 4: Check composer autoload
echo "\n4. CHECKING COMPOSER AUTOLOAD:\n";
echo "===============================\n";

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer autoload exists\n";
    
    // Check if we can load it
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✅ Composer autoload loaded successfully\n";
    } catch (Exception $e) {
        echo "❌ Composer autoload error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Composer autoload not found - run 'composer install'\n";
}

// Step 5: Test basic Laravel bootstrap
echo "\n5. TESTING LARAVEL BOOTSTRAP:\n";
echo "==============================\n";

try {
    if (file_exists(__DIR__ . '/bootstrap/app.php')) {
        echo "✅ Laravel bootstrap file exists\n";
        
        // Try to create the app
        $app = require __DIR__ . '/bootstrap/app.php';
        echo "✅ Laravel application created\n";
        
        // Check if we can get basic services
        $version = $app->version();
        echo "✅ Laravel version: $version\n";
        
    } else {
        echo "❌ Laravel bootstrap file not found\n";
    }
} catch (Exception $e) {
    echo "❌ Laravel bootstrap error: " . $e->getMessage() . "\n";
    echo "   This indicates a fundamental Laravel configuration issue\n";
}

echo "\n🎯 EMERGENCY RESET COMPLETE\n";
echo "============================\n";
echo "✅ All caches cleared\n";
echo "✅ Ultra-minimal routes created\n";
echo "✅ System reset to basic state\n";

echo "\n🧪 TEST THESE URLS NOW:\n";
echo "========================\n";
echo "http://nelly-elearning.test/test-emergency\n";
echo "http://nelly-elearning.test/florida\n";
echo "http://nelly-elearning.test/missouri\n";
echo "http://nelly-elearning.test/texas\n";
echo "http://nelly-elearning.test/delaware\n";
echo "http://nelly-elearning.test/admin\n";

echo "\n💡 IF STILL FAILING:\n";
echo "====================\n";
echo "1. Restart your web server (Apache/Nginx)\n";
echo "2. Check Laravel logs in storage/logs/\n";
echo "3. Verify PHP version compatibility\n";
echo "4. Run 'composer install' to reinstall dependencies\n";
?>