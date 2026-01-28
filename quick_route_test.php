<?php
/**
 * Quick Route Test - Test if Laravel routes are working
 */

echo "🧪 Testing Laravel Route System\n";
echo "==============================\n\n";

// Test if we can load Laravel
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    
    echo "✅ Laravel application loaded successfully\n";
    
    // Test route registration
    $router = $app->make('router');
    $routes = $router->getRoutes();
    
    echo "✅ Router loaded with " . count($routes) . " routes\n";
    
    // Look for our test routes
    $testRoutes = [
        '/florida-simple',
        '/missouri-simple', 
        '/texas-simple',
        '/delaware-simple',
        '/admin-simple'
    ];
    
    echo "\n🔍 Checking for test routes:\n";
    
    foreach ($testRoutes as $route) {
        $found = false;
        foreach ($routes as $r) {
            if ($r->uri() === ltrim($route, '/')) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "✅ Found: {$route}\n";
        } else {
            echo "❌ Missing: {$route}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Next: Visit http://nelly-elearning.test/florida-simple in your browser\n";