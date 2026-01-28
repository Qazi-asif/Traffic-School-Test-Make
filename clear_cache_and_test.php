<?php
/**
 * Clear Laravel Cache and Test State Routes
 */

echo "🔄 Clearing Laravel Cache...\n";

// Clear various Laravel caches
if (function_exists('artisan')) {
    try {
        artisan('config:clear');
        echo "✅ Config cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Config clear failed: " . $e->getMessage() . "\n";
    }

    try {
        artisan('route:clear');
        echo "✅ Route cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Route clear failed: " . $e->getMessage() . "\n";
    }

    try {
        artisan('view:clear');
        echo "✅ View cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ View clear failed: " . $e->getMessage() . "\n";
    }

    try {
        artisan('cache:clear');
        echo "✅ Application cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Cache clear failed: " . $e->getMessage() . "\n";
    }
}

echo "\n🧪 Testing Route Registration...\n";

// Test if routes are registered
try {
    $routes = app('router')->getRoutes();
    $stateRoutes = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'florida') === 0 || 
            strpos($uri, 'missouri') === 0 || 
            strpos($uri, 'texas') === 0 || 
            strpos($uri, 'delaware') === 0) {
            $stateRoutes[] = $uri;
        }
    }
    
    if (count($stateRoutes) > 0) {
        echo "✅ State routes found:\n";
        foreach ($stateRoutes as $route) {
            echo "   - /$route\n";
        }
    } else {
        echo "❌ No state routes found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Route test failed: " . $e->getMessage() . "\n";
}

echo "\n🌐 Test URLs:\n";
echo "- http://nelly-elearning.test/florida\n";
echo "- http://nelly-elearning.test/missouri\n";
echo "- http://nelly-elearning.test/texas\n";
echo "- http://nelly-elearning.test/delaware\n";

echo "\n✅ Cache cleared! Try accessing the URLs above.\n";
?>