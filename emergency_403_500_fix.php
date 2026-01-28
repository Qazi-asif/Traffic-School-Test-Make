<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== EMERGENCY 403/500 FIX ===\n\n";

try {
    // Quick Fix 1: Create push_notifications table if missing
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
        echo "✓ push_notifications table created\n";
    }
    
    // Quick Fix 2: Ensure admin user exists
    $adminUser = DB::table('users')->where('role', 'super-admin')->first();
    if (!$adminUser) {
        // Make the first user an admin
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'super-admin']);
            echo "✓ Made user '{$firstUser->email}' a super-admin\n";
        } else {
            // Create emergency admin
            DB::table('users')->insert([
                'name' => 'Emergency Admin',
                'email' => 'admin@emergency.local',
                'password' => bcrypt('emergency123'),
                'role' => 'super-admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✓ Created emergency admin: admin@emergency.local / emergency123\n";
        }
    }
    
    // Quick Fix 3: Add role column if missing
    if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "✓ Added role column to users table\n";
    }
    
    // Quick Fix 4: Fix users without roles
    $usersFixed = DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
    if ($usersFixed > 0) {
        echo "✓ Fixed {$usersFixed} users without roles\n";
    }
    
    echo "\n🎉 EMERGENCY FIX COMPLETE!\n";
    echo "- 500 errors should be resolved\n";
    echo "- 403 errors should be resolved\n";
    echo "- Course creation should work now\n";
    echo "\nPlease refresh your browser and try again.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== END EMERGENCY FIX ===\n";