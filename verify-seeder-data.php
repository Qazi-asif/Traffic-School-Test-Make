<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== UserDataSeeder Verification ===\n\n";

try {
    // Check users table
    $totalUsers = DB::table('users')->count();
    echo "✅ Total users created: {$totalUsers}\n\n";

    // Check users by state
    echo "📍 Users by state:\n";
    $usersByState = DB::table('users')
        ->select('state_code', DB::raw('count(*) as total'))
        ->groupBy('state_code')
        ->get();
    
    foreach ($usersByState as $state) {
        echo "   - " . strtoupper($state->state_code) . ": {$state->total} users\n";
    }

    // Check users by role
    echo "\n👥 Users by role:\n";
    $usersByRole = DB::table('users')
        ->select('role', DB::raw('count(*) as total'))
        ->groupBy('role')
        ->get();
    
    foreach ($usersByRole as $role) {
        echo "   - " . ucfirst($role->role) . ": {$role->total} users\n";
    }

    // Check system settings
    $totalSettings = DB::table('system_settings')->count();
    echo "\n⚙️ System settings created: {$totalSettings}\n";

    // Show sample users
    echo "\n📋 Sample users created:\n";
    $sampleUsers = DB::table('users')
        ->select('name', 'email', 'state_code', 'role', 'is_active')
        ->limit(5)
        ->get();
    
    foreach ($sampleUsers as $user) {
        $status = $user->is_active ? 'Active' : 'Inactive';
        echo "   - {$user->name} ({$user->email}) - {$user->state_code} - {$user->role} - {$status}\n";
    }

    echo "\n✅ UserDataSeeder executed successfully!\n";
    echo "🚀 Ready to test admin system with sample data.\n\n";

    echo "🔑 Login credentials:\n";
    echo "   Admin: admin@trafficschool.com / password123\n";
    echo "   Students: All users have password 'password123'\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}