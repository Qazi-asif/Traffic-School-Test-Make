<?php
/**
 * Setup Authentication System
 * Creates roles and test users for multi-state authentication
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "🚀 Setting up Multi-State Authentication System...\n\n";

// Create Roles
echo "📋 Creating Roles...\n";

$roles = [
    [
        'name' => 'Student',
        'slug' => 'student',
        'permissions' => ['view_courses', 'take_courses', 'view_certificates']
    ],
    [
        'name' => 'Instructor',
        'slug' => 'instructor', 
        'permissions' => ['view_courses', 'manage_courses', 'view_students']
    ],
    [
        'name' => 'School Admin',
        'slug' => 'school-admin',
        'permissions' => ['manage_school', 'view_reports', 'manage_students', 'manage_courses']
    ],
    [
        'name' => 'Super Admin',
        'slug' => 'super-admin',
        'permissions' => ['*'] // All permissions
    ]
];

foreach ($roles as $roleData) {
    try {
        $role = Role::updateOrCreate(
            ['slug' => $roleData['slug']],
            $roleData
        );
        echo "✅ Created/Updated Role: {$roleData['name']} ({$roleData['slug']})\n";
    } catch (Exception $e) {
        echo "❌ Error creating role {$roleData['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n👥 Creating Test Users...\n";

// Get student role ID
$studentRole = Role::where('slug', 'student')->first();
$adminRole = Role::where('slug', 'super-admin')->first();

if (!$studentRole || !$adminRole) {
    echo "❌ Error: Could not find required roles\n";
    exit(1);
}

$testUsers = [
    // State-specific students
    [
        'first_name' => 'Florida',
        'last_name' => 'Student',
        'email' => 'florida@test.com',
        'password' => Hash::make('password123'),
        'state' => 'florida',
        'license_state' => 'FL',
        'role_id' => $studentRole->id,
        'status' => 'active'
    ],
    [
        'first_name' => 'Missouri',
        'last_name' => 'Student',
        'email' => 'missouri@test.com',
        'password' => Hash::make('password123'),
        'state' => 'missouri',
        'license_state' => 'MO',
        'role_id' => $studentRole->id,
        'status' => 'active'
    ],
    [
        'first_name' => 'Texas',
        'last_name' => 'Student',
        'email' => 'texas@test.com',
        'password' => Hash::make('password123'),
        'state' => 'texas',
        'license_state' => 'TX',
        'role_id' => $studentRole->id,
        'status' => 'active'
    ],
    [
        'first_name' => 'Delaware',
        'last_name' => 'Student',
        'email' => 'delaware@test.com',
        'password' => Hash::make('password123'),
        'state' => 'delaware',
        'license_state' => 'DE',
        'role_id' => $studentRole->id,
        'status' => 'active'
    ],
    // Admin user
    [
        'first_name' => 'Super',
        'last_name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('admin123'),
        'state' => 'admin',
        'license_state' => null,
        'role_id' => $adminRole->id,
        'status' => 'active'
    ]
];

foreach ($testUsers as $userData) {
    try {
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            $userData
        );
        
        echo "✅ Created/Updated: {$userData['first_name']} {$userData['last_name']} ({$userData['email']})\n";
        echo "   State: {$userData['state']} | Role: " . ($userData['role_id'] == $studentRole->id ? 'Student' : 'Admin') . "\n";
        echo "   Password: " . ($userData['role_id'] == $adminRole->id ? 'admin123' : 'password123') . "\n\n";
        
    } catch (Exception $e) {
        echo "❌ Error creating {$userData['first_name']} {$userData['last_name']}: " . $e->getMessage() . "\n\n";
    }
}

echo "🎯 Authentication System Setup Complete!\n\n";
echo "📋 Test the following URLs:\n";
echo "🌴 Florida Login: http://nelly-elearning.test/florida/login\n";
echo "🏛️  Missouri Login: http://nelly-elearning.test/missouri/login\n";
echo "🤠 Texas Login: http://nelly-elearning.test/texas/login\n";
echo "🏖️  Delaware Login: http://nelly-elearning.test/delaware/login\n\n";

echo "📋 Registration URLs:\n";
echo "🌴 Florida Register: http://nelly-elearning.test/florida/register\n";
echo "🏛️  Missouri Register: http://nelly-elearning.test/missouri/register\n";
echo "🤠 Texas Register: http://nelly-elearning.test/texas/register\n";
echo "🏖️  Delaware Register: http://nelly-elearning.test/delaware/register\n\n";

echo "🔑 Test Credentials:\n";
echo "Students: florida@test.com, missouri@test.com, texas@test.com, delaware@test.com\n";
echo "Password: password123\n";
echo "Admin: admin@test.com / admin123\n";