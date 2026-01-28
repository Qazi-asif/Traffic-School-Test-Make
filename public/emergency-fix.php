<?php
// Emergency fix for 403/500 errors - accessible via browser
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Emergency 403/500 Error Fix</h1>";
echo "<pre>";

try {
    echo "=== EMERGENCY FIX RUNNING ===\n\n";
    
    // Fix 1: push_notifications table
    if (!Schema::hasTable('push_notifications')) {
        echo "Creating push_notifications table...\n";
        Schema::create('push_notifications', function ($table) {
            $table->id();
            $table->string('user_email');
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        echo "✅ push_notifications table created\n";
    } else {
        echo "✅ push_notifications table exists\n";
    }
    
    // Fix 2: Admin user
    $adminCount = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->count();
    echo "Admin users found: {$adminCount}\n";
    
    if ($adminCount === 0) {
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'super-admin']);
            echo "✅ Made '{$firstUser->email}' a super-admin\n";
        } else {
            DB::table('users')->insert([
                'name' => 'Emergency Admin',
                'email' => 'admin@emergency.local',
                'password' => bcrypt('emergency123'),
                'role' => 'super-admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Created emergency admin: admin@emergency.local / emergency123\n";
        }
    }
    
    // Fix 3: Role column
    if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "✅ Added role column to users table\n";
    }
    
    // Fix 4: Users without roles
    $usersFixed = DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
    if ($usersFixed > 0) {
        echo "✅ Fixed {$usersFixed} users without roles\n";
    }
    
    // Fix 5: Test notification system
    try {
        $testCount = DB::table('push_notifications')->count();
        echo "✅ Notification system working (found {$testCount} notifications)\n";
    } catch (Exception $e) {
        echo "⚠️ Notification system issue: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 EMERGENCY FIX COMPLETE!\n";
    echo "✅ 500 errors should be resolved\n";
    echo "✅ 403 errors should be resolved\n";
    echo "✅ Course creation should work now\n";
    echo "\n📝 Next steps:\n";
    echo "1. Clear your browser cache\n";
    echo "2. Log out and log back in\n";
    echo "3. Try creating a course again\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
?>