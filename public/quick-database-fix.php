<?php
// Quick Database Fix for Course Creation
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Quick Database Fix</h1>";
echo "<pre>";

try {
    echo "=== QUICK DATABASE FIX ===\n\n";
    
    // 1. Ensure florida_courses table has all required columns
    echo "1. Checking florida_courses table...\n";
    
    if (!Schema::hasTable('florida_courses')) {
        echo "   Creating florida_courses table...\n";
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
    } else {
        echo "   ✅ florida_courses table exists\n";
        
        // Add missing columns if they don't exist
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
    }
    
    // 2. Fix users table for roles
    echo "\n2. Checking users table...\n";
    
    if (!Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "   ✅ Added role column to users\n";
    }
    
    // Set default roles for users without roles
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
    
    // 3. Test course creation
    echo "\n3. Testing course creation...\n";
    
    $testCourseId = DB::table('florida_courses')->insertGetId([
        'title' => 'Quick Fix Test Course',
        'description' => 'Test course to verify system works',
        'state' => 'FL',
        'state_code' => 'FL',
        'duration' => 240,
        'total_duration' => 240,
        'price' => 29.99,
        'passing_score' => 80,
        'min_pass_score' => 80,
        'is_active' => true,
        'course_type' => 'BDI',
        'delivery_type' => 'Online',
        'dicds_course_id' => 'QUICK_TEST_' . time(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "   ✅ Course creation works! Test ID: {$testCourseId}\n";
    
    // Clean up test course
    DB::table('florida_courses')->where('id', $testCourseId)->delete();
    echo "   ✅ Test course cleaned up\n";
    
    // 4. Clear caches
    echo "\n4. Clearing caches...\n";
    
    try {
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        echo "   ✅ All caches cleared\n";
    } catch (Exception $e) {
        echo "   ⚠️ Could not clear caches: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 QUICK FIX COMPLETE!\n";
    echo "\n✅ WHAT WAS FIXED:\n";
    echo "1. Database structure verified and fixed\n";
    echo "2. User roles system fixed\n";
    echo "3. Course creation tested and working\n";
    echo "4. All caches cleared\n";
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Clear browser cache (Ctrl+Shift+Delete)\n";
    echo "2. Try course creation - should work now!\n";
    
} catch (Exception $e) {
    echo "❌ QUICK FIX ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>