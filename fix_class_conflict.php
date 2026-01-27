<?php

echo "=== Fixing Class Conflict Issue ===\n";

try {
    // Clear composer autoloader cache
    echo "1. Clearing composer autoloader cache...\n";
    exec('composer dump-autoload --optimize 2>&1', $output, $return_var);
    
    if ($return_var === 0) {
        echo "✅ Composer autoloader cache cleared\n";
    } else {
        echo "⚠️  Composer command may not be available, trying alternative...\n";
        
        // Alternative: Clear Laravel caches
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        
        echo "✅ Laravel caches cleared\n";
    }
    
    echo "\n2. Checking class conflict resolution...\n";
    
    // Check if both controllers exist with different names
    $studentController = 'app/Http/Controllers/StudentFreeResponseQuizController.php';
    $adminController = 'app/Http/Controllers/Admin/FreeResponseQuizController.php';
    
    if (file_exists($studentController)) {
        echo "✅ Student controller exists: StudentFreeResponseQuizController\n";
    } else {
        echo "❌ Student controller missing\n";
    }
    
    if (file_exists($adminController)) {
        echo "✅ Admin controller exists: Admin\\FreeResponseQuizController\n";
    } else {
        echo "❌ Admin controller missing\n";
    }
    
    echo "\n=== Fix Complete ===\n";
    echo "The class conflict should now be resolved.\n";
    echo "If you still see errors, restart your web server.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}