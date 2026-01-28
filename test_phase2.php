<?php
echo "🚀 PHASE 2 INTEGRATION TEST\n";
echo "===========================\n\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "✅ Laravel bootstrapped\n";
    
    // Test if controllers exist
    $controllers = [
        'App\Http\Controllers\Student\Florida\CoursePlayerController',
        'App\Http\Controllers\Student\Missouri\CoursePlayerController',
        'App\Http\Controllers\Admin\DashboardController'
    ];
    
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "✅ Controller exists: $controller\n";
        } else {
            echo "❌ Controller missing: $controller\n";
        }
    }
    
    // Test database tables
    $tables = ['florida_courses', 'missouri_courses', 'users'];
    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "✅ Table $table: $count records\n";
        } catch (Exception $e) {
            echo "❌ Table $table: Error\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🌐 TEST THESE URLS:\n";
echo "===================\n";
echo "http://nelly-elearning.test/florida\n";
echo "http://nelly-elearning.test/florida/test\n";
echo "http://nelly-elearning.test/missouri\n";
echo "http://nelly-elearning.test/admin\n";
?>