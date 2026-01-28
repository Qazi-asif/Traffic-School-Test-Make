<?php
/**
 * Test State Routes - Run this to verify routing is working
 */

echo "🧪 TESTING STATE ROUTES\n";
echo "======================\n\n";

// Test if we can access Laravel
try {
    if (!function_exists('app')) {
        // Try to bootstrap Laravel
        require_once __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    }
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Test route registration
    $router = app('router');
    $routes = $router->getRoutes();
    
    $stateRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        
        if (preg_match('/^(florida|missouri|texas|delaware)/', $uri)) {
            $stateRoutes[] = "$methods /$uri";
        }
    }
    
    if (count($stateRoutes) > 0) {
        echo "✅ Found " . count($stateRoutes) . " state routes:\n";
        foreach (array_slice($stateRoutes, 0, 10) as $route) {
            echo "   $route\n";
        }
        if (count($stateRoutes) > 10) {
            echo "   ... and " . (count($stateRoutes) - 10) . " more\n";
        }
    } else {
        echo "❌ No state routes found!\n";
        echo "   This means the route files aren't being loaded properly.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🌐 TEST THESE URLS:\n";
echo "==================\n";
echo "✅ http://nelly-elearning.test/florida\n";
echo "✅ http://nelly-elearning.test/florida/test\n";
echo "✅ http://nelly-elearning.test/missouri\n";
echo "✅ http://nelly-elearning.test/texas\n";
echo "✅ http://nelly-elearning.test/delaware\n";

echo "\n💡 TROUBLESHOOTING:\n";
echo "===================\n";
echo "If routes still don't work:\n";
echo "1. Clear Laravel cache: php artisan route:clear\n";
echo "2. Clear config cache: php artisan config:clear\n";
echo "3. Restart web server\n";
echo "4. Check Laravel logs: storage/logs/laravel.log\n";

echo "\n🔧 QUICK FIX COMMANDS:\n";
echo "======================\n";
echo "php artisan route:clear && php artisan config:clear && php artisan cache:clear\n";
?>