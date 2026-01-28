<?php
/**
 * Simple PHP Info and Laravel Diagnostic Page
 * This should work immediately with Laragon
 */

echo "<h1>🔧 Laravel Diagnostic Page</h1>";
echo "<p><strong>Status:</strong> PHP is working with Apache!</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

echo "<h2>🌐 Server Information</h2>";
echo "<ul>";
echo "<li><strong>Server Name:</strong> " . $_SERVER['SERVER_NAME'] . "</li>";
echo "<li><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</li>";
echo "</ul>";

echo "<h2>📁 File Structure Check</h2>";
echo "<ul>";
$files = ['artisan', 'composer.json', '.env', 'public/index.php', 'bootstrap/app.php', 'vendor/autoload.php'];
foreach ($files as $file) {
    $exists = file_exists($file);
    $icon = $exists ? "✅" : "❌";
    echo "<li>{$icon} {$file}</li>";
}
echo "</ul>";

echo "<h2>🔗 Test Links</h2>";
echo "<p>If Laravel is working, these links should work:</p>";
echo "<ul>";
echo "<li><a href='/florida/login' target='_blank'>Florida Login</a></li>";
echo "<li><a href='/missouri/login' target='_blank'>Missouri Login</a></li>";
echo "<li><a href='/texas/login' target='_blank'>Texas Login</a></li>";
echo "<li><a href='/delaware/login' target='_blank'>Delaware Login</a></li>";
echo "</ul>";

echo "<h2>🧪 Laravel Test</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "<p>✅ Composer autoload found</p>";
    
    try {
        require_once 'vendor/autoload.php';
        echo "<p>✅ Autoload successful</p>";
        
        if (file_exists('bootstrap/app.php')) {
            echo "<p>✅ Bootstrap file found</p>";
            
            try {
                $app = require_once 'bootstrap/app.php';
                echo "<p>✅ Laravel application loaded successfully!</p>";
                echo "<p><strong>🎉 Laravel is ready to work!</strong></p>";
                
                // Test if we can get routes
                try {
                    $router = $app->make('router');
                    $routes = $router->getRoutes();
                    $routeCount = count($routes);
                    echo "<p>✅ Found {$routeCount} registered routes</p>";
                    
                    // Look for our auth routes
                    $authRoutes = 0;
                    foreach ($routes as $route) {
                        if (strpos($route->uri(), 'login') !== false) {
                            $authRoutes++;
                        }
                    }
                    echo "<p>✅ Found {$authRoutes} authentication routes</p>";
                    
                } catch (Exception $e) {
                    echo "<p>⚠️ Could not check routes: " . $e->getMessage() . "</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ Laravel app failed to load: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>❌ Bootstrap file missing</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Autoload failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Composer autoload not found</p>";
}

echo "<h2>🔧 Next Steps</h2>";
echo "<p>If you see '✅ Laravel application loaded successfully!' above:</p>";
echo "<ol>";
echo "<li>Laravel is working correctly</li>";
echo "<li>The issue is with URL routing in Laragon</li>";
echo "<li>Follow the LARAGON_CONFIG_GUIDE.txt instructions</li>";
echo "<li>Restart Apache in Laragon</li>";
echo "<li>Try the login links above</li>";
echo "</ol>";

echo "<p>If Laravel is not loading:</p>";
echo "<ol>";
echo "<li>Run: composer install</li>";
echo "<li>Copy .env.example to .env</li>";
echo "<li>Run: php artisan key:generate</li>";
echo "<li>Refresh this page</li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>Diagnostic page created at " . date('Y-m-d H:i:s') . "</small></p>";
?>