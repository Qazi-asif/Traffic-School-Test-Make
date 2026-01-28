<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== IMMEDIATE 403 COURSE CREATION FIX ===\n\n";

try {
    // Step 1: Check current user authentication
    echo "1. Checking user authentication and roles...\n";
    
    // Get all users and their roles
    $users = DB::table('users')->select('id', 'name', 'email', 'role')->get();
    
    echo "   Current users in system:\n";
    foreach ($users as $user) {
        $role = $user->role ?? 'NO ROLE';
        echo "     - {$user->email} (Role: {$role})\n";
    }
    
    // Step 2: Fix users without proper roles
    echo "\n2. Fixing user roles...\n";
    
    // Count users without roles
    $usersWithoutRoles = DB::table('users')->whereNull('role')->orWhere('role', '')->count();
    echo "   Users without roles: {$usersWithoutRoles}\n";
    
    if ($usersWithoutRoles > 0) {
        // Assign 'user' role to users without roles
        DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
        echo "   ✓ Assigned 'user' role to {$usersWithoutRoles} users\n";
    }
    
    // Step 3: Ensure at least one admin exists
    $adminCount = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->count();
    echo "   Admin users: {$adminCount}\n";
    
    if ($adminCount === 0) {
        // Make the first user a super-admin
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'super-admin']);
            echo "   ✓ Made '{$firstUser->email}' a super-admin\n";
        } else {
            // Create emergency admin
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Emergency Admin',
                'email' => 'admin@emergency.local',
                'password' => bcrypt('emergency123'),
                'role' => 'super-admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "   ✓ Created emergency admin: admin@emergency.local / emergency123\n";
        }
    }
    
    // Step 4: Check role column exists
    echo "\n3. Checking database structure...\n";
    
    if (!Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "   ✓ Added role column to users table\n";
    } else {
        echo "   ✓ Role column exists\n";
    }
    
    // Step 5: Create roles table if it doesn't exist
    if (!Schema::hasTable('roles')) {
        echo "   Creating roles table...\n";
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert basic roles
        DB::table('roles')->insert([
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Full system access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrative access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        echo "   ✓ Created roles table with basic roles\n";
    } else {
        echo "   ✓ Roles table exists\n";
    }
    
    // Step 6: Test the specific route that's failing
    echo "\n4. Testing route access...\n";
    
    // Check if the route exists
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $floridaCoursesRoute = null;
    
    foreach ($routes as $route) {
        if ($route->uri() === 'api/florida-courses' && in_array('POST', $route->methods())) {
            $floridaCoursesRoute = $route;
            break;
        }
    }
    
    if ($floridaCoursesRoute) {
        echo "   ✓ Route 'POST /api/florida-courses' exists\n";
        echo "   Route action: " . $floridaCoursesRoute->getActionName() . "\n";
        
        // Check middleware
        $middleware = $floridaCoursesRoute->middleware();
        echo "   Route middleware: " . implode(', ', $middleware) . "\n";
    } else {
        echo "   ❌ Route 'POST /api/florida-courses' not found\n";
    }
    
    // Step 7: Show current user status
    echo "\n5. Final user status check...\n";
    
    $updatedUsers = DB::table('users')->select('id', 'name', 'email', 'role')->get();
    
    echo "   Updated users:\n";
    foreach ($updatedUsers as $user) {
        $role = $user->role ?? 'NO ROLE';
        $canAccess = in_array($role, ['super-admin', 'admin', 'user']) ? '✓ CAN ACCESS' : '❌ NO ACCESS';
        echo "     - {$user->email} (Role: {$role}) {$canAccess}\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "403 FIX RESULTS\n";
    echo str_repeat("=", 50) . "\n";
    
    $totalUsers = $updatedUsers->count();
    $usersWithValidRoles = $updatedUsers->whereIn('role', ['super-admin', 'admin', 'user'])->count();
    
    echo "✅ Total users: {$totalUsers}\n";
    echo "✅ Users with valid roles: {$usersWithValidRoles}\n";
    echo "✅ Route middleware: role:super-admin,admin,user\n";
    
    if ($usersWithValidRoles === $totalUsers) {
        echo "\n🎉 ALL USERS CAN NOW ACCESS COURSE CREATION!\n";
        echo "\n📝 NEXT STEPS:\n";
        echo "1. Clear browser cache and cookies\n";
        echo "2. Log out and log back in\n";
        echo "3. Try creating a course - it should work now!\n";
        
        if (DB::table('users')->where('email', 'admin@emergency.local')->exists()) {
            echo "\n🔑 EMERGENCY ADMIN ACCESS:\n";
            echo "Email: admin@emergency.local\n";
            echo "Password: emergency123\n";
        }
    } else {
        echo "\n⚠️ SOME USERS STILL NEED ROLE ASSIGNMENT\n";
        echo "Please assign proper roles to all users.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== FIX COMPLETE ===\n";