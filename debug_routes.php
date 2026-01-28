<?php
/**
 * Debug Routes
 * Check if authentication routes are properly registered
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Debugging Routes\n";
echo "==================\n\n";

try {
    // Get the router
    $router = $app->make('router');
    $routes = $router->getRoutes();
    
    echo "Total routes registered: " . count($routes) . "\n\n";
    
    // Look for authentication routes
    $authRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName();
        $methods = implode('|', $route->methods());
        
        if (strpos($uri, 'login') !== false || strpos($uri, 'register') !== false || strpos($uri, 'dashboard') !== false) {
            $authRoutes[] = [
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'action' => $route->getActionName()
            ];
        }
    }
    
    echo "Authentication-related routes found: " . count($authRoutes) . "\n";
    echo "================================================\n";
    
    foreach ($authRoutes as $route) {
        echo "Method: {$route['methods']}\n";
        echo "URI: /{$route['uri']}\n";
        echo "Name: " . ($route['name'] ?? 'No name') . "\n";
        echo "Action: {$route['action']}\n";
        echo "---\n";
    }
    
    // Check specific routes we need
    echo "\nChecking specific routes:\n";
    echo "========================\n";
    
    $requiredRoutes = [
        'florida/login',
        'missouri/login', 
        'texas/login',
        'delaware/login',
        'florida/register',
        'missouri/register',
        'texas/register',
        'delaware/register'
    ];
    
    foreach ($requiredRoutes as $requiredRoute) {
        $found = false;
        foreach ($authRoutes as $route) {
            if ($route['uri'] === $requiredRoute) {
                $found = true;
                break;
            }
        }
        echo ($found ? '✅' : '❌') . " /{$requiredRoute}\n";
    }
    
    // Check controllers
    echo "\nChecking controllers:\n";
    echo "====================\n";
    
    $controllers = [
        'App\Http\Controllers\Auth\StateAuthController' => 'State Auth Controller',
        'App\Http\Controllers\AuthController' => 'Main Auth Controller'
    ];
    
    foreach ($controllers as $class => $name) {
        if (class_exists($class)) {
            echo "✅ {$name}: Available\n";
        } else {
            echo "❌ {$name}: Missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 Route Debug Complete\n";