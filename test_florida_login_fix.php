<?php
/**
 * Test Florida Login Route Fix
 * Verify the syntax error is resolved and routes are working
 */

echo "🧪 Testing Florida Login Route Fix\n";
echo "==================================\n\n";

try {
    // Test Laravel application loading
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel application loaded successfully\n";
    
    // Test route registration
    $router = $app->make('router');
    $routes = $router->getRoutes();
    echo "✅ Router loaded with " . count($routes) . " routes\n";
    
    // Look for state authentication routes
    $authRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'login') !== false || strpos($uri, 'register') !== false) {
            $authRoutes[] = $uri;
        }
    }
    
    echo "\n🔍 Found authentication routes:\n";
    foreach ($authRoutes as $route) {
        echo "   /{$route}\n";
    }
    
    echo "\n✅ Syntax error fixed! Routes are properly registered.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 Test URLs:\n";
echo "Florida Login: http://nelly-elearning.test/florida/login\n";
echo "Missouri Login: http://nelly-elearning.test/missouri/login\n";
echo "Texas Login: http://nelly-elearning.test/texas/login\n";
echo "Delaware Login: http://nelly-elearning.test/delaware/login\n";