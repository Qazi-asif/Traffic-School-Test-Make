<?php
/**
 * Clear Laravel Cache - Phase 1 Integration
 */

echo "🔄 CLEARING LARAVEL CACHE\n";
echo "=========================\n\n";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Clear route cache
    try {
        Artisan::call('route:clear');
        echo "✅ Route cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Route clear failed: " . $e->getMessage() . "\n";
    }
    
    // Clear config cache
    try {
        Artisan::call('config:clear');
        echo "✅ Config cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ Config clear failed: " . $e->getMessage() . "\n";
    }
    
    // Clear view cache
    try {
        Artisan::call('view:clear');
        echo "✅ View cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️ View clear failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 CACHE CLEARED SUCCESSFULLY!\n";
    echo "===============================\n";
    echo "Now test these URLs:\n";
    echo "- http://nelly-elearning.test/florida\n";
    echo "- http://nelly-elearning.test/florida/test-controller\n";
    echo "- http://nelly-elearning.test/missouri\n";
    echo "- http://nelly-elearning.test/admin\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>