<?php
/**
 * Clear Laravel Cache - Fix Service Provider Issues
 */

echo "🧹 CLEARING LARAVEL CACHE\n";
echo "==========================\n\n";

// Function to delete directory contents
function clearDirectory($dir) {
    if (!is_dir($dir)) {
        echo "⚪ Directory not found: $dir\n";
        return;
    }
    
    $files = glob($dir . '/*');
    $count = 0;
    
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        } elseif (is_dir($file)) {
            clearDirectory($file);
            rmdir($file);
            $count++;
        }
    }
    
    echo "✅ Cleared $count items from: $dir\n";
}

// Clear cache directories
$cacheDirectories = [
    __DIR__ . '/storage/framework/cache/data',
    __DIR__ . '/storage/framework/sessions',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/bootstrap/cache'
];

foreach ($cacheDirectories as $dir) {
    clearDirectory($dir);
}

// Clear specific cache files
$cacheFiles = [
    __DIR__ . '/bootstrap/cache/config.php',
    __DIR__ . '/bootstrap/cache/routes-v7.php',
    __DIR__ . '/bootstrap/cache/services.php',
    __DIR__ . '/bootstrap/cache/packages.php'
];

foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "✅ Deleted cache file: " . basename($file) . "\n";
    }
}

echo "\n🔧 TESTING LARAVEL AFTER CACHE CLEAR:\n";
echo "======================================\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer autoload loaded\n";
    
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel app created\n";
    
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "✅ Laravel kernel bootstrapped\n";
    
    // Test view service
    try {
        $view = app('view');
        echo "✅ View service working: " . get_class($view) . "\n";
        echo "🎉 LARAVEL IS NOW WORKING!\n";
    } catch (Exception $e) {
        echo "❌ View service still broken: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Laravel still broken: " . $e->getMessage() . "\n";
}

echo "\n🌐 NOW TEST THESE URLS:\n";
echo "=======================\n";
echo "http://nelly-elearning.test/test-laravel.php\n";
echo "http://nelly-elearning.test/florida.php\n";
echo "http://nelly-elearning.test/missouri.php\n";
echo "http://nelly-elearning.test/texas.php\n";
echo "http://nelly-elearning.test/delaware.php\n";

echo "\n✅ CACHE CLEARING COMPLETE!\n";
?>