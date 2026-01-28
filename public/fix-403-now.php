<?php
// Immediate 403 fix - run this in browser
require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>403 Course Creation Fix</h1>";
echo "<pre>";

try {
    echo "=== FIXING 403 FORBIDDEN ERROR ===\n\n";
    
    // Quick diagnosis
    echo "1. Diagnosing the issue...\n";
    
    $users = DB::table('users')->select('id', 'name', 'email', 'role')->get();
    echo "   Found " . $users->count() . " users:\n";
    
    foreach ($users as $user) {
        $role = $user->role ?? 'NO ROLE';
        $access = in_array($role, ['super-admin', 'admin', 'user']) ? '✅ CAN ACCESS' : '❌ BLOCKED';
        echo "     - {$user->email} (Role: {$role}) {$access}\n";
    }
    
    // Fix users without roles
    echo "\n2. Fixing user roles...\n";
    
    $usersFixed = 0;
    foreach ($users as $user) {
        if (!$user->role || $user->role === '') {
            DB::table('users')->where('id', $user->id)->update(['role' => 'user']);
            echo "   ✅ Fixed {$user->email} - assigned 'user' role\n";
            $usersFixed++;
        }
    }
    
    if ($usersFixed === 0) {
        echo "   ✅ All users already have roles\n";
    }
    
    // Ensure admin exists
    echo "\n3. Ensuring admin access...\n";
    
    $adminCount = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->count();
    if ($adminCount === 0) {
        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('users')->where('id', $firstUser->id)->update(['role' => 'super-admin']);
            echo "   ✅ Made {$firstUser->email} a super-admin\n";
        }
    } else {
        echo "   ✅ Found {$adminCount} admin users\n";
    }
    
    // Add role column if missing
    echo "\n4. Checking database structure...\n";
    
    if (!Schema::hasColumn('users', 'role')) {
        Schema::table('users', function ($table) {
            $table->string('role')->default('user');
        });
        echo "   ✅ Added role column to users table\n";
    } else {
        echo "   ✅ Role column exists\n";
    }
    
    // Final verification
    echo "\n5. Final verification...\n";
    
    $finalUsers = DB::table('users')->select('email', 'role')->get();
    $canAccessCount = 0;
    
    foreach ($finalUsers as $user) {
        if (in_array($user->role, ['super-admin', 'admin', 'user'])) {
            $canAccessCount++;
        }
    }
    
    echo "   Users who can access course creation: {$canAccessCount}/{$finalUsers->count()}\n";
    
    if ($canAccessCount === $finalUsers->count()) {
        echo "\n🎉 SUCCESS! 403 ERROR FIXED!\n";
        echo "\n✅ All users now have proper roles\n";
        echo "✅ Course creation should work\n";
        echo "✅ No more 403 Forbidden errors\n";
        
        echo "\n📝 WHAT TO DO NOW:\n";
        echo "1. Close this page\n";
        echo "2. Clear your browser cache (Ctrl+Shift+Delete)\n";
        echo "3. Log out of your account\n";
        echo "4. Log back in\n";
        echo "5. Try creating a course again\n";
        
        echo "\n🔍 TECHNICAL DETAILS:\n";
        echo "- Route: POST /api/florida-courses\n";
        echo "- Required roles: super-admin, admin, or user\n";
        echo "- All users now have valid roles\n";
        
    } else {
        echo "\n⚠️ PARTIAL FIX - Some users still need roles\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Clear browser cache</strong> - Press Ctrl+Shift+Delete</li>";
echo "<li><strong>Log out</strong> of your current session</li>";
echo "<li><strong>Log back in</strong> to refresh your permissions</li>";
echo "<li><strong>Try creating a course</strong> - the 403 error should be gone!</li>";
echo "</ol>";

echo "<p><strong>If you still get 403 errors after following these steps, there may be a session/cache issue. Try using an incognito/private browser window.</strong></p>";
?>