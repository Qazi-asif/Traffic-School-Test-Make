<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Clearing Application Cache ===\n";
    
    // Clear various caches
    \Artisan::call('cache:clear');
    echo "✅ Application cache cleared\n";
    
    \Artisan::call('config:clear');
    echo "✅ Configuration cache cleared\n";
    
    \Artisan::call('route:clear');
    echo "✅ Route cache cleared\n";
    
    \Artisan::call('view:clear');
    echo "✅ View cache cleared\n";
    
    // Clear the specific enrollment cache that was mentioned in the logs
    $cacheKeys = [
        'enrollment_course_data_99_*',
        'enrollment_course_data_*'
    ];
    
    foreach ($cacheKeys as $pattern) {
        try {
            \Cache::flush();
            echo "✅ Cache flushed\n";
            break;
        } catch (\Exception $e) {
            echo "⚠️  Cache flush warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Cache Clearing Complete ===\n";
    echo "Please try accessing the Florida 4-Hour BDI Course again.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}