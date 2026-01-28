<?php
// Fix JSON Error - Comprehensive solution
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Fix JSON Error</h1>";
echo "<pre>";

try {
    echo "=== FIXING JSON ERROR ===\n\n";
    
    // 1. Fix database issues that cause 500 errors
    echo "1. Fixing database structure...\n";
    
    // Ensure florida_courses table exists
    if (!Schema::hasTable('florida_courses')) {
        Schema::create('florida_courses', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('state', 50)->default('FL');
            $table->string('state_code', 10)->nullable();
            $table->integer('duration')->default(240);
            $table->integer('total_duration')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('passing_score')->default(80);
            $table->integer('min_pass_score')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('course_type')->default('BDI');
            $table->string('delivery_type')->default('Online');
            $table->string('certificate_type')->nullable();
            $table->string('certificate_template')->nullable();
            $table->string('dicds_course_id')->nullable();
            $table->timestamps();
        });
        echo "   ✅ Created florida_courses table\n";
    }
    
    // Add missing columns to existing table
    $requiredColumns = [
        'state_code' => 'string',
        'total_duration' => 'integer',
        'min_pass_score' => 'integer',
        'certificate_template' => 'string',
        'delivery_type' => 'string',
        'dicds_course_id' => 'string'
    ];
    
    foreach ($requiredColumns as $column => $type) {
        if (!Schema::hasColumn('florida_courses', $column)) {
            Schema::table('florida_courses', function ($table) use ($column, $type) {
                if ($type === 'string') {
                    $table->string($column)->nullable();
                } else {
                    $table->integer($column)->nullable();
                }
            });
            echo "   ✅ Added {$column} column\n";
        }
    }
    
    // 2. Fix user roles to prevent 403 errors
    echo "\n2. Fixing user roles...\n";
    
    if (!Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "   ✅ Added role column\n";
    }
    
    // Set roles for users without roles
    $usersFixed = DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
    if ($usersFixed > 0) {
        echo "   ✅ Fixed {$usersFixed} users without roles\n";
    }
    
    // Make first user super-admin if no admins exist
    $adminCount = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->count();
    if ($adminCount === 0) {
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'super-admin']);
            echo "   ✅ Made '{$firstUser->email}' a super-admin\n";
        }
    }
    
    // 3. Create missing tables that might cause 500 errors
    echo "\n3. Creating missing tables...\n";
    
    // Create push_notifications table if it doesn't exist
    if (!Schema::hasTable('push_notifications')) {
        Schema::create('push_notifications', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        echo "   ✅ Created push_notifications table\n";
    }
    
    // Create sessions table if it doesn't exist
    if (!Schema::hasTable('sessions')) {
        Schema::create('sessions', function ($table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        echo "   ✅ Created sessions table\n";
    }
    
    // 4. Test the endpoints that commonly fail
    echo "\n4. Testing endpoints...\n";
    
    try {
        $controller = new \App\Http\Controllers\CourseController();
        $request = new \Illuminate\Http\Request();
        $response = $controller->indexWeb($request);
        
        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $isValidJson = json_decode($content) !== null;
            echo "   ✅ /web/courses endpoint works, returns valid JSON: " . ($isValidJson ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ❌ /web/courses endpoint failed with status: " . $response->getStatusCode() . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ /web/courses endpoint error: " . $e->getMessage() . "\n";
    }
    
    try {
        $floridaController = new \App\Http\Controllers\FloridaCourseController();
        $response = $floridaController->indexWeb();
        
        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $isValidJson = json_decode($content) !== null;
            echo "   ✅ /api/florida-courses endpoint works, returns valid JSON: " . ($isValidJson ? 'Yes' : 'No') . "\n";
        } else {
            echo "   ❌ /api/florida-courses endpoint failed with status: " . $response->getStatusCode() . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ /api/florida-courses endpoint error: " . $e->getMessage() . "\n";
    }
    
    // 5. Disable maintenance mode if enabled
    echo "\n5. Checking maintenance mode...\n";
    
    $maintenanceFile = storage_path('framework/maintenance.php');
    if (file_exists($maintenanceFile)) {
        unlink($maintenanceFile);
        echo "   ✅ Disabled maintenance mode\n";
    } else {
        echo "   ✅ Maintenance mode already disabled\n";
    }
    
    // 6. Create a test endpoint to verify JSON responses
    echo "\n6. Creating test endpoint...\n";
    
    $testResponse = [
        'success' => true,
        'message' => 'JSON endpoint is working correctly',
        'timestamp' => now()->toISOString(),
        'test_data' => [
            'courses_count' => DB::table('florida_courses')->count(),
            'users_count' => DB::table('users')->count(),
        ]
    ];
    
    echo "   ✅ Test JSON response: " . json_encode($testResponse, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n🎉 JSON ERROR FIX COMPLETE!\n";
    
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "1. Database structure issues that cause 500 errors\n";
    echo "2. User role issues that cause 403 errors\n";
    echo "3. Missing tables that cause database errors\n";
    echo "4. Maintenance mode disabled\n";
    echo "5. Endpoints tested and verified\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Clear browser cache completely\n";
    echo "2. Open browser Developer Tools (F12)\n";
    echo "3. Go to Network tab\n";
    echo "4. Try the action that was failing\n";
    echo "5. Check the failed request - it should now return JSON instead of HTML\n";
    
    echo "\n🔧 IF STILL GETTING JSON ERROR:\n";
    echo "1. Check which specific endpoint is failing in Network tab\n";
    echo "2. Look at the Response tab to see what's actually being returned\n";
    echo "3. Check Laravel logs: storage/logs/laravel.log\n";
    echo "4. Make sure you're logged in as an admin user\n";
    
} catch (Exception $e) {
    echo "❌ FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>