<?php
/**
 * Clear All Laravel Cache - Emergency Cache Clear
 */

echo "🔄 EMERGENCY CACHE CLEAR\n";
echo "========================\n\n";

// Method 1: Try Artisan commands
echo "1️⃣ Trying Artisan commands...\n";

$commands = [
    'config:clear' => 'Config cache',
    'route:clear' => 'Route cache', 
    'view:clear' => 'View cache',
    'cache:clear' => 'Application cache',
    'optimize:clear' => 'All optimization cache'
];

foreach ($commands as $command => $description) {
    try {
        $output = shell_exec("php artisan $command 2>&1");
        if ($output !== null) {
            echo "✅ $description cleared\n";
        } else {
            echo "⚠️ $description - command may have failed\n";
        }
    } catch (Exception $e) {
        echo "❌ $description failed: " . $e->getMessage() . "\n";
    }
}

// Method 2: Manual cache clearing
echo "\n2️⃣ Manual cache clearing...\n";

$cacheDirectories = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php', 
    'bootstrap/cache/services.php',
    'storage/framework/cache',
    'storage/framework/views',
    'storage/framework/sessions'
];

foreach ($cacheDirectories as $path) {
    if (file_exists($path)) {
        if (is_file($path)) {
            unlink($path);
            echo "✅ Deleted cache file: $path\n";
        } elseif (is_dir($path)) {
            $files = glob($path . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "✅ Cleared cache directory: $path\n";
        }
    } else {
        echo "ℹ️ Cache path not found: $path\n";
    }
}

echo "\n3️⃣ Testing route registration...\n";

try {
    // Test if we can load Laravel
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        
        // Test route loading
        $routes = $app->make('router')->getRoutes();
        $floridaRoutes = 0;
        
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'florida') === 0) {
                $floridaRoutes++;
            }
        }
        
        if ($floridaRoutes > 0) {
            echo "✅ Found $floridaRoutes Florida routes registered\n";
        } else {
            echo "❌ No Florida routes found\n";
        }
        
    } else {
        echo "⚠️ Laravel not found in current directory\n";
    }
    
} catch (Exception $e) {
    echo "❌ Route test failed: " . $e->getMessage() . "\n";
}

echo "\n🌐 NOW TRY THESE URLS:\n";
echo "======================\n";
echo "http://nelly-elearning.test/florida\n";
echo "http://nelly-elearning.test/missouri\n";
echo "http://nelly-elearning.test/texas\n";
echo "http://nelly-elearning.test/delaware\n";

echo "\n✅ Cache clearing complete!\n";
echo "If routes still don't work, restart your web server.\n";
?>