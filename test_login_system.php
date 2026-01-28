<?php
/**
 * Test Login System - Check Authentication Setup
 */

echo "🔐 TESTING LOGIN SYSTEM\n";
echo "========================\n\n";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Check if users table exists and has data
    try {
        $userCount = DB::table('users')->count();
        echo "✅ Users table accessible: $userCount users\n";
        
        if ($userCount > 0) {
            $users = DB::table('users')->select('name', 'email', 'role', 'state_code')->get();
            echo "\n👥 EXISTING USERS:\n";
            echo "==================\n";
            foreach ($users as $user) {
                echo "- {$user->name} ({$user->email}) - {$user->role} - {$user->state_code}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Users table error: " . $e->getMessage() . "\n";
    }
    
    // Check authentication configuration
    echo "\n🔧 AUTHENTICATION CONFIG:\n";
    echo "==========================\n";
    
    try {
        $authConfig = config('auth');
        echo "✅ Auth config loaded\n";
        echo "Default guard: " . $authConfig['defaults']['guard'] . "\n";
        echo "Default provider: " . $authConfig['defaults']['passwords'] . "\n";
    } catch (Exception $e) {
        echo "❌ Auth config error: " . $e->getMessage() . "\n";
    }
    
    // Check if AuthController exists
    echo "\n🎮 CONTROLLER CHECK:\n";
    echo "====================\n";
    
    if (class_exists('App\Http\Controllers\AuthController')) {
        echo "✅ AuthController exists\n";
    } else {
        echo "❌ AuthController missing\n";
    }
    
    // Check routes
    echo "\n🛣️ ROUTE CHECK:\n";
    echo "===============\n";
    
    $router = app('router');
    $routes = $router->getRoutes();
    
    $authRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (in_array($uri, ['login', 'logout', 'dashboard'])) {
            $methods = implode('|', $route->methods());
            $authRoutes[] = "$methods /$uri";
        }
    }
    
    if (count($authRoutes) > 0) {
        echo "✅ Auth routes found:\n";
        foreach ($authRoutes as $route) {
            echo "  $route\n";
        }
    } else {
        echo "❌ No auth routes found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🌐 LOGIN INSTRUCTIONS:\n";
echo "======================\n";
echo "1. Run: http://nelly-elearning.test/create_test_users.php\n";
echo "2. Go to: http://nelly-elearning.test/login\n";
echo "3. Use credentials:\n";
echo "   Email: student@test.com\n";
echo "   Password: password123\n";
echo "4. After login, visit state portals:\n";
echo "   - http://nelly-elearning.test/florida\n";
echo "   - http://nelly-elearning.test/missouri\n";
echo "   - http://nelly-elearning.test/texas\n";
echo "   - http://nelly-elearning.test/delaware\n";
?>