<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

echo "🔍 TESTING MODULE BLOCKING SYSTEM\n";
echo "=================================\n\n";

// Test database connection
try {
    $modules = DB::table('system_modules')->get();
    echo "✅ Database connection successful\n";
    echo "📊 Found " . $modules->count() . " modules in database\n\n";
    
    echo "📋 CURRENT MODULE STATUS:\n";
    echo "-------------------------\n";
    foreach ($modules as $module) {
        $status = $module->enabled ? '✅ ENABLED' : '❌ DISABLED';
        echo "• {$module->module_name}: {$status}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "💡 Make sure you've run the SQL script to create the tables.\n";
    exit(1);
}

echo "\n🧪 TESTING ROUTE PATTERNS:\n";
echo "----------------------------\n";

$routeModuleMap = [
    'admin' => 'admin_panel',
    'admin/dashboard' => 'admin_panel',
    'courses' => 'course_enrollment',
    'course-player' => 'course_enrollment',
    'payment' => 'payment_processing',
    'checkout' => 'payment_processing',
    'certificates' => 'certificate_generation',
    'announcements' => 'announcements',
    'final-exam' => 'final_exams',
];

foreach ($routeModuleMap as $route => $module) {
    $moduleStatus = DB::table('system_modules')
                     ->where('module_name', $module)
                     ->first();
    
    if ($moduleStatus) {
        $status = $moduleStatus->enabled ? '🟢 ALLOWED' : '🔴 BLOCKED';
        echo "Route: /{$route} → Module: {$module} → {$status}\n";
    } else {
        echo "Route: /{$route} → Module: {$module} → ⚠️  NOT FOUND IN DB\n";
    }
}

echo "\n🎯 TESTING INSTRUCTIONS:\n";
echo "-------------------------\n";
echo "1. Go to your hidden admin panel\n";
echo "2. Disable a module (e.g., 'admin_panel')\n";
echo "3. Try to access /admin - you should see maintenance page\n";
echo "4. Check Laravel logs for debugging info\n";
echo "5. Clear cache if needed: php artisan cache:clear\n";

echo "\n🔧 TROUBLESHOOTING:\n";
echo "-------------------\n";
echo "• If modules still work after disabling:\n";
echo "  - Check Laravel logs for middleware errors\n";
echo "  - Clear cache: php artisan cache:clear\n";
echo "  - Verify middleware is registered in Kernel.php\n";
echo "  - Check route patterns match your URLs\n";

echo "\n✅ Test completed!\n";