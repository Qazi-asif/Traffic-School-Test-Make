<?php
/**
 * Test All Routes - Check Route Registration
 */

echo "🧪 TESTING ALL ROUTES\n";
echo "=====================\n\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrapped\n";
    
    // Clear route cache
    try {
        Artisan::call('route:clear');
        echo "✅ Route cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Route clear failed: " . $e->getMessage() . "\n";
    }
    
    // Get all routes
    $router = app('router');
    $routes = $router->getRoutes();
    
    echo "\n📋 CHECKING STATE ROUTES:\n";
    echo "=========================\n";
    
    $stateRoutes = [];
    $testRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $methods = implode('|', $route->methods());
        $name = $route->getName();
        
        // Check for state routes
        if (preg_match('/^(florida|missouri|texas|delaware|admin)/', $uri)) {
            $stateRoutes[] = [
                'uri' => $uri,
                'methods' => $methods,
                'name' => $name,
                'action' => $route->getActionName()
            ];
        }
        
        // Check for test routes
        if (strpos($uri, 'test') !== false) {
            $testRoutes[] = [
                'uri' => $uri,
                'methods' => $methods,
                'name' => $name
            ];
        }
    }
    
    echo "State routes found: " . count($stateRoutes) . "\n";
    echo "Test routes found: " . count($testRoutes) . "\n\n";
    
    if (count($stateRoutes) > 0) {
        echo "🏛️ STATE ROUTES:\n";
        echo "================\n";
        foreach ($stateRoutes as $route) {
            echo "  {$route['methods']} /{$route['uri']}";
            if ($route['name']) echo " ({$route['name']})";
            echo "\n";
        }
    }
    
    if (count($testRoutes) > 0) {
        echo "\n🧪 TEST ROUTES:\n";
        echo "===============\n";
        foreach ($testRoutes as $route) {
            echo "  {$route['methods']} /{$route['uri']}";
            if ($route['name']) echo " ({$route['name']})";
            echo "\n";
        }
    }
    
    // Test specific routes
    echo "\n🎯 ROUTE TESTING:\n";
    echo "=================\n";
    
    $testUrls = [
        'florida',
        'florida/test', 
        'missouri',
        'missouri/test',
        'texas',
        'texas/test',
        'delaware',
        'delaware/test',
        'admin',
        'admin/test'
    ];
    
    foreach ($testUrls as $url) {
        try {
            $route = $router->getRoutes()->match(
                \Illuminate\Http\Request::create('/' . $url, 'GET')
            );
            echo "✅ /$url - Route found: " . $route->getName() . "\n";
        } catch (Exception $e) {
            echo "❌ /$url - Route not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🌐 TEST THESE URLS IN BROWSER:\n";
echo "===============================\n";
echo "http://nelly-elearning.test/florida\n";
echo "http://nelly-elearning.test/florida/test\n";
echo "http://nelly-elearning.test/missouri\n";
echo "http://nelly-elearning.test/missouri/test\n";
echo "http://nelly-elearning.test/texas\n";
echo "http://nelly-elearning.test/texas/test\n";
echo "http://nelly-elearning.test/delaware\n";
echo "http://nelly-elearning.test/delaware/test\n";
echo "http://nelly-elearning.test/admin\n";
echo "http://nelly-elearning.test/admin/test\n";
?>