<?php
/**
 * Test Laravel Directly - Bypass Routing Issues
 */

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html><html><head><title>Laravel Test</title>";
echo "<style>body{font-family:Arial;margin:40px;} .success{color:green;background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;} .error{color:red;background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;}</style>";
echo "</head><body>";

echo "<h1>🔧 Laravel Direct Test</h1>";

// Test 1: Basic PHP
echo "<div class='success'>✅ PHP " . PHP_VERSION . " working</div>";

// Test 2: Composer Autoload
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<div class='success'>✅ Composer autoload loaded</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Composer autoload failed: " . $e->getMessage() . "</div>";
    exit;
}

// Test 3: Laravel Bootstrap
try {
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "<div class='success'>✅ Laravel app created (version " . $app->version() . ")</div>";
    
    // Test 4: Kernel Bootstrap
    try {
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->bootstrap();
        echo "<div class='success'>✅ Laravel kernel bootstrapped</div>";
        
        // Test 5: View Service
        try {
            $view = app('view');
            echo "<div class='success'>✅ View service working: " . get_class($view) . "</div>";
            
            // Test 6: Database
            try {
                $db = app('db');
                $pdo = $db->connection()->getPdo();
                echo "<div class='success'>✅ Database connected: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</div>";
                
                // Test 7: Simple Query
                try {
                    $userCount = DB::table('users')->count();
                    echo "<div class='success'>✅ Database query works: $userCount users</div>";
                } catch (Exception $e) {
                    echo "<div class='error'>❌ Database query failed: " . $e->getMessage() . "</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ View service failed: " . $e->getMessage() . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Kernel bootstrap failed: " . $e->getMessage() . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Laravel bootstrap failed: " . $e->getMessage() . "</div>";
}

echo "<h2>🌐 Test State Routes</h2>";
echo "<p>If Laravel is working above, test these:</p>";
echo "<ul>";
echo "<li><a href='/florida.php'>Florida PHP</a></li>";
echo "<li><a href='/missouri.php'>Missouri PHP</a></li>";
echo "<li><a href='/texas.php'>Texas PHP</a></li>";
echo "<li><a href='/delaware.php'>Delaware PHP</a></li>";
echo "</ul>";

echo "</body></html>";
?>